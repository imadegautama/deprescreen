import React from 'react';
import { ScreeningStats, Threshold } from '@/types';

interface Props {
    stats: ScreeningStats;
    threshold?: Threshold;
}

export default function ResultSummary({ stats, threshold }: Props) {
    const getScoreColor = (percentage: number) => {
        if (percentage <= 25) return 'text-green-600';
        if (percentage <= 50) return 'text-yellow-600';
        if (percentage <= 75) return 'text-orange-600';
        return 'text-red-600';
    };

    const getScoreBgColor = (percentage: number) => {
        if (percentage <= 25) return 'bg-green-50 border-green-300';
        if (percentage <= 50) return 'bg-yellow-50 border-yellow-300';
        if (percentage <= 75) return 'bg-orange-50 border-orange-300';
        return 'bg-red-50 border-red-300';
    };

    return (
        <div className={`p-6 rounded-lg border-2 ${getScoreBgColor(stats.percentage)}`}>
            <h2 className="text-2xl font-bold text-gray-900 mb-4">Ringkasan Skor</h2>

            <div className="space-y-4">
                {/* Total Score */}
                <div>
                    <p className="text-sm text-gray-600 mb-1">Total Skor</p>
                    <p className={`text-4xl font-bold ${getScoreColor(stats.percentage)}`}>
                        {stats.total_score}
                    </p>
                    <p className="text-sm text-gray-600">dari {stats.max_possible_score}</p>
                </div>

                {/* Percentage */}
                <div>
                    <p className="text-sm text-gray-600 mb-1">Tingkat Keparahan</p>
                    <p className={`text-3xl font-bold ${getScoreColor(stats.percentage)}`}>
                        {stats.percentage}%
                    </p>
                </div>

                {/* Core Symptoms */}
                <div className="pt-2 border-t">
                    <p className="text-sm text-gray-600 mb-1">Gejala Inti yang Terdeteksi</p>
                    <p className="text-2xl font-bold text-red-600">{stats.core_symptoms_count}</p>
                </div>

                {/* Risk Score */}
                <div className="pt-2 border-t">
                    <p className="text-sm text-gray-600 mb-1">Skor Risiko</p>
                    <div className="flex items-center gap-2">
                        <div className="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div
                                className="h-full bg-gradient-to-r from-green-500 to-red-500"
                                style={{ width: `${stats.risk_score}%` }}
                            ></div>
                        </div>
                        <span className="font-semibold text-gray-900">{Math.round(stats.risk_score)}%</span>
                    </div>
                </div>
            </div>
        </div>
    );
}
