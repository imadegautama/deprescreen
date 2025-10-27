<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\ScreeningSession;
use App\Models\Symptom;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnalysisController extends Controller
{
    /**
     * Tampilkan dashboard analisis (untuk admin/professional)
     */
    public function dashboard()
    {
        $stats = [
            'total_sessions' => ScreeningSession::count(),
            'crisis_cases' => ScreeningSession::where('crisis_flag', true)->count(),
            'high_risk' => ScreeningSession::where('level', 'tinggi')->count(),
            'medium_risk' => ScreeningSession::where('level', 'sedang')->count(),
            'low_risk' => ScreeningSession::where('level', 'rendah')->count(),
            'average_score' => round(ScreeningSession::avg('score'), 2),
            'with_core_symptoms' => ScreeningSession::where('has_core', true)->count(),
        ];

        // Data untuk chart: distribusi level
        $levelDistribution = ScreeningSession::selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->get();

        // Data untuk chart: gejala paling sering
        $topSymptoms = Answer::selectRaw('symptoms.label, symptoms.code, COUNT(*) as frequency')
            ->join('symptoms', 'answers.symptom_id', '=', 'symptoms.symptom_id')
            ->where('answers.value', '>=', 2)
            ->groupBy('symptoms.symptom_id', 'symptoms.label', 'symptoms.code')
            ->orderByDesc('frequency')
            ->limit(10)
            ->get();

        // Data untuk chart: trend per minggu
        $weeklyTrend = ScreeningSession::selectRaw(
            "DATE_FORMAT(created_at, '%Y-W%w') as week, COUNT(*) as count"
        )
            ->groupBy('week')
            ->orderBy('week')
            ->limit(12)
            ->get();

        return Inertia::render('Analysis/Dashboard', [
            'stats' => $stats,
            'levelDistribution' => $levelDistribution,
            'topSymptoms' => $topSymptoms,
            'weeklyTrend' => $weeklyTrend,
        ]);
    }

    /**
     * Tampilkan detail analisis session tertentu
     */
    public function sessionDetail($sessionId)
    {
        $session = ScreeningSession::with('answers.symptom')->findOrFail($sessionId);

        // Kelompokkan gejala berdasarkan kategori
        $answeredSymptoms = $session->answers()
            ->with('symptom')
            ->orderByDesc('value')
            ->get();

        // Detail analisis
        $analysis = [
            'severity_score' => $session->score,
            'is_crisis' => $session->crisis_flag,
            'has_core_symptoms' => $session->has_core,
            'core_symptom_count' => $answeredSymptoms->where('symptom.is_core', true)->count(),
            'severity_percentage' => round(($session->score / (Symptom::count() * 4)) * 100, 2),
            'risk_level' => $session->level,
            'recommendations' => $this->generateRecommendations($session),
        ];

        return Inertia::render('Analysis/SessionDetail', [
            'session' => $session,
            'answeredSymptoms' => $answeredSymptoms,
            'analysis' => $analysis,
        ]);
    }

    /**
     * Generate rekomendasi detail berdasarkan jawaban
     */
    private function generateRecommendations(ScreeningSession $session): array
    {
        $recommendations = [];

        // Jika ada krisis
        if ($session->crisis_flag) {
            $recommendations[] = [
                'type' => 'critical',
                'message' => 'Klien menunjukkan tanda-tanda krisis mental. Segera rujuk ke layanan psikiatri atau IGD.',
            ];
        }

        // Jika ada core symptoms
        if ($session->has_core) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Klien menunjukkan gejala core depresi. Perlu intervensi profesional segera.',
            ];
        }

        // Berdasarkan level risk
        if ($session->level === 'tinggi') {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Tingkat risiko tinggi - Rekomendasikan psikoterapi dan mungkin farmakologi.',
            ];
        } elseif ($session->level === 'sedang') {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'Tingkat risiko sedang - Rekomendasi: konseling supportif dan self-help resources.',
            ];
        } else {
            $recommendations[] = [
                'type' => 'success',
                'message' => 'Tingkat risiko rendah - Edukasi psikologis dan monitoring berkelanjutan.',
            ];
        }

        // Gejala yang menonjol
        $topSymptoms = $session->answers()
            ->where('value', '>=', 3)
            ->with('symptom')
            ->orderByDesc('value')
            ->limit(3)
            ->get();

        if ($topSymptoms->count() > 0) {
            $symptomList = $topSymptoms->pluck('symptom.label')->implode(', ');
            $recommendations[] = [
                'type' => 'info',
                'message' => "Gejala yang menonjol: {$symptomList}. Fokus pada penanganan gejala tersebut.",
            ];
        }

        return $recommendations;
    }

    /**
     * Export data screening ke CSV
     */
    public function export(Request $request)
    {
        $query = ScreeningSession::query();

        // Filter berdasarkan level
        if ($request->has('level') && $request->level) {
            $query->where('level', $request->level);
        }

        // Filter berdasarkan crisis
        if ($request->has('crisis_only') && $request->crisis_only) {
            $query->where('crisis_flag', true);
        }

        // Filter berdasarkan range tanggal
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sessions = $query->with('answers.symptom')->get();

        // Generate CSV
        $fileName = 'screening_report_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($sessions) {
            $file = fopen('php://output', 'w');

            // Header CSV
            fputcsv($file, [
                'Session ID',
                'Score',
                'Level',
                'Has Core',
                'Crisis Flag',
                'Created At',
            ]);

            // Data rows
            foreach ($sessions as $session) {
                fputcsv($file, [
                    $session->screening_session_id,
                    $session->score,
                    $session->level,
                    $session->has_core ? 'Yes' : 'No',
                    $session->crisis_flag ? 'Yes' : 'No',
                    $session->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
