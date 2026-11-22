<?php

namespace App\Http\Controllers;

use App\Http\Requests\AhpComparisonRequest;
use App\Models\AhpComparison;
use App\Models\Criterion;
use App\Services\AhpComparisonService;
use Illuminate\Http\RedirectResponse;

class AhpComparisonController extends Controller
{
    public function __construct(private AhpComparisonService $comparisonService) {}

    public function index()
    {
        $comparisons = AhpComparison::with(['criterionA', 'criterionB'])
            ->orderBy('criterion_a_id')->orderBy('criterion_b_id')->get();
        $criteria = Criterion::orderBy('id')->get();
        $expected = $criteria->count() * ($criteria->count() - 1) / 2;

        return view('pages.admin.ahp-comparison.index', compact('comparisons', 'criteria', 'expected'));
    }

    public function create()
    {
        $criteria = Criterion::orderBy('id')->get();

        $comparison = null;
        $direction = '';
        $intensity = '';

        return view('pages.admin.ahp-comparison.create', compact('criteria', 'comparison', 'direction', 'intensity'));
    }

    public function store(AhpComparisonRequest $request): RedirectResponse
    {
        $this->comparisonService->create($request->validated());

        return redirect()->route('ahp-comparisons.index')->with('success', 'Perbandingan berhasil ditambahkan.');
    }

    public function edit(AhpComparison $ahpComparison)
    {
        $ahpComparison->load(['criterionA', 'criterionB']);

        $comparison = $ahpComparison;
        $ratio = (float) $comparison->value;
        $intensity = $ratio > 0 ? (int) round(max($ratio, 1 / $ratio)) : '';
        $direction = $intensity === '' ? '' : ($intensity === 1 ? 'equal' : ($ratio > 1 ? 'a' : 'b'));

        return view('pages.admin.ahp-comparison.edit', compact('comparison', 'direction', 'intensity'));
    }

    public function update(AhpComparisonRequest $request, AhpComparison $ahpComparison): RedirectResponse
    {
        $this->comparisonService->update($ahpComparison, $request->validated());

        return redirect()->route('ahp-comparisons.index')->with('success', 'Perbandingan berhasil diperbarui.');
    }

    public function destroy(AhpComparison $ahpComparison): RedirectResponse
    {
        $this->comparisonService->delete($ahpComparison);

        return redirect()->route('ahp-comparisons.index')->with('success', 'Perbandingan berhasil dihapus.');
    }
}
