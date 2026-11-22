<div class="mb-3">
    <label class="form-label" for="criterion_a_id">Kriteria A <span class="text-danger">*</span></label>
    @if($comparison)
        <input class="form-control" value="{{ $comparison->criterionA->name }}" readonly>
    @else
        <select id="criterion_a_id" name="criterion_a_id" class="form-select @error('criterion_a_id') is-invalid @enderror" required>
            <option value="">Pilih kriteria A</option>
            @foreach($criteria as $criterion)<option value="{{ $criterion->id }}" @selected(old('criterion_a_id') == $criterion->id)>{{ $criterion->name }}</option>@endforeach
        </select>
        @error('criterion_a_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    @endif
</div>
<div class="mb-3">
    <label class="form-label" for="criterion_b_id">Kriteria B <span class="text-danger">*</span></label>
    @if($comparison)
        <input class="form-control" value="{{ $comparison->criterionB->name }}" readonly>
    @else
        <select id="criterion_b_id" name="criterion_b_id" class="form-select @error('criterion_b_id') is-invalid @enderror" required>
            <option value="">Pilih kriteria B</option>
            @foreach($criteria as $criterion)<option value="{{ $criterion->id }}" @selected(old('criterion_b_id') == $criterion->id)>{{ $criterion->name }}</option>@endforeach
        </select>
        @error('criterion_b_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    @endif
</div>
<div class="mb-3">
    <label class="form-label" for="more_important">Lebih penting <span class="text-danger">*</span></label>
    <select id="more_important" name="more_important" class="form-select @error('more_important') is-invalid @enderror" required>
        <option value="">Pilih</option>
        <option value="equal" @selected(old('more_important', $direction) === 'equal')>Sama penting</option>
        <option value="a" @selected(old('more_important', $direction) === 'a')>Kriteria A</option>
        <option value="b" @selected(old('more_important', $direction) === 'b')>Kriteria B</option>
    </select>
    @error('more_important')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="intensity">Intensitas 1?9 <span class="text-danger">*</span></label>
    <select id="intensity" name="intensity" class="form-select @error('intensity') is-invalid @enderror" required>
        <option value="">Pilih intensitas</option>
        @for($i = 1; $i <= 9; $i++)<option value="{{ $i }}" @selected(old('intensity', $intensity) == $i)>{{ $i }}{{ $i === 1 ? ' ? Sama penting' : '' }}</option>@endfor
    </select>
    <div class="form-text">1 = sama penting, 3 = cukup lebih penting, 5 = kuat, 7 = sangat, 9 = ekstrem. Angka genap adalah tingkat antara.</div>
    @error('intensity')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('ahp-comparisons.index') }}" class="btn btn-secondary">Batal</a>
    <button type="submit" class="btn btn-app-secondary">Simpan</button>
</div>
