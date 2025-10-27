<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\ScreeningSession;
use App\Models\Symptom;
use App\Models\Threshold;

/**
 * Sistem Pakar untuk Pengecekan Depresi G1-G9
 *
 * Implementasi sesuai prototipe dengan:
 * - G1-G8: Scale 0-2 (Tidak, Kadang, Sering)
 * - G9: Boolean 0-1 (Tidak, Ya)
 * - Core symptoms: G1 > 0 AND G2 > 0
 * - Thresholds: 0-4 (rendah), 5-9 (sedang), 10-16 (tinggi)
 * - Escalation: if core symptoms, escalate level by 1
 * - Crisis override: if G9 = 1, show crisis message
 */
class DepressionExpertSystem
{
    /**
     * Hitung skor berdasarkan jawaban G1-G9
     *
     * Score calculation berbasis TYPE flag (bukan hardcoded code)
     * - type='scale': Add to score (0-2 each, max 16)
     * - type='boolean': Skip scoring
     *
     * Format answers: array of ['symptom_id' => integer, 'value' => integer]
     */
    public function calculateScore(array $answers): int
    {
        $score = 0;

        // Get all symptoms dengan type flag
        $symptoms = Symptom::whereIn('symptom_id', array_column($answers, 'symptom_id'))
            ->get()
            ->keyBy('symptom_id');

        foreach ($answers as $answer) {
            $symptomId = $answer['symptom_id'];
            $value = (int)($answer['value'] ?? 0);
            $symptom = $symptoms[$symptomId] ?? null;

            // Add to score based on TYPE flag (not hardcoded code)
            if ($symptom && $symptom->type === 'scale') {
                $score += $value;
            }
            // type='boolean': Skip scoring (G9)
        }

        return $score;
    }

    /**
     * Tentukan level risk berdasarkan skor dan dengan escalation jika ada core symptoms
     * Menggunakan Threshold dari database
     */
    public function determineLevelByScore(int $score, bool $hasCoreSymptoms = false): string
    {
        // Get threshold dari database berdasarkan score
        $threshold = Threshold::levelForScore($score);

        if (!$threshold) {
            // Fallback ke config jika tidak ada threshold di database
            $config = config('expert_system.thresholds');
            $baseLevel = 'rendah';
            foreach ($config as $levelName => $thresholdConfig) {
                if ($score >= $thresholdConfig['min'] && $score <= $thresholdConfig['max']) {
                    $baseLevel = $levelName;
                    break;
                }
            }
        } else {
            $baseLevel = $threshold->level;
        }

        // Apply escalation jika core symptoms present
        if ($hasCoreSymptoms && config('expert_system.escalation_rules.core_symptoms_escalate')) {
            $escalationMap = config('expert_system.escalation_rules.escalation_levels');
            $baseLevel = $escalationMap[$baseLevel] ?? $baseLevel;
        }

        return $baseLevel;
    }

    /**
     * Deteksi core symptoms dari jawaban
     *
     * Core symptoms: Symptoms yang di-flag dengan is_core=true dan value > 0
     * (Bukan hardcoded G1 dan G2, tapi berbasis database flag)
     *
     * Format answers: array of ['symptom_id' => integer, 'value' => integer]
     */
    public function detectCoreSymptoms(array $answers): array
    {
        $coreSymptomValues = [];

        // Get semua core symptoms dari database
        $coreSymptoms = Symptom::where('is_core', true)->get();

        foreach ($answers as $answer) {
            $symptomId = $answer['symptom_id'] ?? null;
            $value = (int)($answer['value'] ?? 0);

            // Cek apakah symptom ini adalah core symptom
            $coreSymptom = $coreSymptoms->firstWhere('symptom_id', $symptomId);
            if ($coreSymptom && $value > 0) {
                $coreSymptomValues[] = [
                    'symptom_id' => $symptomId,
                    'code' => $coreSymptom->code,
                    'value' => $value,
                ];
            }
        }

        // Has core jika ada 2 atau lebih core symptoms dengan value > 0
        // (G1 dan G2 keduanya harus > 0)
        $hasCoreSymptoms = count($coreSymptomValues) >= 2;

        return [
            'has_core' => $hasCoreSymptoms,
            'core_symptoms' => $coreSymptomValues,
            'description' => $hasCoreSymptoms
                ? 'Core symptoms present: ' . implode(', ', array_column($coreSymptomValues, 'code'))
                : 'No core symptoms detected',
        ];
    }

    /**
     * Deteksi tanda krisis
     *
     * Crisis trigger: Symptoms yang di-flag dengan is_sensitive=true dan value=1
     * (Bukan hardcoded G9, tapi berbasis database flag)
     *
     * Format answers: array of ['symptom_id' => integer, 'value' => integer]
     */
    public function detectCrisisFlags(array $answers): bool
    {
        // Get semua sensitive symptoms dari database
        $sensitiveSymptoms = Symptom::where('is_sensitive', true)->get();

        foreach ($answers as $answer) {
            $symptomId = $answer['symptom_id'] ?? null;
            $value = (int)($answer['value'] ?? 0);

            // Cek apakah ada sensitive symptom dengan value = 1
            $sensitiveSympotm = $sensitiveSymptoms->firstWhere('symptom_id', $symptomId);
            if ($sensitiveSympotm && $value === 1) {
                return true;  // Crisis detected
            }
        }

        return false;
    }

    /**
     * Analisis pola gejala (legacy method untuk compatibility)
     */
    public function analyzeSymptomPattern(ScreeningSession $session): array
    {
        $answers = $session->answers()->with('symptom')->get();

        return [
            'total_symptoms' => $answers->count(),
            'answered_symptoms' => $answers->where('value', '>', 0)->count(),
            'severity_breakdown' => [
                'sering' => $answers->where('value', 2)->count(),
                'kadang' => $answers->where('value', 1)->count(),
                'tidak' => $answers->where('value', 0)->count(),
            ],
        ];
    }

    /**
     * Identifikasi cluster gejala (legacy - deprecated)
     */
    private function identifySymptomClusters($answers): array
    {
        return [];
    }

    /**
     * Generate profil pola (legacy - deprecated)
     */
    private function generatePatternProfile(ScreeningSession $session): string
    {
        return 'Pattern profile';
    }

    /**
     * Generate treatment recommendation (legacy method untuk compatibility)
     */
    public function generateTreatmentRecommendation(ScreeningSession $session): array
    {
        return [
            'urgent_actions' => $session->crisis_flag ? [
                'Rujuk ke layanan darurat psikiatri/IGD',
                'Evaluasi risiko bunuh diri',
            ] : [],
            'primary_interventions' => [],
            'secondary_interventions' => [],
            'monitoring' => ['Follow-up as scheduled'],
        ];
    }

    /**
     * Bandingkan hasil dengan screening sebelumnya (legacy - tidak digunakan untuk G1-G9)
     */
    public function compareWithPreviousSession(ScreeningSession $currentSession, ?ScreeningSession $previousSession = null): array
    {
        return [
            'previous_session_exists' => false,
            'comparison' => null,
        ];
    }

    /**
     * Generate risk score untuk clinical judgment
     */
    public function generateRiskScore(ScreeningSession $session): int
    {
        $maxScore = 16;
        $baseScore = min(100, (($session->score / $maxScore) * 100));

        // Modifier: crisis flag
        if ($session->crisis_flag) {
            $baseScore = min(100, $baseScore + 30);
        }

        // Modifier: core symptoms
        if ($session->has_core) {
            $baseScore = min(100, $baseScore + 15);
        }

        return (int)$baseScore;
    }
}
