<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Clínica Médica',
            'Pediatría',
            'Odontología',
            'Cardiología',
            'Dermatología',
            'Ginecología',
            'Traumatología',
        ];

        foreach ($names as $name) {
            Specialty::firstOrCreate(['name' => $name]);
        }
    }
}
