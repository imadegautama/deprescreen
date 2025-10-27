<?php

/**
 * Konfigurasi Expert System untuk Screening Depresi
 *
 * File ini berisi konstanta dan konfigurasi yang digunakan oleh
 * sistem pakar pengecekan depresi
 */

return [
    /**
     * Skala jawaban untuk screening G1-G9
     * G1-G8: Scale 0-2 (Tidak, Kadang, Sering)
     * G9: Boolean 0-1 (Tidak, Ya)
     */
    'answer_scale' => [
        'g1_to_g8' => [
            0 => 'Tidak',
            1 => 'Kadang',
            2 => 'Sering',
        ],
        'g9' => [
            0 => 'Tidak',
            1 => 'Ya',
        ],
    ],

    /**
     * G1-G9 Symptom definitions sesuai prototipe
     */
    'symptoms' => [
        'G1' => [
            'code' => 'G1',
            'label' => 'Perasaan sedih yang mendalam atau kosong',
            'description' => 'Dalam minggu terakhir, apakah Anda merasakan sedih yang mendalam atau perasaan kosong?',
            'is_core' => true,
            'is_sensitive' => false,
            'type' => 'scale',
            'order' => 1,
        ],
        'G2' => [
            'code' => 'G2',
            'label' => 'Kehilangan minat atau kesenangan dalam aktivitas',
            'description' => 'Dalam minggu terakhir, apakah Anda mengalami kehilangan minat atau kesenangan dalam aktivitas yang biasanya Anda nikmati?',
            'is_core' => true,
            'is_sensitive' => false,
            'type' => 'scale',
            'order' => 2,
        ],
        'G3' => [
            'code' => 'G3',
            'label' => 'Perubahan nafsu makan atau berat badan',
            'description' => 'Dalam minggu terakhir, apakah terjadi perubahan signifikan pada nafsu makan atau berat badan Anda?',
            'is_core' => false,
            'is_sensitive' => false,
            'type' => 'scale',
            'order' => 3,
        ],
        'G4' => [
            'code' => 'G4',
            'label' => 'Gangguan tidur',
            'description' => 'Dalam minggu terakhir, apakah Anda mengalami gangguan tidur (sulit tidur, terlalu banyak tidur, atau tidur yang tidak nyenyak)?',
            'is_core' => false,
            'is_sensitive' => false,
            'type' => 'scale',
            'order' => 4,
        ],
        'G5' => [
            'code' => 'G5',
            'label' => 'Perubahan aktivitas atau gerakan',
            'description' => 'Dalam minggu terakhir, apakah Anda merasakan perubahan energi, kecepatan bicara, atau gerakan tubuh?',
            'is_core' => false,
            'is_sensitive' => false,
            'type' => 'scale',
            'order' => 5,
        ],
        'G6' => [
            'code' => 'G6',
            'label' => 'Kelelahan atau kehilangan energi',
            'description' => 'Dalam minggu terakhir, apakah Anda merasa lelah atau kehilangan energi hampir setiap hari?',
            'is_core' => false,
            'is_sensitive' => false,
            'type' => 'scale',
            'order' => 6,
        ],
        'G7' => [
            'code' => 'G7',
            'label' => 'Rasa tidak berguna atau bersalah berlebihan',
            'description' => 'Dalam minggu terakhir, apakah Anda merasa tidak berguna atau merasa bersalah secara berlebihan?',
            'is_core' => false,
            'is_sensitive' => false,
            'type' => 'scale',
            'order' => 7,
        ],
        'G8' => [
            'code' => 'G8',
            'label' => 'Kesulitan berkonsentrasi atau membuat keputusan',
            'description' => 'Dalam minggu terakhir, apakah Anda mengalami kesulitan untuk berkonsentrasi atau membuat keputusan?',
            'is_core' => false,
            'is_sensitive' => false,
            'type' => 'scale',
            'order' => 8,
        ],
        'G9' => [
            'code' => 'G9',
            'label' => 'Pikiran tentang kematian atau bunuh diri',
            'description' => 'Apakah Anda pernah berpikir tentang kematian atau ingin melukai diri sendiri?',
            'is_core' => false,
            'is_sensitive' => true,
            'type' => 'boolean',
            'order' => 9,
        ],
    ],

    /**
     * Scoring algorithm parameters sesuai prototipe
     */
    'scoring' => [
        'g1_to_g8_scale' => 2,  // maksimal 2 untuk G1-G8
        'max_score_g1_to_g8' => 16,  // 8 questions * 2 max score
        'total_max_score' => 16,  // G9 hanya 0/1, tidak masuk scoring
        'core_symptom_threshold' => 1,  // G1 > 0 AND G2 > 0 untuk core symptoms
    ],

    /**
     * Threshold levels sesuai prototipe
     */
    'thresholds' => [
        'rendah' => [
            'min' => 0,
            'max' => 4,
            'label' => 'Risiko Rendah',
            'description' => 'Gejala depresi minimal atau tidak ada',
            'color' => 'green',
            'icon' => '✓',
        ],
        'sedang' => [
            'min' => 5,
            'max' => 9,
            'label' => 'Risiko Sedang',
            'description' => 'Gejala depresi ringan hingga sedang',
            'color' => 'yellow',
            'icon' => '⚠',
        ],
        'tinggi' => [
            'min' => 10,
            'max' => 16,
            'label' => 'Risiko Tinggi',
            'description' => 'Gejala depresi sedang hingga berat',
            'color' => 'red',
            'icon' => '⚠',
        ],
    ],

    /**
     * Escalation rules: if core symptoms present (G1 > 0 AND G2 > 0), escalate level by 1
     */
    'escalation_rules' => [
        'enabled' => true,
        'core_symptoms_escalate' => true,
        'escalation_levels' => [
            'rendah' => 'sedang',  // rendah → sedang jika core symptoms
            'sedang' => 'tinggi',  // sedang → tinggi jika core symptoms
            'tinggi' => 'tinggi',  // tinggi tetap tinggi (max level)
        ],
    ],

    /**
     * Crisis detection sesuai prototipe
     */
    'crisis_detection' => [
        'g9_sensitive_flag' => true,  // G9 = 1 adalah indikator krisis
        'core_symptoms_with_score' => [
            'has_core' => true,
            'min_score' => 20,  // tidak relevan untuk G1-G8 max 16, tapi keep for reference
        ],
    ],

    /**
     * Risk levels deprecated - replaced by thresholds
     */
    'risk_levels' => [
        'rendah' => [
            'min' => 0,
            'max' => 4,
            'label' => 'Risiko Rendah',
            'description' => 'Minimal depression symptoms',
        ],
        'sedang' => [
            'min' => 5,
            'max' => 9,
            'label' => 'Risiko Sedang',
            'description' => 'Mild to moderate depression',
        ],
        'tinggi' => [
            'min' => 10,
            'max' => 16,
            'label' => 'Risiko Tinggi',
            'description' => 'Moderate to severe depression',
        ],
    ],

    /**
     * Treatment recommendations berdasarkan level
     */
    'treatment_recommendations' => [
        'tinggi' => [
            'primary_interventions' => [
                'Psikoterapi individual (CBT, IPT, atau psychodynamic)',
                'Pertimbangkan farmakologi (rujuk psikiatri)',
                'Screening risiko suicidal dan self-harm',
            ],
            'secondary_interventions' => [
                'Behavioral activation',
                'Social support activation',
                'Occupational assessment',
            ],
            'monitoring' => [
                'Follow-up weekly',
                'Safety monitoring',
            ],
        ],
        'sedang' => [
            'primary_interventions' => [
                'Counseling supportif dan psychoeducation',
                'Behavioral activation dan problem-solving',
                'Lifestyle modification (sleep, exercise, nutrition)',
            ],
            'secondary_interventions' => [
                'Coping skills training',
                'Social support building',
            ],
            'monitoring' => [
                'Follow-up bi-weekly',
                'Symptom tracking',
            ],
        ],
        'rendah' => [
            'primary_interventions' => [
                'Psychoeducation dan prevention strategies',
                'Stress management dan coping skills',
                'Health promotion',
            ],
            'secondary_interventions' => [
                'Lifestyle support',
                'Peer support',
            ],
            'monitoring' => [
                'Follow-up as needed',
                'Self-monitoring',
            ],
        ],
    ],

    /**
     * Symptom clusters untuk analisis pola
     */
    'symptom_clusters' => [
        'mood_cognitive' => [
            'label' => 'Mood & Cognitive',
            'description' => 'Perubahan suasana hati dan berpikir',
            'keywords' => ['mood', 'guilt', 'hopeless', 'worthless', 'concentrate'],
        ],
        'physical' => [
            'label' => 'Physical',
            'description' => 'Gejala fisik',
            'keywords' => ['sleep', 'appetite', 'energy', 'fatigue', 'psychomotor'],
        ],
        'behavioral' => [
            'label' => 'Behavioral',
            'description' => 'Perubahan perilaku',
            'keywords' => ['activity', 'motivation', 'decision', 'concentration'],
        ],
        'social' => [
            'label' => 'Social',
            'description' => 'Fungsi sosial',
            'keywords' => ['social', 'withdraw', 'relationship', 'work'],
        ],
    ],

    /**
     * Pola interpretasi hasil screening
     */
    'pattern_profiles' => [
        'severe_persistent' => [
            'condition' => 'max_score >= 4 && avg_score >= 2.5',
            'interpretation' => 'Severe persistent pattern - perlu intervensi intensif',
            'recommendation_level' => 'urgent',
        ],
        'moderate_episodic' => [
            'condition' => 'max_score >= 3 && avg_score >= 2',
            'interpretation' => 'Moderate episodic pattern - perlu monitoring dan terapi',
            'recommendation_level' => 'standard',
        ],
        'mild_inconsistent' => [
            'condition' => 'max_score >= 2 && avg_score >= 1.5',
            'interpretation' => 'Mild inconsistent pattern - observasi dan support',
            'recommendation_level' => 'light',
        ],
        'minimal_symptoms' => [
            'condition' => 'max_score < 2',
            'interpretation' => 'Minimal symptoms - monitoring rutin',
            'recommendation_level' => 'preventive',
        ],
    ],

    /**
     * Crisis action items
     */
    'crisis_actions' => [
        'Rujuk ke layanan darurat psikiatri/IGD',
        'Evaluasi risiko bunuh diri dan self-harm',
        'Stabilisasi krisis segera',
        'Involve family/support system',
        'Establish safety plan',
        'Consider hospitalization if needed',
    ],

    /**
     * Sensitive symptoms yang memerlukan perhatian khusus
     */
    'sensitive_symptom_categories' => [
        'suicidal_ideation' => [
            'label' => 'Suicidal Ideation',
            'action_threshold' => 2,
            'recommended_action' => 'Immediate psychiatric referral',
        ],
        'self_harm' => [
            'label' => 'Self Harm',
            'action_threshold' => 2,
            'recommended_action' => 'Crisis intervention',
        ],
        'substance_abuse' => [
            'label' => 'Substance Abuse',
            'action_threshold' => 2,
            'recommended_action' => 'Addiction assessment and referral',
        ],
    ],

    /**
     * Risk score modifiers untuk generateRiskScore()
     */
    'risk_score_modifiers' => [
        'crisis_flag' => 30,
        'core_symptoms' => 15,
        'high_sensitive_symptoms' => 20,
        'family_history' => 10, // optional untuk future use
        'previous_episode' => 10, // optional untuk future use
    ],

    /**
     * Trend interpretation untuk comparison
     */
    'trend_interpretation' => [
        'improvement_threshold' => -5,
        'worsening_threshold' => 5,
        'improvement_text' => 'Kondisi menunjukkan perbaikan - pertahankan strategi treatment',
        'worsening_text' => 'Kondisi cenderung memburuk - tingkatkan monitoring',
        'stable_text' => 'Kondisi relatif stabil - lanjutkan plan yang ada',
        'significant_improvement' => 'Kondisi membaik signifikan - lanjutkan intervensi yang efektif',
        'significant_worsening' => 'Kondisi memburuk signifikan - perlu evaluasi dan intervensi segera',
    ],

    /**
     * Messaging templates untuk feedback
     */
    'feedback_templates' => [
        'crisis_warning' => '🚨 PERHATIAN: Hasil screening menunjukkan tanda-tanda krisis. Segera hubungi layanan kesehatan mental atau nomor darurat.',
        'high_risk_warning' => '⚠️ Hasil screening menunjukkan gejala depresi core yang signifikan.',
        'follow_up_advised' => '📋 Follow-up assessment direkomendasikan dalam 1-2 minggu.',
        'professional_consultation' => '👨‍⚕️ Konsultasikan dengan profesional kesehatan mental.',
    ],

    /**
     * Scoring metadata
     */
    'scoring' => [
        'max_possible_value_per_symptom' => 4,
        'min_possible_value_per_symptom' => 0,
        'export_formats' => ['csv', 'json', 'pdf'],
    ],

    /**
     * Retention policy
     */
    'data_retention' => [
        'screening_sessions_retention_days' => 365 * 5, // 5 tahun
        'auto_archive_after_days' => 365 * 2, // 2 tahun
    ],
];
