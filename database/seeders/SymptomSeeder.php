<?php

namespace Database\Seeders;

use App\Models\Symptom;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SymptomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $symptomps = [
            [
                'code' => 'G1',
                'label' => 'Kehilangan minat atau kesenangan dalam beraktivitas',
                'is_core' => 1,
                'is_sensitive' => 0,
                'type' => 'scale',
            ],
            [
                'code' => 'G2',
                'label' => 'Merasa sedih, putus asa, atau mudah menangis',
                'is_core' => 1,
                'is_sensitive' => 0,
                'type' => 'scale',
            ],
            [
                'code' => 'G3',
                'label' => 'Sulit tidur atau tidur terlalu lama',
                'is_core' => 0,
                'is_sensitive' => 0,
                'type' => 'scale',
            ],
            [
                'code' => 'G4',
                'label' => 'Perubahan nafsu makan atau berat badan',
                'is_core' => 0,
                'is_sensitive' => 0,
                'type' => 'scale',
            ],
            [
                'code' => 'G5',
                'label' => 'Merasa mudah lelah atau kekurangan energi',
                'is_core' => 0,
                'is_sensitive' => 0,
                'type' => 'scale',
            ],
            [
                'code' => 'G6',
                'label' => 'Merasa tidak berharga, bersalah, atau menyalahkan diri sendiri',
                'is_core' => 0,
                'is_sensitive' => 0,
                'type' => 'scale',
            ],
            [
                'code' => 'G7',
                'label' => 'Kesulitan berkonsentrasi atau membuat keputusan',
                'is_core' => 0,
                'is_sensitive' => 0,
                'type' => 'scale',
            ],
            [
                'code' => 'G8',
                'label' => 'Gerakan atau bicara terasa lebih lambat, atau justru lebih gelisah dari biasanya',
                'is_core' => 0,
                'is_sensitive' => 0,
                'type' => 'scale',
            ],
            [
                'code' => 'G9',
                'label' => 'Pernah terlintas pikiran untuk menyakiti diri sendiri',
                'is_core' => 0,
                'is_sensitive' => 1,
                'type' => 'boolean',
            ],
        ];

        foreach ($symptomps as $symptom) {
            Symptom::updateOrCreate(
                ['code' => $symptom['code']],
                $symptom
            );
        }
    }
}
