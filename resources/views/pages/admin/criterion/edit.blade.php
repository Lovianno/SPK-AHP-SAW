@extends('layouts.admin')

@section('title', 'Ubah Kriteria')

@section('breadcrumb')
	<li class="breadcrumb-item"><a href="{{ route('criteria.index') }}" class="text-decoration-none">Data Kriteria</a></li>
	<li class="breadcrumb-item active" aria-current="page">Ubah Kriteria</li>
@endsection

@section('content')
	<div class="container-fluid">
		<div class="card shadow-sm border-0 mb-4 p-3">
			<div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
				<h5 class="mb-0 fw-semibold fs-4">Ubah Kriteria</h5>
				<a href="{{ route('criteria.index') }}" class="btn btn-secondary">
					<i class="bi bi-arrow-left"></i> Batal
				</a>
			</div>
			<div class="card-body">
				<form action="{{ route('criteria.update', $criterion) }}" method="POST">
					@csrf
					@method('PUT')

                    @error('register')
                        <div class="alert alert-danger" role="alert">{{ $message }}</div>
                    @enderror


                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Kriteria <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                            maxlength="100" required placeholder="Masukkan nama kriteria" value="{{ old('name', $criterion->name) }}">
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="attribute" class="form-label">Atribut <span class="text-danger">*</span></label>
                        <select id="attribute" name="attribute" class="form-select @error('attribute') is-invalid @enderror" required>
                            <option value="">Pilih atribut</option>
                            <option value="benefit" @selected(old('attribute', $criterion->attribute) === 'benefit')>Benefit (semakin tinggi semakin baik)</option>
                            <option value="cost" @selected(old('attribute', $criterion->attribute) === 'cost')>Cost (semakin rendah semakin baik)</option>
                        </select>
                        @error('attribute')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="unit" class="form-label">Satuan <span class="text-muted">(Opsional)</span></label>
                        <input type="text" id="unit" name="unit" maxlength="50" class="form-control @error('unit') is-invalid @enderror"
                            placeholder="Contoh: skor 1-100" value="{{ old('unit', $criterion->unit) }}">
                        @error('unit')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi <span class="text-muted">(Opsional)</span></label>
                        <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                            placeholder="Masukkan deskripsi kriteria">{{ old('description', $criterion->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

					{{-- SUBMIT BUTTON --}}
					<div class="d-flex justify-content-end mt-4">
						<button type="submit" class="btn btn-app-secondary" id="submitCriterionBtn">
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
			const criterionForm = document.querySelector('form[action="{{ route('criteria.update', $criterion) }}"]');
			const submitCriterionBtn = document.getElementById('submitCriterionBtn');
			if (criterionForm && submitCriterionBtn) {
				criterionForm.addEventListener('submit', function() {
					submitCriterionBtn.disabled = true;
					submitCriterionBtn.querySelector('.button-content').classList.add('d-none');
					submitCriterionBtn.querySelector('.spinner-content').classList.remove('d-none');
				});
			}
		</script>
	@endpush
