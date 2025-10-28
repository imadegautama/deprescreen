<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\ScreeningSession;
use App\Models\Symptom;
use App\Models\Threshold;
use App\Services\AdviceGenerationService;
use App\Services\DepressionExpertSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ScreeningController extends Controller
{
    protected DepressionExpertSystem $expertSystem;
    protected AdviceGenerationService $adviceService;

    public function __construct(
        DepressionExpertSystem $expertSystem,
        AdviceGenerationService $adviceService
    ) {
        $this->expertSystem = $expertSystem;
        $this->adviceService = $adviceService;
    }

    /**
     * Tampilkan halaman awal screening (daftar gejala)
     */
    public function index()
    {
        // Ambil semua gejala yang aktif
        $symptoms = Symptom::orderBy('code')->get();

        return Inertia::render('Screening/Index', [
            'symptoms' => $symptoms,
        ]);
    }

    /**
     * Buat session screening baru dan simpan jawaban
     *
     * Validasi untuk setiap symptom:
     * - Berbasis flags di database (is_core, is_sensitive)
     * - Bukan hardcoded berdasarkan code (G1, G2, etc)
     * - type: 'scale' (0-2) atau 'boolean' (0-1)
     */
    public function store(Request $request)
    {
        try {
            // Validasi input sesuai struktur
            $validated = $request->validate([
                'answers' => 'required|array|size:9',  // Exactly 9 answers
                'answers.*.symptom_id' => 'required|exists:symptoms,symptom_id',
                'answers.*.value' => 'required|integer',
            ]);

            $answers = $validated['answers'];

            // Get all symptoms dengan flags mereka dari database
            $symptomsMap = Symptom::all()->keyBy('symptom_id');

            // Validasi per-question ranges berdasarkan symptom type dan flags
            foreach ($answers as $answer) {
                $symptomId = $answer['symptom_id'];
                $value = $answer['value'];
                $symptom = $symptomsMap[$symptomId] ?? null;

                if (!$symptom) {
                    throw new \Exception("Symptom ID $symptomId not found");
                }

                // Validate berdasarkan type di database
                if ($symptom->type === 'scale') {
                    // Scale questions (G1-G8): 0-2
                    if ($value < 0 || $value > 2) {
                        throw new \Exception("{$symptom->code} ({$symptom->label}) harus bernilai 0-2, got $value");
                    }
                } elseif ($symptom->type === 'boolean') {
                    // Boolean questions (G9): 0-1
                    if ($value < 0 || $value > 1) {
                        throw new \Exception("{$symptom->code} ({$symptom->label}) harus bernilai 0-1, got $value");
                    }
                }

                // Trigger warnings untuk sensitive questions jika ada flag
                if ($symptom->is_sensitive && $value === 1) {
                    Log::warning("SENSITIVE FLAG TRIGGERED", [
                        'symptom_id' => $symptomId,
                        'code' => $symptom->code,
                        'label' => $symptom->label,
                        'value' => $value,
                        'timestamp' => now(),
                    ]);
                }

                // Trigger warnings untuk core symptoms jika ada flag
                if ($symptom->is_core && $value > 0) {
                    Log::info("CORE SYMPTOM DETECTED", [
                        'symptom_id' => $symptomId,
                        'code' => $symptom->code,
                        'label' => $symptom->label,
                        'value' => $value,
                        'timestamp' => now(),
                    ]);
                }
            }

            // Gunakan database transaction untuk atomicity
            $session = DB::transaction(function () use ($answers) {
                // Gunakan expert system untuk analisis
                $totalScore = $this->expertSystem->calculateScore($answers);
                $coreAnalysis = $this->expertSystem->detectCoreSymptoms($answers);
                $hasCrisis = $this->expertSystem->detectCrisisFlags($answers);
                $level = $this->expertSystem->determineLevelByScore($totalScore, $coreAnalysis['has_core']);

                // Buat screening session baru
                $session = ScreeningSession::create([
                    'score' => $totalScore,
                    'level' => $level,
                    'has_core' => $coreAnalysis['has_core'],
                    'crisis_flag' => $hasCrisis,
                ]);

                // Simpan semua jawaban ke database dengan kolom yang benar
                foreach ($answers as $answer) {
                    Answer::create([
                        'screening_session_id' => $session->screening_session_id,
                        'symptom_id' => $answer['symptom_id'],
                        'value' => $answer['value'],
                    ]);
                }

                return $session;
            });

            // Redirect ke halaman hasil
            return redirect()->route('screening.result', $session->screening_session_id);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Tampilkan hasil screening dengan styling sesuai prototipe
     */
    public function result($sessionId)
    {
        $session = ScreeningSession::with('answers.symptom')->findOrFail($sessionId);

        // Load threshold dari database
        $threshold = Threshold::getByLevel($session->level);
        if (!$threshold) {
            // Fallback ke database default jika tidak ada
            $threshold = Threshold::levelForScore($session->score);
        }

        // Ambil gejala yang dijawab dengan score > 0
        $activeSymptoms = $session->answers()
            ->where('value', '>', 0)
            ->with('symptom')
            ->get()
            ->sortBy('symptom.order');

        // Gunakan expert system untuk analisis
        $symbolAnalysis = $this->expertSystem->analyzeSymptomPattern($session);
        $recommendations = $this->expertSystem->generateTreatmentRecommendation($session);
        $riskScore = $this->expertSystem->generateRiskScore($session);

        // Hitung statistik sesuai G1-G9 (max score 16)
        $stats = [
            'total_score' => $session->score,
            'max_possible_score' => 16,  // G1-G8 max 2 each = 16 total
            'percentage' => round(($session->score / 16) * 100, 2),
            'has_crisis' => $session->crisis_flag,
            'risk_score' => $riskScore,
        ];

        // Generate AI advice
        $aiAdvice = null;
        try {
            $aiAdvice = $this->adviceService->generateAdvice($session);
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();

            // Log with helpful info
            Log::warning('AI Advice generation failed, using fallback', [
                'session_id' => $sessionId,
                'error' => $errorMsg,
                'hint' => str_contains($errorMsg, '403')
                    ? 'API Key valid but Generative AI API not enabled in Google Cloud Console'
                    : 'Check GEMINI_API_KEY in .env or network connection',
            ]);

            $aiAdvice = null;  // Will use database advice as fallback
        }

        return Inertia::render('Screening/Result', [
            'session' => $session,
            'threshold' => $threshold ? [
                'level' => $threshold->level,
                'min_score' => $threshold->min_score,
                'max_score' => $threshold->max_score,
                'advice_text' => $threshold->advice_text,
                'label' => match ($threshold->level) {
                    'rendah' => 'Risiko Rendah',
                    'sedang' => 'Risiko Sedang',
                    'tinggi' => 'Risiko Tinggi',
                    default => 'Unknown'
                },
                'description' => match ($threshold->level) {
                    'rendah' => 'Gejala depresi minimal atau tidak ada',
                    'sedang' => 'Gejala depresi ringan hingga sedang',
                    'tinggi' => 'Gejala depresi sedang hingga berat',
                    default => 'Unknown'
                },
                'color' => match ($threshold->level) {
                    'rendah' => 'green',
                    'sedang' => 'yellow',
                    'tinggi' => 'red',
                    default => 'gray'
                },
            ] : config('expert_system.thresholds.' . $session->level),
            'activeSymptoms' => $activeSymptoms,
            'stats' => $stats,
            'advice' => $this->getAdvice($session, $threshold),
            'aiAdvice' => $aiAdvice,
            'symptomAnalysis' => $symbolAnalysis,
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Generate saran berdasarkan hasil screening (sesuai prototipe)
     * Menggunakan advice_text dari database threshold
     */
    private function getAdvice(ScreeningSession $session, ?Threshold $threshold = null): string
    {
        // Crisis flag always takes precedence
        if ($session->crisis_flag) {
            return "🚨 PERHATIAN: Hasil screening menunjukkan tanda-tanda krisis. " .
                "Segera hubungi layanan kesehatan mental atau nomor darurat.";
        }

        // Use threshold advice_text jika tersedia
        if ($threshold && $threshold->advice_text) {
            return $threshold->advice_text;
        }

        // Fallback ke hardcoded advice berdasarkan level
        if ($session->level === 'tinggi' && $session->has_core) {
            return "⚠️ Hasil screening menunjukkan gejala depresi core yang signifikan. " .
                "Direkomendasikan konsultasi segera dengan profesional kesehatan mental.";
        }

        if ($session->level === 'tinggi') {
            return "⚠️ Hasil screening menunjukkan gejala depresi sedang hingga berat. " .
                "Konsultasikan dengan profesional kesehatan mental.";
        }

        if ($session->level === 'sedang') {
            return "Hasil screening menunjukkan gejala depresi ringan hingga sedang. " .
                "Pertimbangkan konsultasi dengan profesional kesehatan mental.";
        }

        return "Hasil screening menunjukkan gejala depresi minimal atau tidak ada. " .
            "Lanjutkan gaya hidup sehat dan pemantauan rutin.";
    }

    /**
     * Tampilkan riwayat screening session user
     */
    public function history()
    {
        $sessions = ScreeningSession::recent()->paginate(10);

        return Inertia::render('Screening/History', [
            'sessions' => $sessions,
        ]);
    }

    /**
     * Reset/mulai screening baru
     */
    public function create()
    {
        return redirect()->route('screening.index');
    }

    /**
     * Bandingkan hasil dengan screening sebelumnya (optional API)
     */
    public function compareWithPrevious($sessionId)
    {
        $currentSession = ScreeningSession::findOrFail($sessionId);
        $previousSession = ScreeningSession::where('screening_session_id', '<', $sessionId)
            ->orderBy('screening_session_id', 'desc')
            ->first();

        $comparison = $this->expertSystem->compareWithPreviousSession($currentSession, $previousSession);

        return response()->json($comparison);
    }
}
