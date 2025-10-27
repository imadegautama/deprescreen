import React, { useState } from 'react';
import { Symptom } from '@/types';

interface ScreeningFormProps {
    symptoms: Symptom[];
    answers: Record<string, number>;
    onAnswerChange: (symptomId: string, value: number) => void;
    onSubmit: (e: React.FormEvent) => void;
    loading: boolean;
    onStepChange?: (step: number) => void;
    currentStep?: number;
}

// Scale labels untuk G1-G8 (0-2)
const SCALE_LABELS_G1_TO_G8 = [
    { value: 0, label: 'Tidak', color: 'gray' },
    { value: 1, label: 'Kadang', color: 'yellow' },
    { value: 2, label: 'Sering', color: 'red' },
];

// Scale labels untuk G9 (0-1 boolean)
const SCALE_LABELS_G9 = [
    { value: 0, label: 'Tidak', color: 'gray' },
    { value: 1, label: 'Ya', color: 'red' },
];

const getColorClass = (value: number, severity: 'low' | 'medium' | 'high') => {
    const colors = {
        low: {
            gray: 'bg-gray-100 text-gray-900 border-gray-300',
            yellow: 'bg-yellow-100 text-yellow-900 border-yellow-300',
            red: 'bg-red-100 text-red-900 border-red-300',
        },
        medium: {
            gray: 'bg-gray-100 text-gray-900 border-gray-300',
            yellow: 'bg-yellow-100 text-yellow-900 border-yellow-300',
            red: 'bg-orange-100 text-orange-900 border-orange-300',
        },
        high: {
            gray: 'bg-gray-100 text-gray-900 border-gray-300',
            yellow: 'bg-orange-100 text-orange-900 border-orange-300',
            red: 'bg-red-100 text-red-900 border-red-300',
        },
    };

    return colors[severity][value === 0 ? 'gray' : value === 1 ? 'yellow' : 'red'] || colors[severity].gray;
};

export default function ScreeningForm({
    symptoms,
    answers,
    onAnswerChange,
    onSubmit,
    loading,
}: ScreeningFormProps) {
    const [expandedSymptom, setExpandedSymptom] = useState<string | null>(null);

    // Sort symptoms by order
    const sortedSymptoms = [...symptoms].sort((a, b) => (a.order || 0) - (b.order || 0));

    const getScaleLabels = (symptom: Symptom) => {
        return symptom.type === 'boolean' ? SCALE_LABELS_G9 : SCALE_LABELS_G1_TO_G8;
    };

    const getSeverity = (symptom: Symptom): 'low' | 'medium' | 'high' => {
        // For G1 and G2 (core symptoms), they're high priority
        if (symptom.code === 'G1' || symptom.code === 'G2') {
            return 'high';
        }
        return 'low';
    };

    return (
        <form onSubmit={onSubmit} className="space-y-6">
            {/* All Symptoms */}
            <div className="space-y-4">
                {sortedSymptoms.map((symptom, index) => {
                    const scaleLabels = getScaleLabels(symptom);
                    const severity = getSeverity(symptom);
                    const selectedValue = answers[symptom.symptom_id] || 0;

                    return (
                        <div
                            key={symptom.symptom_id}
                            className="bg-white rounded-lg shadow-md border-2 border-gray-200 overflow-hidden hover:shadow-lg transition-shadow"
                        >
                            {/* Symptom Header */}
                            <button
                                type="button"
                                onClick={() =>
                                    setExpandedSymptom(
                                        expandedSymptom === symptom.symptom_id ? null : symptom.symptom_id
                                    )
                                }
                                className={`w-full p-4 flex items-start justify-between hover:bg-gray-50 transition-colors ${
                                    symptom.code === 'G1' || symptom.code === 'G2'
                                        ? 'border-l-4 border-red-500'
                                        : symptom.is_sensitive
                                          ? 'border-l-4 border-orange-500'
                                          : ''
                                }`}
                            >
                                <div className="text-left flex-1">
                                    <div className="flex items-center gap-2">
                                        <span className="font-bold text-lg text-indigo-600 bg-indigo-50 px-2 py-1 rounded">
                                            {index + 1}. {symptom.code}
                                        </span>
                                        {(symptom.code === 'G1' || symptom.code === 'G2') && (
                                            <span className="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded">
                                                Core Symptom
                                            </span>
                                        )}
                                        {symptom.is_sensitive && (
                                            <span className="px-2 py-1 text-xs font-semibold bg-orange-100 text-orange-800 rounded">
                                                Sensitive
                                            </span>
                                        )}
                                    </div>
                                    <h3 className="text-base font-semibold text-gray-900 mt-2">
                                        {symptom.label}
                                    </h3>
                                </div>

                                {/* Selected Value Display */}
                                {selectedValue !== 0 && (
                                    <div
                                        className={`ml-4 px-3 py-1 rounded font-semibold text-sm ${getColorClass(selectedValue, severity)}`}
                                    >
                                        {scaleLabels[selectedValue]?.label || ''}
                                    </div>
                                )}

                                {/* Expand Icon */}
                                <svg
                                    className={`ml-2 w-5 h-5 text-gray-500 transition-transform ${
                                        expandedSymptom === symptom.symptom_id ? 'rotate-180' : ''
                                    }`}
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M19 14l-7 7m0 0l-7-7m7 7V3"
                                    />
                                </svg>
                            </button>

                            {/* Expanded Content */}
                            {expandedSymptom === symptom.symptom_id && (
                                <div className="border-t border-gray-200 bg-gray-50 p-4">
                                    {/* Description */}
                                    <p className="text-gray-700 mb-4 italic">{symptom.description}</p>

                                    {/* Scale Options */}
                                    <div className="space-y-2">
                                        <label className="block text-sm font-medium text-gray-700 mb-3">
                                            {symptom.type === 'boolean'
                                                ? 'Apakah Anda mengalami hal ini?'
                                                : 'Seberapa sering Anda mengalami hal ini?'}
                                        </label>
                                        <div className="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                            {scaleLabels.map((scale) => (
                                                <button
                                                    key={scale.value}
                                                    type="button"
                                                    onClick={() =>
                                                        onAnswerChange(symptom.symptom_id, scale.value)
                                                    }
                                                    className={`p-3 rounded font-semibold text-sm transition-all border-2 ${
                                                        selectedValue === scale.value
                                                            ? `${getColorClass(scale.value, severity)} border-current`
                                                            : 'bg-white text-gray-700 border-gray-200 hover:border-indigo-300'
                                                    }`}
                                                >
                                                    {scale.label}
                                                </button>
                                            ))}
                                        </div>
                                        {symptom.type !== 'boolean' && (
                                            <p className="text-xs text-gray-500 mt-2">
                                                0 = Tidak, 1 = Kadang, 2 = Sering
                                            </p>
                                        )}
                                    </div>
                                </div>
                            )}
                        </div>
                    );
                })}
            </div>

            {/* Submit Button */}
            <button
                type="submit"
                disabled={loading}
                className="w-full px-6 py-3 text-lg font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2"
            >
                {loading && (
                    <svg className="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle
                            className="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            strokeWidth="4"
                        ></circle>
                        <path
                            className="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                )}
                {loading ? 'Memproses Hasil...' : 'Kirim & Lihat Hasil'}
            </button>
        </form>
    );
}
