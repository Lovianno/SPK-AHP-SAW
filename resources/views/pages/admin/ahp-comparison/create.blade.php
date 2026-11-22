@extends('layouts.admin')
@section('title', 'Tambah Perbandingan')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('ahp-comparisons.index') }}">Perbandingan AHP</a></li>
    <li class="breadcrumb-item active">Tambah Perbandingan</li>
@endsection
@section('content')
<div class="container-fluid"><div class="card shadow-sm border-0 mb-4 p-3">
    <div class="card-header bg-white border-0"><h5 class="fw-semibold fs-4 mb-0">Tambah Perbandingan</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('ahp-comparisons.store') }}">
            @csrf
            @include('pages.admin.ahp-comparison.form')
        </form>
    </div>
</div></div>
@endsection
