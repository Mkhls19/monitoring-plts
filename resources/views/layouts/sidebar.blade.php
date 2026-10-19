<aside class="sidebar">
    <div class="sidebar-header">
        <h2>PLTS Admin</h2>
        <div class="user-info">Logged in as: <strong>Admin</strong></div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <!-- Main Section with Submenu -->
            <li class="nav-item">
                <a class="nav-link fw-bold text-dark d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#mainSubmenu" role="button" aria-expanded="true" aria-controls="mainSubmenu">
                    <span class="d-flex align-items-center gap-2"><i class="ph ph-squares-four"></i> Main Dashboard</span>
                    <i class="ph ph-caret-down fs-6 text-muted"></i>
                </a>
                <div class="collapse show" id="mainSubmenu">
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item">
                            <a class="nav-link py-2 {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard"><i class="ph ph-chart-line-up"></i> Ringkasan Utama</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-2 {{ request()->is('informasi-dc') ? 'active' : '' }}" href="/informasi-dc"><i class="ph ph-lightning"></i> Informasi DC</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-2 {{ request()->is('informasi-ac') ? 'active' : '' }}" href="/informasi-ac"><i class="ph ph-plug"></i> Informasi AC</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-2 {{ request()->is('informasi-energi') ? 'active' : '' }}" href="/informasi-energi"><i class="ph ph-battery-full"></i> Informasi Energi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-2 {{ request()->is('informasi-ekstra') ? 'active' : '' }}" href="/informasi-ekstra"><i class="ph ph-info"></i> Informasi Ekstra</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Analitik Section with Submenu -->
            <li class="nav-item mt-2">
                <a class="nav-link fw-bold text-dark d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#analitikSubmenu" role="button" aria-expanded="false" aria-controls="analitikSubmenu">
                    <span class="d-flex align-items-center gap-2"><i class="ph ph-trend-up"></i> Analitik Data</span>
                    <i class="ph ph-caret-down fs-6 text-muted"></i>
                </a>
                <div class="collapse mt-1" id="analitikSubmenu">
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item">
                            <a class="nav-link py-2 {{ request()->is('tabel-energi') ? 'active' : '' }}" href="/tabel-energi"><i class="ph ph-table"></i> Tabel Energi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-2 {{ request()->is('grafik-historis') ? 'active' : '' }}" href="/grafik-historis"><i class="ph ph-chart-bar"></i> Grafik Historis</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- General Menu -->
            <li class="nav-item mt-2">
                <a class="nav-link text-secondary fw-bold {{ request()->is('konfigurasi') ? 'active' : '' }}" href="/konfigurasi"><i class="ph ph-gear"></i> Konfigurasi</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-secondary fw-bold {{ request()->is('status-perangkat') ? 'active' : '' }}" href="/status-perangkat"><i class="ph ph-desktop"></i> Status Perangkat</a>
            </li>
            
            <li class="nav-item mt-4">
                <a class="nav-link text-danger fw-bold d-flex align-items-center gap-2 rounded border border-danger border-opacity-10" href="/reset-system" style="background: rgba(220, 53, 69, 0.05); margin: 0 1rem;">
                    <i class="ph ph-warning-circle text-danger"></i> Reset Data
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="/logout" class="btn-logout"><i class="ph ph-sign-out"></i> Logout</a>
    </div>
</aside>