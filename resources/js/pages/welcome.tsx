import { Head } from '@inertiajs/react';

export default function Welcome() {
    return (
        <>
            <Head title="Deprescreen">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 py-8 px-4 sm:px-6 lg:px-8">
                <div className="max-w-4xl mx-auto">
                    {/* Header */}
                    <div className="text-center mb-16">
                        <h1 className="text-5xl sm:text-6xl font-bold text-gray-900 mb-4">
                            Sistem Pakar Screening Depresi
                        </h1>
                        <p className="text-xl text-gray-600 mb-8">
                            Deteksi dini depresi melalui asesmen kesehatan mental yang komprehensif
                        </p>
                        <a
                            href="/screening"
                            className="inline-block px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-lg font-semibold rounded-lg transition-colors"
                        >
                            Mulai Screening Sekarang
                        </a>
                    </div>

                    {/* Features */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                        <div className="bg-white p-8 rounded-lg shadow-md border border-gray-200">
                            <div className="text-4xl mb-4">🧠</div>
                            <h3 className="text-xl font-bold text-gray-900 mb-2">Berbasis Expert System</h3>
                            <p className="text-gray-600">
                                Menggunakan sistem pakar yang dirancang oleh profesional kesehatan mental
                                untuk hasil yang akurat.
                            </p>
                        </div>

                        <div className="bg-white p-8 rounded-lg shadow-md border border-gray-200">
                            <div className="text-4xl mb-4">⚡</div>
                            <h3 className="text-xl font-bold text-gray-900 mb-2">Cepat & Mudah</h3>
                            <p className="text-gray-600">
                                Selesaikan screening hanya dalam 5-10 menit dengan antarmuka yang
                                user-friendly.
                            </p>
                        </div>

                        <div className="bg-white p-8 rounded-lg shadow-md border border-gray-200">
                            <div className="text-4xl mb-4">📊</div>
                            <h3 className="text-xl font-bold text-gray-900 mb-2">Hasil Terperinci</h3>
                            <p className="text-gray-600">
                                Dapatkan analisis mendalam tentang gejala Anda dan rekomendasi langkah
                                selanjutnya.
                            </p>
                        </div>
                    </div>

                    {/* How It Works */}
                    <div className="bg-white p-12 rounded-lg shadow-md border border-gray-200 mb-16">
                        <h2 className="text-3xl font-bold text-gray-900 mb-8 text-center">Cara Kerjanya</h2>
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div className="text-center">
                                <div className="mx-auto w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                                    <span className="text-2xl font-bold text-indigo-600">1</span>
                                </div>
                                <h3 className="font-semibold text-gray-900 mb-2">Jawab Pertanyaan</h3>
                                <p className="text-sm text-gray-600">
                                    Jawab pertanyaan seputar gejala dan kondisi Anda
                                </p>
                            </div>

                            <div className="text-center">
                                <div className="mx-auto w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                                    <span className="text-2xl font-bold text-indigo-600">2</span>
                                </div>
                                <h3 className="font-semibold text-gray-900 mb-2">Analisis</h3>
                                <p className="text-sm text-gray-600">Sistem pakar menganalisis jawaban Anda</p>
                            </div>

                            <div className="text-center">
                                <div className="mx-auto w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                                    <span className="text-2xl font-bold text-indigo-600">3</span>
                                </div>
                                <h3 className="font-semibold text-gray-900 mb-2">Hasil</h3>
                                <p className="text-sm text-gray-600">
                                    Dapatkan hasil screening dan tingkat keparahan
                                </p>
                            </div>

                            <div className="text-center">
                                <div className="mx-auto w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                                    <span className="text-2xl font-bold text-indigo-600">4</span>
                                </div>
                                <h3 className="font-semibold text-gray-900 mb-2">Rekomendasi</h3>
                                <p className="text-sm text-gray-600">Terima rekomendasi tindakan selanjutnya</p>
                            </div>
                        </div>
                    </div>

                    {/* Important Note */}
                    <div className="bg-blue-50 border-2 border-blue-300 p-8 rounded-lg mb-16">
                        <h3 className="text-lg font-bold text-blue-900 mb-4">⚠️ Penting untuk Diketahui</h3>
                        <ul className="text-blue-800 space-y-2">
                            <li>✓ Screening ini adalah alat bantu edukasi, bukan diagnosis resmi</li>
                            <li>✓ Hasil harus diverifikasi oleh profesional kesehatan mental</li>
                            <li>✓ Jika Anda dalam krisis, segera hubungi layanan darurat</li>
                            <li>✓ Data Anda dijaga kerahasiaannya sesuai standar privasi</li>
                        </ul>
                    </div>

                    {/* CTA */}
                    <div className="text-center">
                        <div className="mb-8">
                            <a
                                href="/screening"
                                className="inline-block px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-lg font-semibold rounded-lg transition-colors"
                            >
                                Mulai Screening Sekarang
                            </a>
                        </div>
                        <a
                            href="/screening/history"
                            className="inline-block px-8 py-3 bg-gray-200 hover:bg-gray-300 text-gray-900 font-semibold rounded-lg transition-colors ml-4"
                        >
                            Lihat Riwayat Screening
                        </a>
                    </div>

                    {/* Footer */}
                    <div className="text-center mt-16 text-gray-600">
                        <p className="text-sm">
                            Dibuat dengan ❤️ untuk kesehatan mental yang lebih baik
                        </p>
                        <p className="text-xs mt-2">
                            © 2025 Depresi Screening System. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
