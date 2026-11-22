@extends('layouts.admin')
@section('title', 'Perbandingan AHP')
@section('breadcrumb')<li class="breadcrumb-item active">Perbandingan AHP</li>@endsection
@section('content')
<div class="container-fluid"><div class="card shadow-sm border-0 mb-4 p-3">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="fw-semibold fs-4 mb-0">Perbandingan AHP</h5>
        <a href="{{ route('ahp-comparisons.create') }}" class="btn btn-app-secondary" @if($criteria->count() < 2) aria-disabled="true" @endif>Tambah Perbandingan</a>
    </div>
    <div class="card-body">
        <p>{{ $comparisons->count() }} dari {{ max(0, $expected) }} pasangan terisi.</p>
        @if($criteria->count() < 2)<div class="alert alert-warning">Tambahkan minimal dua kriteria terlebih dahulu.</div>@endif
        <div class="table-responsive"><table class="table table-hover">
            <thead><tr><th>#</th><th>Kriteria A</th><th>Kriteria B</th><th>Lebih penting</th><th>Intensitas</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($comparisons as $comparison)
                    @php
                        $ratio = (float) $comparison->value;
                        $intensity = $ratio > 0 ? (int) round(max($ratio, 1 / $ratio)) : null;
                        $winner = $intensity === null ? 'Nilai tidak valid' : ($intensity === 1 ? 'Sama penting' : ($ratio > 1 ? $comparison->criterionA->name : $comparison->criterionB->name));
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $comparison->criterionA->name }}</td>
                        <td>{{ $comparison->criterionB->name }}</td>
                        <td>{{ $winner }}</td>
                        <td>{{ $intensity ?? '-' }}</td>
                        <td class="d-flex gap-2">
                            <a href="{{ route('ahp-comparisons.edit', $comparison) }}" class="btn btn-sm btn-warning">Ubah</a>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delete-{{ $comparison->id }}">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada perbandingan.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <a href="{{ route('ahp.index') }}" class="btn btn-primary mt-3">Lihat Pembobotan AHP</a>
    </div>
</div></div>
@foreach($comparisons as $comparison)
<div class="modal fade" id="delete-{{ $comparison->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Hapus Perbandingan</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">Hapus perbandingan {{ $comparison->criterionA->name }} dan {{ $comparison->criterionB->name }}? Pembobotan AHP dan SAW menunggu pasangan ini diisi kembali.</div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <form method="POST" action="{{ route('ahp-comparisons.destroy', $comparison) }}">@csrf @method('DELETE')<button type="submit" class="btn btn-danger">Hapus</button></form>
        </div>
    </div></div>
</div>
@endforeach
@endsection
