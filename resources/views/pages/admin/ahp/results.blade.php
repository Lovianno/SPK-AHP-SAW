<section class="card shadow-sm border-0 mb-4 p-3" aria-labelledby="ahp-matrix-title">
    <div class="card-header bg-white border-0">
        <h5 id="ahp-matrix-title" class="fw-semibold mb-1">1. Matriks perbandingan</h5>
        <p class="text-muted mb-0">Nilai pada baris menunjukkan perbandingannya terhadap kriteria di kolom. Arah sebaliknya menggunakan nilai kebalikan.</p>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead><tr><th scope="col">Kriteria</th>@foreach ($data['criteria'] as $criterion)<th scope="col">{{ $criterion['name'] }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach ($data['criteria'] as $row)
                        <tr><th scope="row">{{ $row['name'] }}</th>@foreach ($data['criteria'] as $column)<td>{{ number_format($ahp['matrix'][$row['id']][$column['id']], 6, ',', '.') }}</td>@endforeach</tr>
                    @endforeach
                </tbody>
                <tfoot><tr><th>Jumlah kolom</th>@foreach ($data['criteria'] as $criterion)<th>{{ number_format($ahp['sums'][$criterion['id']], 6, ',', '.') }}</th>@endforeach</tr></tfoot>
            </table>
        </div>
    </div>
</section>

<section class="card shadow-sm border-0 mb-4 p-3" aria-labelledby="ahp-weight-title">
    <div class="card-header bg-white border-0">
        <h5 id="ahp-weight-title" class="fw-semibold mb-1">2. Normalisasi dan bobot</h5>
        <p class="text-muted mb-0">Setiap nilai dibagi jumlah kolomnya. Rata-rata nilai pada satu baris menjadi bobot kriteria tersebut.</p>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead><tr><th scope="col">Kriteria</th>@foreach ($data['criteria'] as $criterion)<th scope="col">{{ $criterion['name'] }}</th>@endforeach<th scope="col">Jumlah baris</th><th scope="col">Bobot</th><th scope="col">Persentase</th></tr></thead>
                <tbody>
                    @foreach ($data['criteria'] as $row)
                        <tr><th scope="row">{{ $row['name'] }}</th>@foreach ($data['criteria'] as $column)<td>{{ number_format($ahp['normal'][$row['id']][$column['id']], 6, ',', '.') }}</td>@endforeach<td>{{ number_format($ahp['rowSums'][$row['id']], 6, ',', '.') }}</td><td>{{ number_format($ahp['weights'][$row['id']], 6, ',', '.') }}</td><td>{{ number_format($ahp['weights'][$row['id']] * 100, 2, ',', '.') }}%</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="card shadow-sm border-0 mb-4 p-3" aria-labelledby="ahp-consistency-title">
    <div class="card-header bg-white border-0">
        <h5 id="ahp-consistency-title" class="fw-semibold mb-1">3. Uji konsistensi</h5>
        <p class="text-muted mb-0">Aw diperoleh dari matriks perbandingan dikalikan bobot. Nilai lambda setiap baris adalah Aw dibagi bobot.</p>
    </div>
    <div class="card-body">
        <div class="table-responsive mb-4">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Kriteria</th><th>Weighted SUM</th><th>Bobot</th><th>Lambda</th></tr></thead>
                <tbody>
                    @foreach ($data['criteria'] as $criterion)
                        <tr><td>{{ $criterion['name'] }}</td><td>{{ number_format($ahp['aw'][$criterion['id']], 6, ',', '.') }}</td><td>{{ number_format($ahp['weights'][$criterion['id']], 6, ',', '.') }}</td><td>{{ number_format($ahp['lambda'][$criterion['id']], 6, ',', '.') }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <h6 class="fw-semibold mb-2">Ringkasan konsistensi</h6>
        <div class="table-responsive">
            <table class="table table-sm w-auto">
                <tbody>
                    <tr><th scope="row" class="pe-4">Lambda rata-rata</th><td>{{ number_format($ahp['lambdaMax'], 6, ',', '.') }}</td></tr>
                    <tr><th scope="row" class="pe-4">CI</th><td>{{ number_format($ahp['ci'], 6, ',', '.') }}</td></tr>
                    <tr><th scope="row" class="pe-4">RI</th><td>{{ number_format($ahp['ri'], 2, ',', '.') }}</td></tr>
                    <tr><th scope="row" class="pe-4">CR</th><td>{{ number_format($ahp['cr'], 6, ',', '.') }}</td></tr>
                </tbody>
            </table>
        </div>
        @if ($ahp['consistent'])
            <div class="alert alert-success mb-3" role="status">
                <strong>Konsisten.</strong> Nilai CR {{ number_format($ahp['cr'], 6, ',', '.') }} memenuhi batas maksimal 0,1. Bobot dapat digunakan untuk SAW.
            </div>
            <a href="{{ route('saw.index') }}" class="btn btn-app-secondary">Lanjut ke SAW</a>
        @else
            <div class="alert alert-warning mb-3" role="status">
                <strong>Belum konsisten.</strong> Nilai CR {{ number_format($ahp['cr'], 6, ',', '.') }} melebihi batas maksimal 0,1. Tinjau kembali perbandingan sebelum melanjutkan ke SAW.
            </div>
            <a href="{{ route('ahp-comparisons.index') }}" class="btn btn-outline-secondary">Tinjau Perbandingan</a>
        @endif
        <p class="text-muted small mb-0 mt-3">Pembulatan angka hanya untuk tampilan; perhitungan memakai nilai penuh.</p>
    </div>
</section>
