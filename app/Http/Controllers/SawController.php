<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CalculatesAhp;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SawController extends Controller
{
    use CalculatesAhp;

    // Membuka halaman tidak menjalankan perhitungan; hanya menampilkan jumlah alternatif.
    public function index()
    {
        return response()->view('pages.admin.saw.index', [
            'alternativeCount' => DB::table('alternatives')->count(),
            'saw' => null,
        ])->header('Cache-Control', 'no-store');
    }

    public function calculate()
    {
        // 1. Siapkan kriteria, jenis benefit/cost, skor, dan bobot dari AHP.
        // Tahap ini baru berjalan setelah tombol Hitung Peringkat SAW ditekan.
        $data = $this->data();
        $ahp = $this->calculateAhp($data);
        $errors = $this->sawErrors($data, $ahp);
        if ($errors) {
            // SAW hanya dijalankan jika AHP konsisten dan semua skor alternatif lengkap.
            throw ValidationException::withMessages(['calculation' => $errors]);
        }

        $saw = $this->calculateSaw($data, $ahp);
        $alternativeCount = count($data['alternatives']);
        // Ubah daftar skor menjadi [ID alternatif][ID kriteria] untuk tabel nilai awal.
        $raw = [];
        foreach ($data['alternative_scores'] as $score) {
            $raw[$score['alternative_id']][$score['criterion_id']] = $score['value'];
        }

        return response()->view('pages.admin.saw.index', compact('data', 'ahp', 'saw', 'alternativeCount', 'raw'))
            ->header('Cache-Control', 'no-store');
    }

    private function data(): array
    {
        // Semua data perhitungan diambil ulang agar hasil memakai isi database saat tombol ditekan.
        $data = [];
        foreach (['criteria', 'alternatives', 'alternative_scores', 'ahp_comparisons'] as $table) {
            $data[$table] = DB::table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        }

        return $data;
    }

    private function sawErrors(array $data, array $ahp): array
    {
        // Mulai dari kesalahan AHP, lalu periksa konsistensi dan kelengkapan data SAW.
        $errors = $ahp['errors'];
        if (! $errors && ! $ahp['consistent']) {
            $errors[] = 'AHP belum konsisten (CR > 0,1). Tinjau perbandingan sebelum menghitung SAW.';
        }
        if (! $data['alternatives']) {
            $errors[] = 'Tambahkan minimal satu alternatif untuk pemeringkatan SAW.';
        }
        // Simpan sebagai daftar supaya skor ganda pada pasangan yang sama juga terdeteksi.
        $scores = [];
        foreach ($data['alternative_scores'] as $score) {
            $scores[$score['alternative_id']][$score['criterion_id']][] = $score['value'];
        }
        foreach ($data['criteria'] as $criterion) {
            if (! in_array($criterion['attribute'], ['benefit', 'cost'], true)) {
                $errors[] = 'Atribut kriteria '.$criterion['name'].' tidak valid.';
            }
            foreach ($data['alternatives'] as $alternative) {
                $values = $scores[$alternative['id']][$criterion['id']] ?? [];
                $value = $values[0] ?? null;
                if (count($values) !== 1 || ! is_numeric($value) || ! is_finite((float) $value) || $value < 1 || $value > 100) {
                    $errors[] = $alternative['name'].' — '.$criterion['name'].': wajib satu skor numerik 1–100; lengkapi atau perbaiki skor.';
                }
            }
        }

        return $errors;
    }

    private function calculateSaw(array $data, array $ahp): array
    {
        $values = $min = $max = $normal = $contributions = $totals = [];

        // 2. Susun matriks keputusan dari skor tersimpan.
        // Akses nilainya: $values[ID alternatif][ID kriteria].
        foreach ($data['alternative_scores'] as $score) {
            $values[$score['alternative_id']][$score['criterion_id']] = (float) $score['value'];
        }

        // 3. Cari nilai minimum dan maksimum untuk setiap kriteria.
        foreach ($data['criteria'] as $criterion) {
            $criterionId = $criterion['id'];
            $scoresForCriterion = array_map(fn ($alternative) => $values[$alternative['id']][$criterionId], $data['alternatives']);
            $min[$criterionId] = min($scoresForCriterion);
            $max[$criterionId] = max($scoresForCriterion);
        }

        // 4. Normalisasi: benefit = nilai / maksimum, cost = minimum / nilai.
        // 5. Simpan setiap hasil ke matriks $normal[ID alternatif][ID kriteria].
        foreach ($data['criteria'] as $criterion) {
            $criterionId = $criterion['id'];
            foreach ($data['alternatives'] as $alternative) {
                $alternativeId = $alternative['id'];
                $normal[$alternativeId][$criterionId] = $criterion['attribute'] === 'benefit'
                    ? $values[$alternativeId][$criterionId] / $max[$criterionId]
                    : $min[$criterionId] / $values[$alternativeId][$criterionId];
            }
        }

        // 6. Kalikan nilai normalisasi dengan bobot, lalu jumlahkan per alternatif.
        foreach ($data['criteria'] as $criterion) {
            $criterionId = $criterion['id'];
            foreach ($data['alternatives'] as $alternative) {
                $alternativeId = $alternative['id'];
                $contributions[$alternativeId][$criterionId] = $ahp['weights'][$criterionId] * $normal[$alternativeId][$criterionId];
            }
        }
        foreach ($data['alternatives'] as $alternative) {
            $totals[] = ['id' => $alternative['id'], 'name' => $alternative['name'], 'value' => array_sum($contributions[$alternative['id']])];
        }

        // 7. Urutkan nilai akhir dari terbesar ke terkecil dan beri peringkat.
        usort($totals, fn ($a, $b) => $b['value'] <=> $a['value']);
        $ranking = [];
        // Nilai yang berselisih paling banyak 1e-9 dianggap seri; peringkat berikutnya
        // melompati jumlah anggota seri, misalnya 1, 1, 3.
        for ($i = 0; $i < count($totals);) {
            $first = $totals[$i]['value'];
            $rank = $i + 1;
            $group = [];
            do {
                $group[] = [...$totals[$i], 'rank' => $rank];
                $i++;
            } while ($i < count($totals) && abs($totals[$i]['value'] - $first) <= 1e-9);
            // Nama lalu ID hanya menentukan urutan tampilan alternatif yang seri.
            usort($group, fn ($a, $b) => strnatcasecmp($a['name'], $b['name']) ?: ($a['id'] <=> $b['id']));
            array_push($ranking, ...$group);
        }
        $best = array_values(array_filter($ranking, fn ($row) => $row['rank'] === 1));

        return compact('values', 'min', 'max', 'normal', 'contributions', 'ranking', 'best');
    }
}
