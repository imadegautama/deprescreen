import React from 'react';

interface WelcomeModalProps {
    onClose: () => void;
    totalSymptoms: number;
}

export default function WelcomeModal({ onClose, totalSymptoms }: WelcomeModalProps) {
    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-lg shadow-xl max-w-md w-full p-6 sm:p-8">
                <div className="text-center">
                    <div className="mx-auto w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                        <svg
                            className="w-6 h-6 text-indigo-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>

                    <h2 className="text-2xl font-bold text-gray-900 mb-2">Selamat Datang</h2>
                    <p className="text-gray-600 mb-6">
                        Asesmen ini dirancang untuk membantu Anda mengevaluasi kesehatan mental dan mendeteksi
                        tanda-tanda depresi secara dini.
                    </p>

                    <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
                        <h3 className="font-semibold text-blue-900 mb-2">Petunjuk Penggunaan:</h3>
                        <ul className="text-sm text-blue-800 space-y-1">
                            <li>✓ Jawab {totalSymptoms} pertanyaan dengan jujur</li>
                            <li>✓ Estimasi waktu: 5-10 menit</li>
                            <li>✓ Skala 0-4 dari tidak sama sekali hingga sangat sering</li>
                            <li>✓ Hasil tidak menggantikan diagnosis profesional</li>
                        </ul>
                    </div>

                    <button
                        onClick={onClose}
                        className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200"
                    >
                        Mulai Asesmen
                    </button>
                </div>
            </div>
        </div>
    );
}
