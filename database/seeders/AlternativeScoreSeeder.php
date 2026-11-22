<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlternativeScoreSeeder extends Seeder
{
    public function run(): void
    {
        // Urutan skor C1-C5: Harga, Jarak, Fasilitas, Keamanan, Luas Kamar.
        // Harga adalah cost; empat kriteria lainnya benefit sesuai studi kasus.
        // Sumber: STUDI KASUS SPK NEW.pdf, Tabel Alternatif (skala 1-5).
        $scores = [
            ['Kamila Kost', [4, 1, 3, 3, 3]],
            ['Tripelsi Kost', [5, 5, 5, 3, 3]],
            ['Kost Soedarmo', [1, 5, 2, 3, 4]],
            ['Kost Bu Kusnul', [4, 1, 3, 3, 2]],
            ['Billo Kost', [4, 4, 5, 3, 4]],
            ['Wisma Pusparini', [1, 5, 1, 5, 2]],
            ['Kost Zaze', [2, 2, 1, 2, 2]],
            ['Degrasia Home', [5, 2, 5, 5, 4]],
            ['Lariz Kost', [2, 5, 2, 3, 2]],
            ['Cozy Kost', [4, 4, 4, 4, 3]],
            ['Kost Sadewa Living', [5, 4, 5, 5, 5]],
            ['Kos Medina', [2, 5, 2, 3, 3]],
            ['Nine Coliving', [5, 5, 5, 5, 2]],
            ['Kos Griya Ameera', [3, 4, 3, 3, 3]],
            ['Hi Smart Boarding House', [4, 3, 5, 4, 3]],
            ["D'Kost Medokan", [5, 1, 3, 3, 3]],
            ['The Wood Kost', [4, 5, 4, 4, 3]],
            ['The Alfaeyza Kost', [5, 5, 4, 5, 4]],
            ['Kost Brody', [5, 2, 5, 5, 4]],
            ['Kost Campur Sirimiri', [1, 3, 1, 1, 2]],
            ['Uni House', [5, 4, 5, 4, 4]],
            ['Kost Julian', [3, 1, 3, 2, 3]],
            ['Kost Mandiri', [2, 2, 3, 2, 2]],
            ['Kost We Goona House', [4, 4, 4, 3, 3]],
            ['Kost Bu Rizka', [5, 3, 5, 5, 5]],
            ['Ratna Kost', [3, 5, 3, 4, 3]],
            ['Kost Melfin', [3, 3, 4, 3, 3]],
            ['Kost Smart', [4, 3, 5, 4, 3]],
            ['Kost Morr Premium', [5, 4, 5, 5, 5]],
            ['Kost Mamar', [2, 1, 2, 2, 3]],
        ];

        $criterionNames = ['Harga', 'Jarak', 'Fasilitas', 'Keamanan', 'Luas Kamar'];
        // Ambil ID berdasarkan nama, tanpa mengasumsikan ID dimulai dari 1.
        $criterionIds = DB::table('criteria')
            ->whereIn('name', $criterionNames)
            ->pluck('id', 'name');
        $alternativeIds = DB::table('alternatives')
            ->whereIn('name', array_column($scores, 0))
            ->pluck('id', 'name');

        if ($criterionIds->count() !== count($criterionNames)
            || $alternativeIds->count() !== count($scores)) {
            throw new \RuntimeException('Jalankan CriteriaSeeder dan AlternativeSeeder sebelum AlternativeScoreSeeder.');
        }

        $now = now();
        $rows = [];

        foreach ($scores as [$name, $values]) {
            foreach ($values as $index => $value) {
                $rows[] = [
                    'alternative_id' => $alternativeIds[$name],
                    'criterion_id' => $criterionIds[$criterionNames[$index]],
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('alternative_scores')->upsert(
            $rows,
            ['alternative_id', 'criterion_id'],
            ['value', 'updated_at']
        );
    }
}
