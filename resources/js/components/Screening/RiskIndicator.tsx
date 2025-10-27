import React from 'react';

interface Props {
    level: string;
    score: number;
}

const getLevelConfig = (level: string) => {
    const config: Record<string, { icon: string; color: string; bgColor: string; title: string }> = {
        minimal: {
            icon: '😊',
            color: 'text-green-600',
            bgColor: 'bg-green-100 border-green-300',
            title: 'Minimal',
        },
        ringan: {
            icon: '🙂',
            color: 'text-blue-600',
            bgColor: 'bg-blue-100 border-blue-300',
            title: 'Ringan',
        },
        sedang: {
            icon: '😟',
            color: 'text-yellow-600',
            bgColor: 'bg-yellow-100 border-yellow-300',
            title: 'Sedang',
        },
        berat: {
            icon: '😞',
            color: 'text-orange-600',
            bgColor: 'bg-orange-100 border-orange-300',
            title: 'Berat',
        },
        sangat_berat: {
            icon: '😢',
            color: 'text-red-600',
            bgColor: 'bg-red-100 border-red-300',
            title: 'Sangat Berat',
        },
    };

    return config[level] || config.minimal;
};

export default function RiskIndicator({ level, score }: Props) {
    const config = getLevelConfig(level);

    return (
        <div className={`p-6 rounded-lg border-2 ${config.bgColor} text-center`}>
            <div className="text-6xl mb-4">{config.icon}</div>

            <h2 className="text-xl font-bold text-gray-900 mb-2">Level Depresi</h2>

            <div className={`text-3xl font-bold ${config.color} mb-4`}>{config.title}</div>

            <div className="space-y-3">
                <div className="bg-white bg-opacity-50 rounded p-2">
                    <p className="text-xs text-gray-600">Indeks Risiko</p>
                    <p className="text-2xl font-bold text-gray-900">{Math.round(score)}/100</p>
                </div>

                <div className="flex flex-wrap justify-center gap-2">
                    {level === 'sangat_berat' && (
                        <span className="inline-block px-3 py-1 bg-red-200 text-red-800 text-xs font-semibold rounded-full">
                            ⚠️ Butuh Intervensi Segera
                        </span>
                    )}
                    {level === 'berat' && (
                        <span className="inline-block px-3 py-1 bg-orange-200 text-orange-800 text-xs font-semibold rounded-full">
                            ⚠️ Butuh Konsultasi
                        </span>
                    )}
                    {level === 'sedang' && (
                        <span className="inline-block px-3 py-1 bg-yellow-200 text-yellow-800 text-xs font-semibold rounded-full">
                            ℹ️ Perlu Perhatian
                        </span>
                    )}
                </div>
            </div>
        </div>
    );
}
