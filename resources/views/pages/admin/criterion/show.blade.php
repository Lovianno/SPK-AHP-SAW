@extends('layouts.admin')

@section('title', 'Detail Kriteria')

@section('breadcrumb')
	<li class="breadcrumb-item"><a href="{{ route('criteria.index') }}" class="text-decoration-none">Data Kriteria</a></li>
	<li class="breadcrumb-item active" aria-current="page">Detail Data Kriteria</li>
@endsection

@section('content')
	<div class="container-fluid">
		<div class="card shadow-sm border-0 mb-4 p-3">
			<div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
				<h5 class="mb-0 fw-semibold fs-4">Detail Data Kriteria</h5>
				<a href="{{ route('criteria.index') }}" class="btn btn-secondary">
					<i class="bi bi-arrow-left"></i> Kembali
				</a>
			</div>
			<div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Nama Kriteria</label>
                    <input type="text" class="form-control" value="{{ $criterion->name ?? '-' }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Atribut</label>
                    <input type="text" class="form-control" value="{{ ucfirst($criterion->attribute) }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Satuan</label>
                    <input type="text" class="form-control" value="{{ $criterion->unit ?? '-' }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" rows="3" readonly>{{ $criterion->description ?? '-' }}</textarea>
                </div>
				<hr>
				<div class="mb-3">
					<label class="form-label">Dibuat Pada</label>
					<input type="text" class="form-control" value="{{ $criterion->created_at->translatedFormat('d F Y, H:i') }}" readonly>
				</div>
				<div class="mb-3">
					<label class="form-label">Diperbarui Pada</label>
					<input type="text" class="form-control" value="{{ $criterion->updated_at->translatedFormat('d F Y, H:i') }}" readonly>
				</div>
			</div>
		</div>
	</div>
@endsection

