@extends('layouts.admin')

@section('title', 'Detail Alternatif')

@section('breadcrumb')
	<li class="breadcrumb-item"><a href="{{ route('alternatives.index') }}" class="text-decoration-none">Data Alternatif</a></li>
	<li class="breadcrumb-item active" aria-current="page">Detail Data Alternatif</li>
@endsection

@section('content')
	<div class="container-fluid">
		<div class="card shadow-sm border-0 mb-4 p-3">
			<div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
				<h5 class="mb-0 fw-semibold fs-4">Detail Data Alternatif</h5>
				<a href="{{ route('alternatives.index') }}" class="btn btn-secondary">
					<i class="bi bi-arrow-left"></i> Kembali
				</a>
			</div>
			<div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Nama Alternatif</label>
                    <input type="text" class="form-control" value="{{ $alternative->name ?? '-' }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" rows="3" readonly>{{ $alternative->description ?? '-' }}</textarea>
                </div>
                <hr>
                <h6 class="fw-semibold">Skor Kriteria</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Kriteria</th><th>Atribut</th><th>Satuan</th><th>Skor</th></tr></thead>
                        <tbody>
                            @forelse ($criteria as $criterion)
                                <tr>
                                    <td>{{ $criterion->name }}</td>
                                    <td>{{ ucfirst($criterion->attribute) }}</td>
                                    <td>{{ $criterion->unit ?? '-' }}</td>
                                    <td>{{ $scores[$criterion->id] ?? 'Belum diisi' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Belum ada kriteria.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
				<hr>
				<div class="mb-3">
					<label class="form-label">Dibuat Pada</label>
					<input type="text" class="form-control" value="{{ $alternative->created_at->translatedFormat('d F Y, H:i') }}" readonly>
				</div>
				<div class="mb-3">
					<label class="form-label">Diperbarui Pada</label>
					<input type="text" class="form-control" value="{{ $alternative->updated_at->translatedFormat('d F Y, H:i') }}" readonly>
				</div>
			</div>
		</div>
	</div>
@endsection

