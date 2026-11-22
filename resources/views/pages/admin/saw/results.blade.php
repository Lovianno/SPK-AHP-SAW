<section class="card shadow-sm border-0 mb-4 p-3" aria-labelledby="saw-scores-title">
    <div class="card-header bg-white border-0">
        <h5 id="saw-scores-title" class="fw-semibold mb-1">1. Data penilaian</h5>
        <p class="text-muted mb-0">Bobot berasal dari AHP. Nilai alternatif memakai skor 1 sampai 100.</p>
    </div>
    <div class="card-body">
        <h6>Bobot kriteria</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th scope="col">Kriteria</th><th scope="col">Atribut</th><th scope="col">Bobot AHP</th></tr></thead>
                <tbody>
                    @foreach ($data['criteria'] as $criterion)
                        <tr><th scope="row">{{ $criterion['name'] }}</th><td>{{ ucfirst($criterion['attribute']) }}</td><td>{{ number_format($ahp['weights'][$criterion['id']], 6, ',', '.') }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h6 class="mt-3">Nilai alternatif</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead><tr><th scope="col">Alternatif</th>@foreach ($data['criteria'] as $criterion)<th scope="col">{{ $criterion['name'] }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach ($data['alternatives'] as $alternative)
                        <tr><th scope="row">{{ $alternative['name'] }}</th>@foreach ($data['criteria'] as $criterion)<td>{{ number_format((float) $raw[$alternative['id']][$criterion['id']], 4, ',', '.') }}</td>@endforeach</tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><th>Minimum</th>@foreach ($data['criteria'] as $criterion)<th>{{ number_format($saw['min'][$criterion['id']], 4, ',', '.') }}</th>@endforeach</tr>
                    <tr><th>Maksimum</th>@foreach ($data['criteria'] as $criterion)<th>{{ number_format($saw['max'][$criterion['id']], 4, ',', '.') }}</th>@endforeach</tr>
                </tfoot>
            </table>
        </div>
    </div>
</section>

<section class="card shadow-sm border-0 mb-4 p-3" aria-labelledby="saw-normal-title">
    <div class="card-header bg-white border-0">
        <h5 id="saw-normal-title" class="fw-semibold mb-1">2. Normalisasi dan kontribusi</h5>
        <p class="text-muted mb-0">Benefit: nilai dibagi maksimum. Cost: minimum dibagi nilai. Kontribusi adalah hasil normalisasi dikali bobot AHP.</p>
    </div>
    <div class="card-body">
        <h6>Nilai normalisasi</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead><tr><th scope="col">Alternatif</th>@foreach ($data['criteria'] as $criterion)<th scope="col">{{ $criterion['name'] }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach ($data['alternatives'] as $alternative)
                        <tr><th scope="row">{{ $alternative['name'] }}</th>@foreach ($data['criteria'] as $criterion)<td>{{ number_format($saw['normal'][$alternative['id']][$criterion['id']], 6, ',', '.') }}</td>@endforeach</tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h6 class="mt-3">Kontribusi bobot</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead><tr><th scope="col">Alternatif</th>@foreach ($data['criteria'] as $criterion)<th scope="col">{{ $criterion['name'] }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach ($data['alternatives'] as $alternative)
                        <tr><th scope="row">{{ $alternative['name'] }}</th>@foreach ($data['criteria'] as $criterion)<td>{{ number_format($saw['contributions'][$alternative['id']][$criterion['id']], 6, ',', '.') }}</td>@endforeach</tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="card shadow-sm border-0 mb-4 p-3" aria-labelledby="saw-ranking-title">
    <div class="card-header bg-white border-0">
        <h5 id="saw-ranking-title" class="fw-semibold mb-1">3. Hasil pemeringkatan</h5>
        <p class="text-muted mb-0">Nilai akhir adalah jumlah seluruh kontribusi bobot. Nilai terbesar mendapat peringkat pertama.</p>
    </div>
    <div class="card-body">
        <p><strong>Alternatif terbaik:</strong> {{ implode(', ', array_column($saw['best'], 'name')) }}.</p>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th scope="col">Peringkat</th><th scope="col">Alternatif</th><th scope="col">Nilai akhir</th></tr></thead>
                <tbody>
                    @foreach ($saw['ranking'] as $row)
                        <tr><td>{{ $row['rank'] }}</td><th scope="row">{{ $row['name'] }}</th><td>{{ number_format($row['value'], 6, ',', '.') }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-muted small mb-0">Peringkat dihitung dari nilai penuh sebelum pembulatan tampilan. Nilai yang sama mendapat peringkat yang sama.</p>
    </div>
</section>
