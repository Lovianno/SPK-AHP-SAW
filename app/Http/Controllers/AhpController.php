<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CalculatesAhp;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AhpController extends Controller
{
    use CalculatesAhp;

    // Halaman awal hanya memeriksa kelengkapan pasangan; hasil AHP belum dihitung.
    public function index()
    {
        return $this->page($this->data());
    }

    // Dipanggil saat tombol "Hitung Bobot AHP" ditekan.
    public function calculate()
    {
        $data = $this->data();
        // Urutan langkah 2-9 ada di calculateAhp() pada Concerns/CalculatesAhp.php.
        $ahp = $this->calculateAhp($data);
        if ($ahp['errors']) {
            // Data pasangan yang belum lengkap atau tidak valid dikirim sebagai pesan formulir.
            throw ValidationException::withMessages(['calculation' => $ahp['errors']]);
        }

        return $this->page($data, $ahp);
    }

    // Menyiapkan jumlah pasangan dan daftar pasangan yang belum diisi untuk tampilan.
    private function page(array $data, ?array $ahp = null)
    {
        $criteria = $data['criteria'];
        $savedPairs = [];
        foreach ($data['ahp_comparisons'] as $comparison) {
            // Kunci "ID A:ID B" memudahkan pengecekan apakah suatu pasangan sudah tersimpan.
            $savedPairs[$comparison['criterion_a_id'].':'.$comparison['criterion_b_id']] = true;
        }

        $missingPairs = [];
        foreach ($criteria as $index => $first) {
            foreach (array_slice($criteria, $index + 1) as $second) {
                if (! isset($savedPairs[$first['id'].':'.$second['id']])) {
                    $missingPairs[] = $first['name'].' dan '.$second['name'];
                }
            }
        }

        $criterionCount = count($criteria);
        // Untuk n kriteria, jumlah pasangan yang diperlukan adalah n x (n - 1) / 2.
        $expectedPairs = $criterionCount * ($criterionCount - 1) / 2;
        $savedCount = count($data['ahp_comparisons']);
        $canCalculate = $criterionCount >= 2 && $criterionCount <= 10 && $savedCount === $expectedPairs && $missingPairs === [];

        return response()->view('pages.admin.ahp.index', compact(
            'data', 'ahp', 'missingPairs', 'criterionCount', 'expectedPairs', 'savedCount', 'canCalculate'
        ))
            ->header('Cache-Control', 'no-store');
    }

    private function data(): array
    {
        // Urutan ID dipakai secara konsisten saat menyusun matriks AHP.
        return [
            'criteria' => DB::table('criteria')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all(),
            'ahp_comparisons' => DB::table('ahp_comparisons')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all(),
        ];
    }
}
