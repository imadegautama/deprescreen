<?php

namespace Database\Seeders;

use App\Models\Symptom;
use Illuminate\Database\Seeder;

class SymptomsSeeder extends Seeder
{
    /**
     * Seed G1-G9 symptoms untuk depression screening
     * Sesuai konfigurasi expert_system.php
     */
    public function run(): void
    {
        $config = config('expert_system.symptoms');

        foreach ($config as $code => $symptomData) {
            Symptom::updateOrCreate(
                ['code' => $code],  // Match by code field (unique)
                [
                    'code' => $symptomData['code'],
                    'label' => $symptomData['label'],
                    'description' => $symptomData['description'],
                    'is_core' => $symptomData['is_core'],
                    'is_sensitive' => $symptomData['is_sensitive'],
                    'type' => $symptomData['type'],
                    'order' => $symptomData['order'],
                ]
            );
        }

        $this->command->info('Successfully seeded G1-G9 symptoms for depression screening.');
    }
}
