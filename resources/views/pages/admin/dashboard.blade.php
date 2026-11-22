@extends('layouts.admin')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h4 class="fw-semibold text-app-primary mb-1">Selamat datang, {{ auth()->user()->name }}</h4>
            <p class="text-muted mb-0">Kelola data rumah kost, hitung bobot AHP, lalu tentukan peringkat kost dengan SAW.</p>
        </div>
    </div>

    <h5 class="fw-semibold mb-3">Ringkasan data</h5>
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Kriteria</div>
                    <div class="fs-4 fw-semibold text-app-primary">{{ $criteriaCount }}</div>
                    <a href="{{ route('criteria.index') }}" class="small">Lihat kriteria</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Alternatif</div>
                    <div class="fs-4 fw-semibold text-app-primary">{{ $alternativesCount }}</div>
                    <a href="{{ route('alternatives.index') }}" class="small">Lihat alternatif</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Perbandingan AHP</div>
                    <div class="fs-4 fw-semibold text-app-primary">{{ $comparisonsCount }} <span class="fs-6 fw-normal text-muted">/ {{ $expectedComparisons }}</span></div>
                    <a href="{{ route('ahp-comparisons.index') }}" class="small">Lihat perbandingan</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Skor alternatif</div>
                    <div class="fs-4 fw-semibold text-app-primary">{{ $scoresCount }} <span class="fs-6 fw-normal text-muted">/ {{ $expectedScores }}</span></div>
                    <a href="{{ route('alternatives.index') }}" class="small">Lihat skor</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h5 class="fw-semibold mb-3">Alur perhitungan</h5>
            <ol class="mb-0 ps-3">
                <li class="mb-2"><a href="{{ route('criteria.index') }}">Isi kriteria</a> yang akan dinilai.</li>
                <li class="mb-2"><a href="{{ route('alternatives.index') }}">Isi alternatif dan skornya</a> untuk setiap kriteria.</li>
                <li class="mb-2"><a href="{{ route('ahp-comparisons.index') }}">Isi perbandingan AHP</a> untuk semua pasangan kriteria.</li>
                <li class="mb-2"><a href="{{ route('ahp.index') }}">Hitung bobot AHP</a> dan periksa konsistensinya.</li>
                <li><a href="{{ route('saw.index') }}">Hitung peringkat SAW</a> untuk melihat hasil akhir.</li>
            </ol>
        </div>
    </div>
</div>
@endsection
