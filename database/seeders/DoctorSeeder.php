<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Clínica Médica' => ['Dr. Juan Pérez', 'Dra. Laura Gómez'],
            'Pediatría' => ['Dra. Ana Morales', 'Dr. Tomás Ruiz'],
            'Odontología' => ['Dra. Marta Díaz', 'Dr. Pablo Sánchez'],
            'Cardiología' => ['Dr. Ricardo López'],
            'Dermatología' => ['Dra. Sofía Herrera'],
            'Ginecología' => ['Dra. Valentina Ortiz'],
            'Traumatología' => ['Dr. Nicolás Torres'],
        ];

        foreach ($data as $specName => $doctors) {
            $spec = Specialty::firstOrCreate(['name' => $specName]);
            foreach ($doctors as $name) {
                Doctor::firstOrCreate([
                    'name' => $name,
                    'specialty_id' => $spec->id,
                ], [
                    'active' => true,
                ]);
            }
        }
    }
}
