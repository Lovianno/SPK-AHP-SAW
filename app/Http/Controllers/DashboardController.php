<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Dashboard hanya menampilkan jumlah data; perhitungan AHP dan SAW tetap di halaman masing-masing.
        $criteriaCount = DB::table('criteria')->count();
        $alternativesCount = DB::table('alternatives')->count();
        $comparisonsCount = DB::table('ahp_comparisons')->count();
        $scoresCount = DB::table('alternative_scores')->count();

        $expectedComparisons = intdiv($criteriaCount * ($criteriaCount - 1), 2);
        $expectedScores = $criteriaCount * $alternativesCount;

        return view('pages.admin.dashboard', compact(
            'criteriaCount',
            'alternativesCount',
            'comparisonsCount',
            'scoresCount',
            'expectedComparisons',
            'expectedScores'
        ));
    }
}
