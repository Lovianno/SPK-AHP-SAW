<?php

namespace App\Http\Controllers;

use App\Models\Criterion;
use Illuminate\Http\Request;
use App\Http\Requests\CriterionRequest;
use App\Services\CriterionService;

class CriterionController extends Controller
{
    public function __construct(private CriterionService $criterionService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $criteria = $this->criterionService->getAll($search);

        $currentPage = $criteria->currentPage();
        $lastPage = $criteria->lastPage();
        $perPage = $criteria->perPage();
        $total = $criteria->total();

        return view('pages.admin.criterion.index', compact(
            'criteria',
            'search',
            'currentPage',
            'lastPage',
            'perPage',
            'total'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return view('pages.admin.criterion.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CriterionRequest $request)
    {
        try {
            $this->criterionService->create($request->validated());
            return redirect()->route('criteria.index')->with('success', 'Data Kriteria berhasil ditambahkan.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->withInput()->withErrors(['register' => 'Terjadi kesalahan saat menambah kriteria. Silakan coba lagi.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Criterion $criterion)
    {

        return view('pages.admin.criterion.show', compact('criterion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Criterion $criterion)
    {

        return view('pages.admin.criterion.edit', compact('criterion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CriterionRequest $request, Criterion $criterion)
    {
        try {
            $this->criterionService->update($criterion, $request->validated());
            return redirect()->route('criteria.index')->with('success', 'Data Kriteria berhasil diperbarui.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->withInput()->withErrors(['register' => 'Terjadi kesalahan saat memperbarui kriteria. Silakan coba lagi.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Criterion $criterion)
    {
        try {
            $this->criterionService->delete($criterion);
            return redirect()->route('criteria.index')->with('success', 'Data Kriteria berhasil dihapus.');
        } catch (\DomainException $e) {
            return redirect()->route('criteria.index')->withErrors(['delete' => $e->getMessage()]);
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('criteria.index')->withErrors(['delete' => 'Terjadi kesalahan saat menghapus kriteria. Silakan coba lagi.']);
        }
    }
}
