export interface Auth {
    user: User;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

// ============ SCREENING TYPES ============

export interface Symptom {
    symptom_id: string;
    code: string;
    label: string;
    description: string;
    is_core: boolean;
    is_sensitive: boolean;
    type: 'scale' | 'boolean';  // 'scale' for G1-G8 (0-2), 'boolean' for G9 (0-1)
    order?: number;
    created_at: string;
    updated_at: string;
}

export interface Answer {
    answer_id: string;
    session_id: string;
    symptom_id: string;
    value: number;
    created_at: string;
    updated_at: string;
    symptom?: Symptom;
}

export interface Threshold {
    threshold_id: string;
    level: 'rendah' | 'sedang' | 'tinggi';
    min_score: number;
    max_score: number;
    label: string;
    description: string;
    color: string;
    icon: string;
    created_at: string;
    updated_at: string;
}

export interface ScreeningSession {
    screening_session_id: string;
    score: number;
    level: string;
    has_core: boolean;
    crisis_flag: boolean;
    created_at: string;
    updated_at: string;
    answers?: Answer[];
    threshold?: Threshold;
}

export interface ScreeningStats {
    total_score: number;
    max_possible_score: number;
    percentage: number;
    core_symptoms_count: number;
    has_crisis: boolean;
    risk_score: number;
}

export interface SymptomAnalysis {
    pattern_type: string;
    severity: string;
    affected_areas: string[];
    recovery_potential: string;
}

export interface Recommendation {
    type: string;
    title: string;
    description: string;
    priority: 'high' | 'medium' | 'low';
    action_url?: string;
}

export interface ScreeningResult {
    session: ScreeningSession;
    threshold: Threshold;
    activeSymptoms: Answer[];
    stats: ScreeningStats;
    advice: string;
    symptomAnalysis: SymptomAnalysis;
    recommendations: Recommendation[];
}
