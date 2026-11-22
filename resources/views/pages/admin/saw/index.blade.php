@extends('layouts.admin')
@section('title', 'Pemeringkatan SAW')
@section('breadcrumb')
    <li class="breadcrumb-item active">Pemeringkatan SAW</li>
@endsection
@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 mb-4 p-3">
        <div class="card-header bg-white border-0">
            <h5 class="fw-semibold fs-4 mb-1">Pemeringkatan SAW</h5>
            <p class="text-muted mb-0">Peringkat dihitung dari bobot AHP dan skor setiap alternatif.</p>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    @foreach ($errors->all() as $message)<div>{{ $message }}</div>@endforeach
                </div>
            @endif

            <p class="mb-3">Alternatif tersedia: <strong>{{ $alternativeCount }}</strong>.</p>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <a href="{{ route('alternatives.index') }}" class="btn btn-outline-secondary">Kelola Alternatif</a>
                <a href="{{ route('ahp-comparisons.index') }}" class="btn btn-outline-secondary">Kelola Perbandingan AHP</a>
                <form method="POST" action="{{ route('saw.calculate') }}" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-app-secondary">Hitung Peringkat SAW</button>
                </form>
            </div>
        </div>
    </div>

    @if ($saw !== null)
        @include('pages.admin.saw.results')
    @endif
</div>
@endsection
