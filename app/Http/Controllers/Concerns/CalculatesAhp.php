<?php

namespace App\Http\Controllers\Concerns;

trait CalculatesAhp
{
    // Random Index (RI) untuk menghitung Consistency Ratio (CR) berdasarkan jumlah kriteria.
    private const RI =  [2 => 0, 3 => 0.58, 4 => 0.90, 5 => 1.12, 6 => 1.24, 7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49];

    // Hanya terima skala Saaty 1 sampai 9 atau nilai kebalikannya (1/2 sampai 1/9).
    private function saaty(mixed $input): ?float
    {
        if (! is_numeric($input) || ! is_finite((float) $input) || (float) $input <= 0) {
            return null;
        }
        for ($i = 1; $i <= 9; $i++) {
            foreach ([(float) $i, 1 / $i] as $value) {
                if (abs((float) $input - $value) <= 1e-10) {
                    // Pecahan di database dibulatkan; gunakan lagi nilai aslinya, misalnya 1/3.
                    return $value;
                }
            }
        }

        return null;
    }

    protected function calculateAhp(array $data): array
    {
        // Hasil metode ini berisi matriks, bobot, uji konsistensi, atau daftar kesalahan.
        $criteria = $data['criteria'];
        $n = count($criteria);
        $errors = [];
        if (! isset(self::RI[$n])) {
            return ['errors' => ['Perhitungan AHP mendukung 2 sampai 10 kriteria; jumlah saat ini: '.$n.'.']];
        }
        $ids = array_column($criteria, 'id');

        // 2. Susun matriks perbandingan berpasangan.
        // Ambil setiap pasangan sekali (ID A < ID B), lalu isi diagonal dengan 1
        // dan nilai pada arah sebaliknya dengan kebalikannya.
        $pairs = [];
        foreach ($data['ahp_comparisons'] as $pair) {
            $a = $pair['criterion_a_id'];
            $b = $pair['criterion_b_id'];
            $value = $this->saaty($pair['value']);
            if ($a >= $b || ! in_array($a, $ids, true) || ! in_array($b, $ids, true) || $value === null || isset($pairs["$a:$b"])) {
                $errors[] = "Perbandingan ID $a dan $b tidak valid. Perbaiki penilaian sebelum menghitung.";

                continue;
            }
            $pairs["$a:$b"] = $value;
        }
        $matrix = [];
        foreach ($criteria as $a) {
            foreach ($criteria as $b) {
                $i = $a['id'];
                $j = $b['id'];
                if ($i === $j) {
                    $matrix[$i][$j] = 1.0;
                } elseif ($i < $j) {
                    if (! isset($pairs["$i:$j"])) {
                        $errors[] = 'Lengkapi pasangan '.$a['name'].' — '.$b['name'].'.';
                    } else {
                        $matrix[$i][$j] = $pairs["$i:$j"];
                        $matrix[$j][$i] = 1 / $pairs["$i:$j"];
                    }
                }
            }
        }
        if ($errors) {
            return compact('errors');
        }

        $sums = $normal = $rowSums = $weights = $aw = $lambda = [];
        // 3. Jumlahkan setiap kolom matriks.
        foreach ($ids as $j) {
            $sums[$j] = array_sum(array_column($matrix, $j));
        }

        foreach ($ids as $i) {
            // 4. Normalisasi matriks: bagi setiap nilai dengan jumlah kolomnya.
            foreach ($ids as $j) {
                $normal[$i][$j] = $matrix[$i][$j] / $sums[$j];
            }

            // 5. Hitung bobot prioritas dari rata-rata nilai pada baris normalisasi.
            $rowSums[$i] = array_sum($normal[$i]);
            $weights[$i] = $rowSums[$i] / $n;
        }

        // 6. Hitung weighted sum vector (Aw): matriks perbandingan dikali bobot.
        foreach ($ids as $i) {
            $aw[$i] = 0;
            foreach ($ids as $j) {
                $aw[$i] += $matrix[$i][$j] * $weights[$j];
            }

            // 7. Hitung consistency vector: setiap nilai Aw dibagi bobot barisnya.
            $lambda[$i] = $aw[$i] / $weights[$i];
        }

        // 8. Hitung lambda maksimum (rata-rata consistency vector), CI, dan CR.
        $lambdaMax = array_sum($lambda) / $n;
        $ci = $n === 2 ? 0 : ($lambdaMax - $n) / ($n - 1);
        $ri = self::RI[$n];
        $cr = $n === 2 ? 0 : $ci / $ri;

        // 9. Terima bobot jika CR tidak lebih dari 0,1; jika lebih, tinjau pasangan.
        $consistent = $cr <= 0.1;

        return compact('errors', 'matrix', 'sums', 'normal', 'rowSums', 'weights', 'aw', 'lambda', 'lambdaMax', 'ci', 'ri', 'cr', 'consistent');
    }
}
