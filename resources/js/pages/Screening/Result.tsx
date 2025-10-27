import React from 'react';
import { Head, Link } from '@inertiajs/react';
import type { ScreeningSession, Answer, ScreeningStats } from '@/types';

interface Props {
    session: ScreeningSession;
    threshold: Record<string, unknown>;
    activeSymptoms: Answer[];
    stats: ScreeningStats;
    advice: string;
    symptomAnalysis: Record<string, unknown>;
    recommendations: string[];
}

const getLevelBadgeColor = (level: string) => {
    const colors = {
        rendah: 'bg-green-100 text-green-800 border-green-300',
        sedang: 'bg-yellow-100 text-yellow-800 border-yellow-300',
        tinggi: 'bg-red-100 text-red-800 border-red-300',
    };
    return colors[level as keyof typeof colors] || colors.rendah;
};

const getLevelIcon = (level: string) => {
    const icons = {
        rendah: '✓',
        sedang: '⚠',
        tinggi: '⚠',
    };
    return icons[level as keyof typeof icons] || '✓';
};

export default function ScreeningResult({
    session,
    threshold,
    activeSymptoms,
    stats,
    advice,
    recommendations,
}: Props) {
    return (
        <>
            <Head title="Hasil Screening" />

            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 py-8 px-4 sm:px-6 lg:px-8">
                <div className="max-w-4xl mx-auto">
                    {/* Header */}
                    <div className="text-center mb-8">
                        <h1 className="text-4xl font-bold text-gray-900 mb-2">Hasil Screening Anda</h1>
                        <p className="text-gray-600">
                            Tanggal: {new Date(session.created_at).toLocaleDateString('id-ID', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                            })}
                        </p>
                    </div>

                    {/* Crisis Alert */}
                    {stats.has_crisis && (
                        <div className="mb-6 p-6 bg-red-50 border-2 border-red-500 rounded-lg shadow-md">
                            <div className="flex items-start gap-4">
                                <div className="text-4xl">🚨</div>
                                <div>
                                    <h3 className="font-bold text-red-900 mb-2 text-lg">PERHATIAN: KRISIS TERDETEKSI</h3>
                                    <p className="text-red-800 mb-3">
                                        {advice}
                                    </p>
                                    <div className="space-y-2 text-sm text-red-800">
                                        <p>🔴 Segera hubungi:</p>
                                        <p>• Layanan kesehatan mental terdekat</p>
                                        <p>• Rumah sakit dengan psikiatri</p>
                                        <p>• Nomor darurat lokal (118/119)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Main Result Card - Level Badge */}
                    <div className="mb-8 p-8 bg-white rounded-lg shadow-lg border-l-8" style={{
                        borderLeftColor: threshold.color === 'green' ? '#10b981' : threshold.color === 'yellow' ? '#eab308' : '#ef4444'
                    }}>
                        <div className="flex items-center justify-between mb-6">
                            <div>
                                <p className="text-gray-600 text-sm font-medium mb-2">HASIL SCREENING</p>
                                <h2 className="text-4xl font-bold text-gray-900">{session.level.toUpperCase()}</h2>
                            </div>
                            <div className={`px-6 py-4 rounded-full border-2 text-center ${getLevelBadgeColor(session.level)}`}>
                                <div className="text-3xl font-bold mb-1">{getLevelIcon(session.level)}</div>
                                <p className="text-sm font-semibold">{String(threshold.label) || 'Unknown'}</p>
                            </div>
                        </div>

                        {/* Score Progress */}
                        <div className="mb-6">
                            <div className="flex justify-between mb-2">
                                <span className="text-sm font-medium text-gray-700">Skor</span>
                                <span className="text-sm font-bold text-gray-900">{stats.total_score} / {stats.max_possible_score}</span>
                            </div>
                            <div className="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                                <div
                                    className={`h-full transition-all duration-500 ${
                                        session.level === 'rendah'
                                            ? 'bg-green-500'
                                            : session.level === 'sedang'
                                              ? 'bg-yellow-500'
                                              : 'bg-red-500'
                                    }`}
                                    style={{
                                        width: `${(stats.total_score / stats.max_possible_score) * 100}%`,
                                    }}
                                />
                            </div>
                            <p className="text-sm text-gray-600 mt-2">{stats.percentage.toFixed(0)}% dari skor maksimal</p>
                        </div>

                        {/* Threshold Info */}
                        <div className="p-4 bg-gray-50 rounded border border-gray-200">
                            <p className="text-sm text-gray-700">
                                <span className="font-semibold">Tingkat Risiko:</span> {String(threshold.description) || 'Unknown'}
                            </p>
                        </div>
                    </div>

                    {/* Advice Box */}
                    {!stats.has_crisis && (
                        <div className="mb-8 p-6 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg">
                            <h3 className="font-semibold text-blue-900 mb-2 flex items-center gap-2">
                                <span>💡</span> Rekomendasi
                            </h3>
                            <p className="text-blue-800 leading-relaxed">{advice}</p>
                        </div>
                    )}

                    {/* Core Symptoms Indicator */}
                    {session.has_core && (
                        <div className="mb-8 p-4 bg-orange-50 border border-orange-300 rounded-lg">
                            <p className="text-orange-800 flex items-center gap-2">
                                <span className="text-lg">⚠️</span>
                                <span>
                                    <strong>Gejala Inti Terdeteksi:</strong> Perasaan sedih yang mendalam dan kehilangan minat/kesenangan hadir bersamaan. Ini memerlukan perhatian khusus.
                                </span>
                            </p>
                        </div>
                    )}

                    {/* Active Symptoms */}
                    {activeSymptoms.length > 0 && (
                        <div className="mb-8 p-6 bg-white rounded-lg shadow-md border border-gray-200">
                            <h2 className="text-2xl font-bold text-gray-900 mb-4">Gejala yang Dialami</h2>
                            <div className="space-y-3">
                                {activeSymptoms.map((answer) => (
                                    <div key={answer.answer_id} className="flex items-start gap-3 p-3 bg-gray-50 rounded border border-gray-200">
                                        <div className="flex-shrink-0">
                                            {answer.value === 1 ? (
                                                <span className="text-yellow-500 text-lg">●</span>
                                            ) : answer.value === 2 ? (
                                                <span className="text-red-500 text-lg">●</span>
                                            ) : (
                                                <span className="text-gray-400 text-lg">○</span>
                                            )}
                                        </div>
                                        <div className="flex-1">
                                            <p className="font-medium text-gray-900">
                                                {answer.symptom?.code}: {answer.symptom?.label}
                                            </p>
                                            <p className="text-sm text-gray-600">
                                                {answer.value === 0 ? 'Tidak' : answer.value === 1 ? 'Kadang' : answer.value === 2 ? 'Sering' : 'Ya'}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Recommendations */}
                    {recommendations && recommendations.length > 0 && (
                        <div className="mb-8 p-6 bg-white rounded-lg shadow-md border border-gray-200">
                            <h2 className="text-2xl font-bold text-gray-900 mb-4">Langkah Selanjutnya</h2>
                            <ul className="space-y-3">
                                {recommendations.map((rec, idx) => (
                                    <li key={idx} className="flex gap-3">
                                        <span className="text-indigo-600 font-bold flex-shrink-0">{idx + 1}.</span>
                                        <span className="text-gray-700">{rec}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    {/* Action Buttons */}
                    <div className="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            href="/screening"
                            className="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-center transition-colors"
                        >
                            Lakukan Screening Lagi
                        </Link>
                        <Link
                            href="/screening/history"
                            className="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-900 font-semibold rounded-lg text-center transition-colors"
                        >
                            Lihat Riwayat
                        </Link>
                    </div>

                    {/* Disclaimer */}
                    <div className="mt-8 p-4 bg-gray-100 rounded-lg border border-gray-300">
                        <p className="text-xs text-gray-700">
                            <span className="font-semibold">📋 Disclaimer:</span> Hasil screening ini hanya untuk tujuan
                            edukasi dan tidak menggantikan diagnosis profesional. Silakan konsultasikan hasil ini dengan
                            tenaga profesional kesehatan mental untuk diagnosis dan perawatan yang akurat.
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
