@extends('layouts.admin')

@section('title', 'Ubah Alternatif')

@section('breadcrumb')
	<li class="breadcrumb-item"><a href="{{ route('alternatives.index') }}" class="text-decoration-none">Data Alternatif</a></li>
	<li class="breadcrumb-item active" aria-current="page">Ubah Alternatif</li>
@endsection

@section('content')
	<div class="container-fluid">
		<div class="card shadow-sm border-0 mb-4 p-3">
			<div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
				<h5 class="mb-0 fw-semibold fs-4">Ubah Alternatif</h5>
				<a href="{{ route('alternatives.index') }}" class="btn btn-secondary">
					<i class="bi bi-arrow-left"></i> Batal
				</a>
			</div>
			<div class="card-body">
				<form action="{{ route('alternatives.update', $alternative) }}" method="POST">
					@csrf
					@method('PUT')

                    @error('register')
                        <div class="alert alert-danger" role="alert">{{ $message }}</div>
                    @enderror


                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Alternatif <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                            maxlength="150" required placeholder="Masukkan nama alternatif" value="{{ old('name', $alternative->name) }}">
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi <span class="text-muted">(Opsional)</span></label>
                        <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                            placeholder="Masukkan deskripsi alternatif">{{ old('description', $alternative->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <h6 class="fw-semibold">Skor Kriteria</h6>
                    <p class="text-muted">Isi skor 1-100 untuk setiap kriteria, maksimal 4 angka desimal.</p>
                    @error('scores')
                        <div class="alert alert-danger" role="alert">{{ $message }}</div>
                    @enderror
                    @forelse ($criteria as $criterion)
                        <div class="mb-3">
                            <label for="score-{{ $criterion->id }}" class="form-label">
                                {{ $criterion->name }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="score-{{ $criterion->id }}" name="scores[{{ $criterion->id }}]"
                                class="form-control @error('scores.'.$criterion->id) is-invalid @enderror"
                                min="1" max="100" step="0.0001" required placeholder="Masukkan skor 1-100"
                                value="{{ old('scores.'.$criterion->id, $scores[$criterion->id] ?? '') }}"
                                aria-describedby="score-help-{{ $criterion->id }}">
                            <div id="score-help-{{ $criterion->id }}" class="form-text">
                                {{ $criterion->attribute === 'benefit' ? 'Benefit: semakin tinggi semakin baik.' : 'Cost: semakin rendah semakin baik.' }}
                                @if ($criterion->unit) Satuan: {{ $criterion->unit }}. @endif
                                {{ $criterion->description }}
                            </div>
                            @error('scores.'.$criterion->id)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @empty
                        <div class="alert alert-info">
                            Belum ada kriteria. Alternatif dapat disimpan dan skornya dilengkapi setelah
                            <a href="{{ route('criteria.create') }}">menambahkan kriteria</a>.
                        </div>
                    @endforelse

					{{-- SUBMIT BUTTON --}}
					<div class="d-flex justify-content-end mt-4">
						<button type="submit" class="btn btn-app-secondary" id="submitAlternativeBtn">
							<span class="button-content">
								Simpan
							</span>
							<span class="spinner-content d-none">
								<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
								Sedang Menyimpan...
							</span>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

	@push('scripts')
		<script>
			const alternativeForm = document.querySelector('form[action="{{ route('alternatives.update', $alternative) }}"]');
			const submitAlternativeBtn = document.getElementById('submitAlternativeBtn');
			if (alternativeForm && submitAlternativeBtn) {
				alternativeForm.addEventListener('submit', function() {
					submitAlternativeBtn.disabled = true;
					submitAlternativeBtn.querySelector('.button-content').classList.add('d-none');
					submitAlternativeBtn.querySelector('.spinner-content').classList.remove('d-none');
				});
			}
		</script>
	@endpush
