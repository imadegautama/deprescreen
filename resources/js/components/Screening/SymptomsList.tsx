import React, { useState } from 'react';
import { Answer } from '@/types';

interface Props {
    activeSymptoms: Answer[];
    coreCount: number;
}

export default function SymptomsList({ activeSymptoms, coreCount }: Props) {
    const [expanded, setExpanded] = useState(false);

    const getSeverityColor = (value: number) => {
        if (value === 1) return 'bg-green-100 text-green-800 border-green-300';
        if (value === 2) return 'bg-yellow-100 text-yellow-800 border-yellow-300';
        if (value === 3) return 'bg-orange-100 text-orange-800 border-orange-300';
        return 'bg-red-100 text-red-800 border-red-300';
    };

    const getSeverityLabel = (value: number) => {
        if (value === 1) return 'Jarang';
        if (value === 2) return 'Kadang-kadang';
        if (value === 3) return 'Sering';
        return 'Sangat Sering';
    };

    const coreSymptoms = activeSymptoms.filter((a) => a.symptom?.is_core);
    const nonCoreSymptoms = activeSymptoms.filter((a) => !a.symptom?.is_core);

    return (
        <div className="mb-8 p-6 bg-white rounded-lg shadow-md border border-gray-200">
            <div className="flex items-center justify-between mb-4">
                <h2 className="text-2xl font-bold text-gray-900">Gejala yang Terdeteksi</h2>
                <button
                    onClick={() => setExpanded(!expanded)}
                    className="text-sm text-indigo-600 hover:text-indigo-700 font-medium"
                >
                    {expanded ? 'Sembunyikan' : 'Lihat Detail'} ({activeSymptoms.length})
                </button>
            </div>

            {/* Core Symptoms */}
            {coreSymptoms.length > 0 && (
                <div className="mb-6">
                    <h3 className="font-semibold text-red-700 mb-3 flex items-center gap-2">
                        <span className="text-lg">🔴</span>
                        Gejala Inti ({coreCount})
                    </h3>
                    <div className="space-y-2">
                        {coreSymptoms.map((answer) => (
                            <div
                                key={answer.answer_id}
                                className="p-3 bg-red-50 border border-red-200 rounded-lg"
                            >
                                <div className="flex items-start justify-between">
                                    <div>
                                        <p className="font-medium text-gray-900">{answer.symptom?.name}</p>
                                        {expanded && (
                                            <p className="text-sm text-gray-600 mt-1">{answer.symptom?.description}</p>
                                        )}
                                    </div>
                                    <span
                                        className={`ml-2 px-2 py-1 rounded text-xs font-semibold border ${getSeverityColor(answer.value)}`}
                                    >
                                        {getSeverityLabel(answer.value)}
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Non-Core Symptoms */}
            {nonCoreSymptoms.length > 0 && (
                <div>
                    <h3 className="font-semibold text-blue-700 mb-3 flex items-center gap-2">
                        <span className="text-lg">🔵</span>
                        Gejala Pendukung ({nonCoreSymptoms.length})
                    </h3>
                    <div className="space-y-2">
                        {nonCoreSymptoms.map((answer) => (
                            <div key={answer.answer_id} className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div className="flex items-start justify-between">
                                    <div>
                                        <p className="font-medium text-gray-900">{answer.symptom?.name}</p>
                                        {expanded && (
                                            <p className="text-sm text-gray-600 mt-1">{answer.symptom?.description}</p>
                                        )}
                                    </div>
                                    <span
                                        className={`ml-2 px-2 py-1 rounded text-xs font-semibold border ${getSeverityColor(answer.value)}`}
                                    >
                                        {getSeverityLabel(answer.value)}
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {activeSymptoms.length === 0 && (
                <div className="text-center py-8 text-gray-600">
                    <p>Tidak ada gejala yang terdeteksi pada jawaban Anda.</p>
                </div>
            )}
        </div>
    );
}
