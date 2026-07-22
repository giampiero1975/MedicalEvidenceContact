<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Seeder;

class BusinessTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Cooperativa',
            'RSA',
            'Casa di comunità',
            'Clinica privata',
            'Farmacia',
        ];

        foreach ($types as $index => $name) {
            BusinessType::updateOrCreate(
                ['name' => $name],
                ['is_active' => true, 'sort_order' => ($index + 1) * 10]
            );
        }
    }
}
