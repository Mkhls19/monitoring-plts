@extends('layouts.app')

@section('title', 'Dashboard Realtime')

@push('extra_css')
<style>
    .mini-status-header {
        display: flex;
        gap: 1.5rem;
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 0.75rem;
        padding: 0.85rem 1.5rem;
        margin-bottom: 2rem;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: var(--shadow-sm);
    }
    .mini-status-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: var(--text-secondary);
        font-weight: 600;
    }
    .mini-status-value {
        font-weight: 800;
        color: var(--text-primary);
    }
    .section-title {
        margin-bottom: 1rem;
        font-size: 0.9rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        border-bottom: 2px solid var(--card-border);
        padding-bottom: 0.5rem;
    }
    
    /* Prominent Energy Summary Cards */
    .energy-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 1rem;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .energy-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: #cbd5e1;
    }
    .energy-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 700;
    }
    .energy-val {
        font-size: 2.1rem;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    
    /* Compact Parameter Cards */
    .compact-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 0.75rem;
        padding: 1.1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: var(--shadow-sm);
        transition: background 0.2s, transform 0.2s;
    }
    .compact-card:hover {
        background: #f8fafc;
        transform: translateY(-2px);
    }
    .compact-icon {
        width: 46px;
        height: 46px;
        border-radius: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        background: #f1f5f9;
        flex-shrink: 0;
    }
    .compact-content {
        flex: 1;
    }
    .compact-title {
        font-size: 0.75rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
        font-weight: 700;
    }
    .compact-val {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--text-primary);
        letter-spacing: -0.02em;
    }
    .compact-unit {
        font-size: 0.85rem;
        color: var(--text-secondary);
        font-weight: 600;
    }

    .mode-pv { color: var(--accent-yellow) !important; }
    .mode-battery { color: var(--accent-blue) !important; }
    .mode-ac { color: var(--accent-green) !important; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1>Dashboard PLTS</h1>
        <p class="subtitle">Sistem Monitoring Energi & Forecasting Inverter</p>
    </div>
</div>

<!-- SECTION 1: HEADER STATUS MINI -->
<div class="mini-status-header">
    <div class="mini-status-item">
        <i class="ph ph-activity" style="font-size: 1.25rem; color: var(--accent-blue);"></i>
        <span>Status Sistem:</span>
        <div style="display: flex; align-items: center; gap: 0.4rem;">
            <span class="status-dot" id="status-dot" style="display: inline-block; width: 9px; height: 9px; border-radius: 50%; background-color: var(--accent-red);"></span>
            <span class="mini-status-value" id="status-text" style="color: var(--accent-red); font-weight: 800;">Offline</span>
        </div>
    </div>
    <div class="mini-status-item">
        <i class="ph ph-clock" style="font-size: 1.25rem; color: var(--text-secondary);"></i>
        <span>Terakhir Update:</span>
        <span class="mini-status-value" id="last-update" style="font-weight: 700;">-</span>
    </div>
    <div class="mini-status-item">
        <i class="ph ph-cpu" style="font-size: 1.25rem; color: var(--accent-purple);"></i>
        <span>Mode Operasi:</span>
        <span class="mini-status-value" id="system-mode" style="font-weight: 800;">Memeriksa...</span>
    </div>
</div>

<!-- SECTION 2: ENERGY SUMMARY & FORECASTING (PRIORITY 1) -->
<h2 class="section-title" style="color: var(--accent-green);"><i class="ph ph-chart-bar"></i> Rekap Energi & Peramalan </h2>
<div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 2.5rem;">
    <div class="energy-card" style="border-top: 4px solid var(--accent-green);">
        <div class="energy-label">Energi Hari Ini</div>
        <div class="energy-val" style="color: var(--accent-green);">
            <span id="val-energy-today">{{ $todayEnergy ? number_format($todayEnergy->energi_pv_harian, 2) : '0.00' }}</span> <span style="font-size: 1rem; color: var(--text-secondary); font-weight: 700;">kWh</span>
        </div>
    </div>
    <div class="energy-card" style="border-top: 4px solid #047857;">
        <div class="energy-label">Energi Bulan Ini</div>
        <div class="energy-val" style="color: #047857;">
            <span id="val-energy-month">{{ $todayEnergy ? number_format($todayEnergy->energi_pv_bulanan, 2) : '0.00' }}</span> <span style="font-size: 1rem; color: var(--text-secondary); font-weight: 700;">kWh</span>
        </div>
    </div>
    <div class="energy-card" style="border-top: 4px solid var(--accent-yellow);">
        <div class="energy-label">Total Akumulasi Energi</div>
        <div class="energy-val" style="color: var(--accent-yellow);">
            <span id="val-energy-total">0.00</span> <span style="font-size: 1rem; color: var(--text-secondary); font-weight: 700;">kWh</span>
        </div>
    </div>
    <div class="energy-card" style="border-top: 4px solid var(--accent-purple);">
        <div class="energy-label">Forecast Besok (DES)</div>
        <div class="energy-val" style="color: var(--accent-purple);">
            {{ $forecast ? number_format($forecast->hasil_prediksi, 2) : '0.00' }} <span style="font-size: 1rem; color: var(--text-secondary); font-weight: 700;">kWh</span>
        </div>
    </div>
</div>

<!-- SECTION 3: MONITORING SINGKAT (PRIORITY 2) -->
<h2 class="section-title" style="color: var(--accent-blue);"><i class="ph ph-activity"></i> Parameter Sensor Realtime</h2>
<div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 2.5rem; gap: 1rem;">
    <div class="compact-card">
        <div class="compact-icon" style="color: var(--accent-yellow); background: #fef3c7;"><i class="ph ph-sun"></i></div>
        <div class="compact-content">
            <div class="compact-title">Daya Panel (PV Power)</div>
            <div class="compact-val"><span id="val-pv-power">0.00</span> <span class="compact-unit">Watt</span></div>
        </div>
    </div>
    <div class="compact-card">
        <div class="compact-icon" style="color: var(--accent-green); background: #d1fae5;"><i class="ph ph-battery-full"></i></div>
        <div class="compact-content">
            <div class="compact-title">Tegangan Baterai</div>
            <div class="compact-val"><span id="val-battery-voltage">0.00</span> <span class="compact-unit">Volt</span></div>
        </div>
    </div>
    <div class="compact-card">
        <div class="compact-icon" style="color: var(--accent-green); background: #d1fae5;"><i class="ph ph-battery-charging"></i></div>
        <div class="compact-content">
            <div class="compact-title">Kapasitas Baterai</div>
            <div class="compact-val"><span id="val-battery-capacity">0</span> <span class="compact-unit">%</span></div>
        </div>
    </div>
    <div class="compact-card">
        <div class="compact-icon" style="color: #db2777; background: #fce7f3;"><i class="ph ph-gauge"></i></div>
        <div class="compact-content">
            <div class="compact-title">Beban Inverter (Load)</div>
            <div class="compact-val"><span id="val-load-percent">0</span> <span class="compact-unit">%</span></div>
        </div>
    </div>
</div>
@endsection