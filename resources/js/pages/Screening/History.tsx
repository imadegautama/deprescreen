import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import type { ScreeningSession } from '@/types';

interface PaginatedData {
    data: ScreeningSession[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    sessions: PaginatedData;
}

const getLevelColor = (level: string) => {
    const colors: Record<string, string> = {
        minimal: 'bg-green-100 text-green-800',
        ringan: 'bg-blue-100 text-blue-800',
        sedang: 'bg-yellow-100 text-yellow-800',
        berat: 'bg-orange-100 text-orange-800',
        sangat_berat: 'bg-red-100 text-red-800',
    };
    return colors[level] || 'bg-gray-100 text-gray-800';
};

const getLevelLabel = (level: string) => {
    const labels: Record<string, string> = {
        minimal: 'Minimal',
        ringan: 'Ringan',
        sedang: 'Sedang',
        berat: 'Berat',
        sangat_berat: 'Sangat Berat',
    };
    return labels[level] || level;
};

const getScorePercentage = (score: number) => {
    const maxScore = 144; // Approximate max for most screening tests
    return Math.min(Math.round((score / maxScore) * 100), 100);
};

export default function ScreeningHistory({ sessions }: Props) {
    const [expandedId, setExpandedId] = useState<string | null>(null);

    if (sessions.data.length === 0) {
        return (
            <>
                <Head title="Riwayat Screening" />
                <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 py-8 px-4 sm:px-6 lg:px-8">
                    <div className="max-w-4xl mx-auto">
                        <div className="text-center mb-8">
                            <h1 className="text-4xl font-bold text-gray-900 mb-2">Riwayat Screening</h1>
                            <p className="text-gray-600">Belum ada riwayat screening</p>
                        </div>

                        <div className="text-center">
                            <Link
                                href="/screening"
                                className="inline-block px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors"
                            >
                                Mulai Screening Sekarang
                            </Link>
                        </div>
                    </div>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Riwayat Screening" />

            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 py-8 px-4 sm:px-6 lg:px-8">
                <div className="max-w-4xl mx-auto">
                    {/* Header */}
                    <div className="text-center mb-8">
                        <h1 className="text-4xl font-bold text-gray-900 mb-2">Riwayat Screening</h1>
                        <p className="text-gray-600">
                            Total: {sessions.total} screening telah dilakukan
                        </p>
                    </div>

                    {/* Sessions List */}
                    <div className="space-y-4 mb-8">
                        {sessions.data.map((session) => (
                            <div
                                key={session.screening_session_id}
                                className="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow"
                            >
                                {/* Session Header */}
                                <button
                                    onClick={() =>
                                        setExpandedId(
                                            expandedId === session.screening_session_id
                                                ? null
                                                : session.screening_session_id
                                        )
                                    }
                                    className="w-full p-4 flex items-center justify-between hover:bg-gray-50 transition-colors"
                                >
                                    <div className="text-left flex-1">
                                        <p className="text-sm text-gray-600">
                                            {new Date(session.created_at).toLocaleDateString('id-ID', {
                                                weekday: 'long',
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            })}
                                        </p>
                                        <h3 className="text-lg font-semibold text-gray-900 mt-1">
                                            Hasil Screening
                                        </h3>
                                    </div>

                                    <div className="flex items-center gap-4 ml-4">
                                        <div className="text-right">
                                            <p className="text-2xl font-bold text-gray-900">{session.score}</p>
                                            <p className="text-xs text-gray-600">Poin</p>
                                        </div>

                                        <span
                                            className={`px-3 py-1 rounded-full text-sm font-semibold ${getLevelColor(session.level)}`}
                                        >
                                            {getLevelLabel(session.level)}
                                        </span>

                                        {session.crisis_flag && (
                                            <span className="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">
                                                ⚠️ Krisis
                                            </span>
                                        )}

                                        <svg
                                            className={`w-5 h-5 text-gray-500 transition-transform ${
                                                expandedId === session.screening_session_id ? 'rotate-180' : ''
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
                                    </div>
                                </button>

                                {/* Expanded Details */}
                                {expandedId === session.screening_session_id && (
                                    <div className="border-t border-gray-200 bg-gray-50 p-4">
                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                            {/* Persentase */}
                                            <div>
                                                <p className="text-sm text-gray-600 mb-1">Tingkat Keparahan</p>
                                                <div className="flex items-center gap-2">
                                                    <div className="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                        <div
                                                            className="h-full bg-gradient-to-r from-green-500 to-red-500"
                                                            style={{
                                                                width: `${getScorePercentage(session.score)}%`,
                                                            }}
                                                        ></div>
                                                    </div>
                                                    <span className="font-semibold text-gray-900">
                                                        {getScorePercentage(session.score)}%
                                                    </span>
                                                </div>
                                            </div>

                                            {/* Core Symptoms */}
                                            <div>
                                                <p className="text-sm text-gray-600 mb-1">Gejala Inti</p>
                                                <p className="text-lg font-semibold text-red-600">
                                                    {session.has_core ? '✓ Ada' : '✗ Tidak Ada'}
                                                </p>
                                            </div>
                                        </div>

                                        {/* Actions */}
                                        <div className="flex gap-2">
                                            <Link
                                                href={`/screening/${session.screening_session_id}/result`}
                                                className="flex-1 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg text-center transition-colors"
                                            >
                                                Lihat Detail
                                            </Link>
                                        </div>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>

                    {/* Pagination Info */}
                    {sessions.last_page > 1 && (
                        <div className="text-center text-sm text-gray-600 mb-8">
                            Halaman {sessions.current_page} dari {sessions.last_page}
                        </div>
                    )}

                    {/* Action Buttons */}
                    <div className="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            href="/screening"
                            className="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-center transition-colors"
                        >
                            Screening Baru
                        </Link>
                        <Link
                            href="/"
                            className="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-900 font-semibold rounded-lg text-center transition-colors"
                        >
                            Kembali ke Beranda
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
