<div class="app-overlay"></div>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo mb-2">
        <a href="{{ route('dashboard') }}" class="app-brand-link d-flex align-items-center text-decoration-none">
            <span class="d-flex flex-column">
                <span class="fw-bold text-app-primary">{{ config('app.name') }}</span>
                <span class="small text-muted mt-1">AHP dan SAW</span>
            </span>
        </a>

        <a href="#" class="layout-menu-toggle menu-link text-large ms-auto d-none d-xl-none"
            data-bs-toggle="sidebar" data-target="#layout-menu" data-overlay="true">
        </a>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var overlay = document.querySelector('.app-overlay');
                var closeBtn = document.querySelector('.layout-menu-toggle[data-bs-toggle="sidebar"]');
                if (overlay && closeBtn) {
                    overlay.addEventListener('click', function() {
                        closeBtn.click();
                    });
                }
            });
        </script>
    </div>

    <div class="menu-inner">
        <ul class="menu-inner py-1 grow">

            <!-- Dashboard -->
            <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link text-decoration-none">
                    <i class="menu-icon tf-icons bi bi-speedometer2"></i>
                    <div>Dashboard</div>
                </a>
            </li>

            <!-- Master Data -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Master Data</span>
            </li>

            <!-- User -->
            <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}" class="menu-link text-decoration-none">
                    <i class="menu-icon tf-icons bi bi-people"></i>
                    <div>Data Pengguna</div>
                </a>
            </li>
            <!-- Kriteria -->
            <li class="menu-item {{ request()->routeIs('criteria.*') ? 'active' : '' }}">
                <a href="{{ route('criteria.index') }}" class="menu-link text-decoration-none">
                    <i class="menu-icon tf-icons bi bi-list-check"></i>
                    <div>Data Kriteria</div>
                </a>
            </li>
            <!-- Alternatif -->
            <li class="menu-item {{ request()->routeIs('alternatives.*') ? 'active' : '' }}">
                <a href="{{ route('alternatives.index') }}" class="menu-link text-decoration-none">
                    <i class="menu-icon tf-icons bi bi-grid"></i>
                    <div>Data Alternatif</div>
                </a>
            </li>
            <!-- Produk -->
            {{-- <li class="menu-item {{ request()->routeIs('produk.*') ? 'active' : '' }}">
                <a href="{{ route('produk.index') }}" class="menu-link text-decoration-none">
                    <i class="menu-icon tf-icons bi bi-box-seam"></i>
                    <div>Data Produk</div>
                </a>
            </li> --}}
            <!-- Pelanggan -->
            {{-- <li class="menu-item {{ request()->routeIs('pelanggan.*') ? 'active' : '' }}">
                <a href="{{ route('pelanggan.index') }}" class="menu-link text-decoration-none">
                    <i class="menu-icon tf-icons bi bi-person-check"></i>
                    <div>Data Pelanggan</div>
                </a>
            </li> --}}
            <!-- Perhitungan -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Perhitungan</span>
            </li>
            <li class="menu-item {{ request()->routeIs('ahp-comparisons.*') ? 'active' : '' }}">
                <a href="{{ route('ahp-comparisons.index') }}" class="menu-link text-decoration-none">
                    <i class="menu-icon tf-icons bi bi-arrow-left-right"></i>
                    <div>Perbandingan AHP</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('ahp.*') ? 'active' : '' }}">
                <a href="{{ route('ahp.index') }}" class="menu-link text-decoration-none">
                    <i class="menu-icon tf-icons bi bi-diagram-3"></i>
                    <div>Pembobotan AHP</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('saw.*') ? 'active' : '' }}">
                <a href="{{ route('saw.index') }}" class="menu-link text-decoration-none">
                    <i class="menu-icon tf-icons bi bi-bar-chart"></i>
                    <div>Pemeringkatan SAW</div>
                </a>
            </li>
            
            
          

        </ul>
    </div>
</aside>
