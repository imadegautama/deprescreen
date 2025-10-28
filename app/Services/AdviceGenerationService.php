<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\ScreeningSession;
use App\Models\Symptom;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdviceGenerationService
{
    private GeminiAIService $geminiService;

    public function __construct(GeminiAIService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Generate personalized advice for screening results
     *
     * @param ScreeningSession $session
     * @return string Personalized advice from AI
     */
    public function generateAdvice(ScreeningSession $session): string
    {
        try {
            // Try to get from cache first (24 hours)
            $cacheKey = "screening_advice_{$session->screening_session_id}";

            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // Build context from screening answers
            $context = $this->buildContext($session);

            // Generate advice prompt
            $prompt = $this->buildPrompt($session, $context);

            // Get advice from Gemini
            $advice = $this->geminiService->generateText($prompt, maxTokens: 800);

            // Cache the advice
            Cache::put($cacheKey, $advice, now()->addDay());

            return $advice;
        } catch (\Exception $e) {
            Log::error('Advice Generation Failed:', [
                'session_id' => $session->screening_session_id,
                'error' => $e->getMessage(),
            ]);

            // Fallback to database advice if AI fails
            return $this->getFallbackAdvice($session);
        }
    }

    /**
     * Build context from screening answers
     *
     * @param ScreeningSession $session
     * @return array
     */
    private function buildContext(ScreeningSession $session): array
    {
        $answers = Answer::with('symptom')
            ->where('screening_session_id', $session->screening_session_id)
            ->get();

        $context = [
            'total_score' => $session->score,
            'risk_level' => $session->level,
            'has_core_symptoms' => $session->has_core,
            'has_crisis_signs' => $session->crisis_flag,
            'symptoms_reported' => [],
            'severity_breakdown' => [
                'not' => 0,
                'sometimes' => 0,
                'often' => 0,
            ],
        ];

        foreach ($answers as $answer) {
            $symbol = $answer->symptom;

            if ($answer->value > 0) {
                $context['symptoms_reported'][] = [
                    'code' => $symbol->code,
                    'label' => $symbol->label,
                    'value' => $answer->value,
                    'is_core' => $symbol->is_core,
                    'is_sensitive' => $symbol->is_sensitive,
                    'severity' => $this->getSeverityLabel($answer->value, $symbol->type),
                ];
            }

            // Count severity breakdown for scale symptoms
            if ($symbol->type === 'scale') {
                if ($answer->value === 0) {
                    $context['severity_breakdown']['not']++;
                } elseif ($answer->value === 1) {
                    $context['severity_breakdown']['sometimes']++;
                } else {
                    $context['severity_breakdown']['often']++;
                }
            }
        }

        return $context;
    }

    /**
     * Build AI prompt for advice generation
     *
     * @param ScreeningSession $session
     * @param array $context
     * @return string
     */
    private function buildPrompt(ScreeningSession $session, array $context): string
    {
        $symptomsText = '';
        foreach ($context['symptoms_reported'] as $symptom) {
            $symptomsText .= "- {$symptom['label']} ({$symptom['severity']})\n";
        }

        $riskText = match ($session->level) {
            'rendah' => 'Low Risk - Minimal depressive symptoms',
            'sedang' => 'Moderate Risk - Some depressive symptoms present',
            'tinggi' => 'High Risk - Significant depressive symptoms',
            default => $session->level,
        };

        $coreSymptomText = $context['has_core_symptoms']
            ? 'Core depressive symptoms (persistent sadness/mood disturbance and loss of interest) are present.'
            : 'Core depressive symptoms are not present.';

        $crisisText = $context['has_crisis_signs']
            ? 'WARNING: Crisis indicators detected. Professional mental health support is strongly recommended.'
            : 'No immediate crisis indicators detected.';

        return <<<PROMPT
Anda adalah konselor kesehatan mental profesional dengan keahlian dalam depresi dan gangguan mood.
Berdasarkan hasil screening psikologis berikut, berikan saran dan rekomendasi yang personal,
empatik, dan konstruktif dalam Bahasa Indonesia.

===== HASIL SCREENING =====
Total Skor: {$session->score}/16
Tingkat Risiko: $riskText
{$coreSymptomText}
{$crisisText}

===== GEJALA YANG DILAPORKAN =====
$symptomsText

===== TASK =====
Berikan:
1. Interpretasi ringkas dari hasil screening (1-2 paragraf)
2. Gejala utama yang teridentifikasi dan implikasinya
3. Rekomendasi tindakan konkret yang dapat dilakukan (3-5 poin)
4. Kapan harus mencari bantuan profesional
5. Pesan dukungan yang empatik dan memotivasi

Format jawaban dengan jelas dan gunakan bahasa yang mudah dipahami.
Hindari diagnosis medis formal, fokus pada panduan dan dukungan.
Jika ada indikator krisis, tekankan pentingnya bantuan profesional segera.
PROMPT;
    }

    /**
     * Get fallback advice from database
     *
     * @param ScreeningSession $session
     * @return string
     */
    private function getFallbackAdvice(ScreeningSession $session): string
    {
        $threshold = \App\Models\Threshold::where('level', $session->level)->first();

        if ($threshold && $threshold->advice_text) {
            return $threshold->advice_text;
        }

        return $this->getDefaultAdvice($session->level);
    }

    /**
     * Get default advice for each risk level
     *
     * @param string $level
     * @return string
     */
    private function getDefaultAdvice(string $level): string
    {
        return match ($level) {
            'rendah' => 'Berdasarkan hasil screening Anda, gejala depresi minimal terdeteksi. '
                . 'Pertahankan gaya hidup sehat dengan olahraga teratur, istirahat cukup, dan hubungan sosial yang kuat. '
                . 'Jika ada perubahan dalam mood atau kesejahteraan Anda, segera konsultasikan dengan profesional kesehatan mental.',

            'sedang' => 'Hasil screening menunjukkan adanya gejala depresi yang sedang. '
                . 'Sangat disarankan untuk konsultasi dengan konselor atau psikolog untuk evaluasi lebih lanjut. '
                . 'Jangan ragu untuk mencari dukungan profesional, karena intervensi dini sangat membantu.',

            'tinggi' => 'Hasil screening menunjukkan gejala depresi yang signifikan. '
                . 'PENTING: Segera hubungi profesional kesehatan mental atau pusat krisis kesehatan jiwa. '
                . 'Jangan menunggu, dukungan profesional sangat diperlukan untuk kesejahteraan Anda.',

            default => 'Terima kasih telah mengikuti screening. Jika Anda memiliki kekhawatiran tentang kesehatan mental, '
                . 'konsultasikan dengan profesional kesehatan mental terdekat.',
        };
    }

    /**
     * Get severity label for symptom value
     *
     * @param int $value
     * @param string $type
     * @return string
     */
    private function getSeverityLabel(int $value, string $type): string
    {
        if ($type === 'boolean') {
            return $value === 1 ? 'Ya' : 'Tidak';
        }

        return match ($value) {
            0 => 'Tidak',
            1 => 'Kadang-kadang',
            2 => 'Sering',
            default => 'Unknown',
        };
    }
}
