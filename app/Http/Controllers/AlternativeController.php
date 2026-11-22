<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\Criterion;
use Illuminate\Http\Request;
use App\Http\Requests\AlternativeRequest;
use App\Services\AlternativeService;

class AlternativeController extends Controller
{
    public function __construct(private AlternativeService $alternativeService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $alternatives = $this->alternativeService->getAll($search);

        $currentPage = $alternatives->currentPage();
        $lastPage = $alternatives->lastPage();
        $perPage = $alternatives->perPage();
        $total = $alternatives->total();

        return view('pages.admin.alternative.index', compact(
            'alternatives',
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

        $criteria = Criterion::orderBy('id')->get();

        return view('pages.admin.alternative.create', compact('criteria'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AlternativeRequest $request)
    {
        try {
            $this->alternativeService->create($request->validated());
            return redirect()->route('alternatives.index')->with('success', 'Data Alternatif berhasil ditambahkan.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->withInput()->withErrors(['register' => 'Terjadi kesalahan saat menambah alternatif. Silakan coba lagi.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Alternative $alternative)
    {

        $criteria = Criterion::orderBy('id')->get();
        $scores = $alternative->scores()->pluck('value', 'criterion_id');

        return view('pages.admin.alternative.show', compact('alternative', 'criteria', 'scores'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Alternative $alternative)
    {

        $criteria = Criterion::orderBy('id')->get();
        $scores = $alternative->scores()->pluck('value', 'criterion_id');

        return view('pages.admin.alternative.edit', compact('alternative', 'criteria', 'scores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AlternativeRequest $request, Alternative $alternative)
    {
        try {
            $this->alternativeService->update($alternative, $request->validated());
            return redirect()->route('alternatives.index')->with('success', 'Data Alternatif berhasil diperbarui.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->withInput()->withErrors(['register' => 'Terjadi kesalahan saat memperbarui alternatif. Silakan coba lagi.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Alternative $alternative)
    {
        try {
            $this->alternativeService->delete($alternative);
            return redirect()->route('alternatives.index')->with('success', 'Data Alternatif berhasil dihapus.');

        } catch (\Exception $e) {
            report($e);
            return redirect()->route('alternatives.index')->withErrors(['delete' => 'Terjadi kesalahan saat menghapus alternatif. Silakan coba lagi.']);
        }
    }
}
