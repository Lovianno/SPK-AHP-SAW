@extends('layouts.admin')
@section('title', 'Pembobotan AHP')
@section('breadcrumb')
    <li class="breadcrumb-item active">Pembobotan AHP</li>
@endsection
@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 mb-4 p-3">
        <div class="card-header bg-white border-0">
            <h5 class="fw-semibold fs-4 mb-1">Pembobotan AHP</h5>
            <p class="text-muted mb-0">Perhitungan bobot berdasarkan perbandingan kriteria yang sudah disimpan.</p>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    @foreach ($errors->all() as $message)<div>{{ $message }}</div>@endforeach
                </div>
            @endif

            <p class="mb-3">Pasangan terisi: <strong>{{ $savedCount }} dari {{ max(0, $expectedPairs) }}</strong>.</p>
            @if (! $canCalculate)
                <div class="alert alert-warning" role="status">
                    @if ($criterionCount < 2 || $criterionCount > 10)
                        Perhitungan memerlukan 2 sampai 10 kriteria. Saat ini ada {{ $criterionCount }} kriteria.
                    @elseif ($missingPairs)
                        <strong>{{ count($missingPairs) }} pasangan belum diisi:</strong>
                        <ul class="mb-0 mt-2">@foreach ($missingPairs as $pair)<li>{{ $pair }}</li>@endforeach</ul>
                    @else
                        Jumlah perbandingan tidak sesuai. Periksa data pasangan sebelum menghitung.
                    @endif
                </div>
            @endif
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <a href="{{ route('ahp-comparisons.index') }}" class="btn btn-outline-secondary">Kelola Perbandingan</a>
                <form method="POST" action="{{ route('ahp.calculate') }}" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-app-secondary" @disabled(! $canCalculate)>Hitung Bobot AHP</button>
                </form>
            </div>
        </div>
    </div>

    @if ($ahp !== null)
        @include('pages.admin.ahp.results')
    @endif
</div>
@endsection
