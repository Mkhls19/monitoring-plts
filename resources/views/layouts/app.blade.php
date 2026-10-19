<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring PLTS - @yield('title')</title>
    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    @stack('extra_css')
</head>
<body class="layout-body">
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="appSidebar">
            <div class="sidebar-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2>PLTS Admin</h2>
                </div>
                <button id="mobileMenuBtn" class="mobile-menu-btn" style="display: none; background: transparent; border: none; color: var(--text-primary); font-size: 1.5rem; cursor: pointer;">
                    <i class="ph ph-list"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="ph ph-squares-four"></i> Dashboard</a></li>
                    <li><a href="{{ url('monitoring') }}" class="nav-link {{ request()->is('monitoring') ? 'active' : '' }}"><i class="ph ph-chart-line-up"></i> Monitoring</a></li>
                    <li><a href="{{ url('history') }}" class="nav-link {{ request()->is('history') ? 'active' : '' }}"><i class="ph ph-clock-counter-clockwise"></i> Historis</a></li>
                    <li><a href="{{ url('logs') }}" class="nav-link {{ request()->is('logs') ? 'active' : '' }}"><i class="ph ph-list-dashes"></i> Logs</a></li>
                    <li><a href="{{ url('reports') }}" class="nav-link {{ request()->is('reports') ? 'active' : '' }}"><i class="ph ph-file-text"></i> Reports</a></li>
                    <li><a href="{{ url('prediction') }}" class="nav-link {{ request()->is('prediction') ? 'active' : '' }}"><i class="ph ph-magic-wand"></i> Prediksi</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-logout" style="width: 100%; background: transparent; cursor: pointer; border: 1px solid rgba(239,68,68,0.3);"><i class="ph ph-sign-out"></i> Logout</button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('appSidebar');
            
            if(menuBtn && sidebar) {
                menuBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('mobile-open');
                });
            }
        });
    </script>
    @stack('extra_js')
</body>
</html>