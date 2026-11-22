<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $criteria = [
            ['Harga', 'cost', 'Penilaian harga kost; skor lebih rendah lebih baik.'],
            ['Jarak', 'benefit', 'Penilaian jarak sesuai studi kasus; skor lebih tinggi lebih baik.'],
            ['Fasilitas', 'benefit', 'Penilaian fasilitas yang tersedia.'],
            ['Keamanan', 'benefit', 'Penilaian keamanan lingkungan kost.'],
            ['Luas Kamar', 'benefit', 'Penilaian luas kamar kost.'],
        ];

        $now = now();

        foreach ($criteria as [$name, $attribute, $description]) {
            $existing = DB::table('criteria')->where('name', $name)->first();
            DB::table('criteria')->updateOrInsert(['name' => $name], [
                'name' => $name,
                'attribute' => $attribute,
                'unit' => 'skor 1-5',
                'description' => $description,
                'created_at' => $existing->created_at ?? $now,
                'updated_at' => $now,
            ]);
        }

    }
}
