<?php

namespace Database\Seeders;

use App\Models\Threshold;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ThresholdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $thresholds = [
            [
                'level' => 'rendah',
                'min_score' => 0,
                'max_score' => 4,
                'advice_text' => 'Kondisi emosional Anda tergolong stabil. Tetap jaga pola hidup sehat, tidur cukup, dan luangkan waktu untuk aktivitas yang menyenangkan.',
            ],
            [
                'level' => 'sedang',
                'min_score' => 5,
                'max_score' => 9,
                'advice_text' => 'Tanda-tanda stres atau gejala ringan mulai muncul. Pertimbangkan berbicara dengan teman dekat, konselor, atau psikolog agar tidak berkembang lebih jauh.',
            ],
            [
                'level' => 'tinggi',
                'min_score' => 10,
                'max_score' => 16,
                'advice_text' => 'Kemungkinan gejala depresi cukup signifikan. Sebaiknya segera konsultasi dengan tenaga profesional (psikolog atau psikiater) untuk mendapatkan penanganan lebih lanjut.',
            ],
        ];


        foreach ($thresholds as $threshold) {
            Threshold::updateOrCreate(
                [
                    'level' => $threshold['level'],
                    'min_score' => $threshold['min_score'],
                    'max_score' => $threshold['max_score'],
                    'advice_text' => $threshold['advice_text'],
                ],
                $threshold
            );
        }
    }
}
