<?php

namespace Database\Seeders;

use App\Models\AhpComparison;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AhpComparisonSeeder extends Seeder
{
    public function run(): void
    {
        // Nilai pasangan C1-C5 pada studi kasus pemilihan rumah kost.
        $names = ['Harga', 'Jarak', 'Fasilitas', 'Keamanan', 'Luas Kamar'];
        $pairs = [[0, 1, 2], [0, 2, 3], [0, 3, 2], [0, 4, 4], [1, 2, 2],
            [1, 3, 1], [1, 4, 3], [2, 3, 1 / 2], [2, 4, 2], [3, 4, 3]];

        DB::transaction(function () use ($names, $pairs) {
            $criteria = DB::table('criteria')->whereIn('name', $names)->get()->groupBy('name');
            foreach ($names as $name) {
                if (($criteria->get($name)?->count() ?? 0) !== 1) {
                    throw new \RuntimeException("Seeder AHP memerlukan tepat satu kriteria bernama {$name}.");
                }
            }
            foreach ($pairs as [$a, $b, $value]) {
                $a = $criteria[$names[$a]]->first()->id;
                $b = $criteria[$names[$b]]->first()->id;
                if ($a > $b) {
                    [$a, $b] = [$b, $a];
                    $value = 1 / $value;
                }
                // firstOrCreate tidak mengubah penilaian yang sudah disimpan pengguna.
                AhpComparison::firstOrCreate(
                    ['criterion_a_id' => $a, 'criterion_b_id' => $b],
                    ['value' => $value]
                );
            }
        });
    }
}
