@extends('layouts.app')

@section('title', 'Monitoring Observasi Realtime')

@push('extra_css')
<style>
    .chart-container {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
    }
    .chart-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        letter-spacing: -0.01em;
    }
    .filter-container {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .btn-filter {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: var(--text-secondary);
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-filter:hover {
        background: #f1f5f9;
        color: var(--text-primary);
    }
    .btn-filter.active {
        background: var(--accent-blue);
        color: white;
        border-color: var(--accent-blue);
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
    }
    .data-table-container {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 1rem;
        padding: 1.5rem;
        overflow-x: auto;
    }
    table.rt-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    table.rt-table th, table.rt-table td {
        padding: 0.8rem 0.5rem;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
    }
    table.rt-table th {
        color: var(--accent-blue);
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    table.rt-table tbody tr:hover {
        background: #f8fafc;
    }
    .val-highlight {
        font-weight: 800;
        font-size: 1.05rem;
        letter-spacing: -0.02em;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1>Monitoring Inverter Realtime</h1>
        <p class="subtitle">Panel observasi performa dan perilaku sistem Inverter PLTS</p>
    </div>
</div>

<!-- 4 SUBSISTEM CARDS -->
<div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); margin-bottom: 2rem; gap: 1.25rem;">
    <!-- 1. PV SYSTEM -->
    <div class="chart-container" style="height: auto;">
        <div class="chart-title" style="color: var(--accent-blue);"><i class="ph ph-solar-panel"></i> Panel Surya (PV)</div>
        <div class="data-table-container" style="padding: 0; background: transparent; border: none; box-shadow: none;">
            <table class="rt-table" style="width: 100%;">
                <tbody>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">PV Voltage</td><td style="text-align: right;"><span class="val-highlight" id="mon-pv-voltage">0.00</span> <strong>V</strong></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">PV Current</td><td style="text-align: right;"><span class="val-highlight" id="mon-pv-current">0.00</span> <strong>A</strong></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">PV Power</td><td style="text-align: right; color: var(--accent-blue);"><span class="val-highlight" id="mon-pv-power">0.00</span> <strong>W</strong></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Energi Hari Ini</td><td style="text-align: right;"><span class="val-highlight" id="mon-energy-today">0.00</span> <strong>kWh</strong></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Peak Sun Hour</td><td style="text-align: right;"><span class="val-highlight" id="mon-psh">0.00</span> <strong>Jam</strong></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. BATTERY SYSTEM -->
    <div class="chart-container" style="height: auto;">
        <div class="chart-title" style="color: var(--accent-green);"><i class="ph ph-battery-charging"></i> Sistem Baterai</div>
        <div class="data-table-container" style="padding: 0; background: transparent; border: none; box-shadow: none;">
            <table class="rt-table" style="width: 100%;">
                <tbody>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Battery Voltage</td><td style="text-align: right; color: var(--accent-green);"><span class="val-highlight" id="mon-battery-voltage">0.00</span> <strong>V</strong></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Battery Capacity</td><td style="text-align: right;"><span class="val-highlight" id="mon-battery-capacity">0</span> <strong>%</strong></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Battery Status</td><td style="text-align: right; font-weight: 700;"><span id="mon-battery-status">-</span></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Charge Current</td><td style="text-align: right;"><span class="val-highlight" id="mon-charge-current">0.00</span> <strong>A</strong></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Charger Temp</td><td style="text-align: right;"><span class="val-highlight" id="mon-charger-temp">0</span> <strong>°C</strong></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. INVERTER -->
    <div class="chart-container" style="height: auto;">
        <div class="chart-title" style="color: var(--accent-yellow);"><i class="ph ph-lightning"></i> Inverter Output</div>
        <div class="data-table-container" style="padding: 0; background: transparent; border: none; box-shadow: none;">
            <table class="rt-table" style="width: 100%;">
                <tbody>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Output Voltage</td><td style="text-align: right; color: var(--accent-yellow);"><span class="val-highlight" id="mon-ac-out-voltage">0.00</span> <strong>V</strong></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Output Freq</td><td style="text-align: right;"><span class="val-highlight" id="mon-ac-out-freq">0.00</span> <strong>Hz</strong></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Inverter Load</td><td style="text-align: right;"><span class="val-highlight" id="mon-load-percent">0</span> <strong>%</strong></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Operating Mode</td><td style="text-align: right; font-weight: 700;"><span id="mon-operating-mode">-</span></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Heatsink Temp</td><td style="text-align: right;"><span class="val-highlight" id="mon-heatsink-temp">0</span> <strong>°C</strong></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. SYSTEM STATUS -->
    <div class="chart-container" style="height: auto;">
        <div class="chart-title" style="color: var(--accent-purple);"><i class="ph ph-activity"></i> Status Sistem</div>
        <div class="data-table-container" style="padding: 0; background: transparent; border: none; box-shadow: none;">
            <table class="rt-table" style="width: 100%;">
                <tbody>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Controller Status</td><td style="text-align: right; font-weight: 700;"><span id="mon-status-controller">-</span></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Charger Status</td><td style="text-align: right; font-weight: 700;"><span id="mon-status-charger">-</span></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Warning</td><td style="text-align: right; font-weight: 700; color: var(--accent-yellow);"><span id="mon-warning-status">-</span></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Fault</td><td style="text-align: right; font-weight: 700; color: var(--accent-red);"><span id="mon-fault-status">-</span></td></tr>
                    <tr><td style="color: var(--text-secondary); font-weight: 600;">Last Update</td><td style="text-align: right; font-weight: 700;"><span id="mon-last-update">-</span></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SECTION 2: REALTIME CHARTS -->
<div class="cards-grid" style="grid-template-columns: 1fr; margin-bottom: 2rem; gap: 1.5rem;">
    <!-- CHART 1: PV PRODUCTION -->
    <div class="chart-container">
        <div class="chart-title" style="color: var(--accent-blue);"><i class="ph ph-solar-panel"></i> Grafik Detail Observasi Panel Surya</div>
        <div style="position: relative; height: 350px; width: 100%;">
            <canvas id="pvChart"></canvas>
        </div>
    </div>
</div>

@endsection

@push('extra_js')
<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
<script>
    let pvChart;
    let isUserInteract = false;

    const commonOptions = {
        responsive: true, maintainAspectRatio: false,
        scales: {
            x: { 
                grid: { color: 'rgba(0, 0, 0, 0.05)' }, 
                ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans', weight: '600' }, maxTicksLimit: 8, maxRotation: 0, autoSkip: true } 
            },
            y: { 
                type: 'linear', display: true, position: 'left', 
                grid: { color: 'rgba(0, 0, 0, 0.06)' }, 
                ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans', weight: '600' } },
                beginAtZero: true
            }
        },
        plugins: { 
            legend: { labels: { color: '#0f172a', font: { family: 'Plus Jakarta Sans', weight: '700' }, boxWidth: 10, usePointStyle: true } }, 
            tooltip: { 
                backgroundColor: '#1e293b',
                titleColor: '#38bdf8',
                bodyColor: '#f8fafc',
                borderColor: '#334155',
                borderWidth: 1,
                padding: 12,
                titleFont: { family: 'Plus Jakarta Sans', weight: '800' },
                bodyFont: { family: 'Plus Jakarta Sans', weight: '600' },
                mode: 'index', 
                intersect: false 
            },
            zoom: {
                pan: { 
                    enabled: true, mode: 'x',
                    onPanStart: () => { isUserInteract = true; }
                },
                zoom: { 
                    wheel: { enabled: true }, pinch: { enabled: true }, mode: 'x',
                    onZoomStart: () => { isUserInteract = true; }
                }
            }
        },
        elements: { 
            point: { radius: 0, hitRadius: 10, hoverRadius: 4 }, 
            line: { tension: 0.4, borderWidth: 2 } 
        },
        interaction: { mode: 'nearest', axis: 'x', intersect: false }
    };

    function initCharts() {
        pvChart = new Chart(document.getElementById('pvChart').getContext('2d'), {
            type: 'line',
            data: { labels: [], datasets: [
                { label: 'PV Power (W)', borderColor: '#d97706', backgroundColor: '#d97706', fill: false, data: [] },
                { label: 'PV Voltage (V)', borderColor: '#2563eb', backgroundColor: '#2563eb', fill: false, data: [] },
                { label: 'PV Current (A)', borderColor: '#059669', backgroundColor: '#059669', fill: false, data: [] }
            ]},
            options: commonOptions
        });
    }

    function formatChartDate(dateString) {
        const d = new Date(dateString);
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    // Fungsi Fetch khusus untuk nilai single (Realtime 4 Subsistem Cards)
    async function fetchLatestStatus() {
        try {
            const timestamp = new Date().getTime();
            const response = await fetch(`/api/v1/latest?_t=${timestamp}`);
            if (response.ok) {
                const data = await response.json();
                
                // PV
                if(document.getElementById('mon-pv-voltage')) document.getElementById('mon-pv-voltage').textContent = parseFloat(data.pvvolt || data.pv_voltage || 0).toFixed(2);
                if(document.getElementById('mon-pv-current')) document.getElementById('mon-pv-current').textContent = parseFloat(data.pvcurrent || data.pv_charge_current || 0).toFixed(2);
                if(document.getElementById('mon-pv-power')) document.getElementById('mon-pv-power').textContent = parseFloat(data.pvpower || data.pv_power || 0).toFixed(2);
                if(document.getElementById('mon-energy-today')) document.getElementById('mon-energy-today').textContent = parseFloat(data.energytoday || 0).toFixed(2);
                if(document.getElementById('mon-psh')) document.getElementById('mon-psh').textContent = parseFloat(data.psh || 0).toFixed(2);

                // Battery Table
                let batCap = Math.round(data.batcap || data.battery_capacity_percent || 0);
                if(document.getElementById('mon-battery-voltage')) document.getElementById('mon-battery-voltage').textContent = parseFloat(data.batvolt || data.battery_voltage || 0).toFixed(2);
                if(document.getElementById('mon-battery-capacity')) document.getElementById('mon-battery-capacity').textContent = batCap;
                if(document.getElementById('mon-battery-status')) document.getElementById('mon-battery-status').textContent = data.batstatus || '-';
                if(document.getElementById('mon-charge-current')) document.getElementById('mon-charge-current').textContent = parseFloat(data.chargecurrent || data.ac_charge_current || 0).toFixed(2);
                if(document.getElementById('mon-charger-temp')) document.getElementById('mon-charger-temp').textContent = Math.round(data.chargertemp || 0);

                // Inverter
                if(document.getElementById('mon-ac-out-voltage')) document.getElementById('mon-ac-out-voltage').textContent = parseFloat(data.invertervolt || data.ac_output_voltage || 0).toFixed(2);
                if(document.getElementById('mon-ac-out-freq')) document.getElementById('mon-ac-out-freq').textContent = parseFloat(data.inverterfreq || data.ac_output_frequency || 0).toFixed(2);
                if(document.getElementById('mon-load-percent')) document.getElementById('mon-load-percent').textContent = Math.round(data.loadpercen || data.inverter_load_percent || 0);
                if(document.getElementById('mon-operating-mode')) document.getElementById('mon-operating-mode').textContent = data.operatingmode || '-';
                if(document.getElementById('mon-heatsink-temp')) document.getElementById('mon-heatsink-temp').textContent = Math.round(data.heatsinktemp || 0);

                // Status
                if(document.getElementById('mon-status-controller')) document.getElementById('mon-status-controller').textContent = data.statuscontroller || '-';
                if(document.getElementById('mon-status-charger')) document.getElementById('mon-status-charger').textContent = data.statuscharger || '-';
                if(document.getElementById('mon-warning-status')) document.getElementById('mon-warning-status').textContent = data.warningstatus || '-';
                if(document.getElementById('mon-fault-status')) document.getElementById('mon-fault-status').textContent = data.faultstatus || '-';
                if(document.getElementById('mon-last-update')) document.getElementById('mon-last-update').textContent = formatChartDate(data.timestamp || data.created_at);

            }
        } catch (e) {
            console.error('Error fetching latest status:', e);
        }
    }

    // Fungsi Fetch khusus untuk Grafik (History)
    async function fetchHistoryForCharts() {
        try {
            // Kita batasi hanya ambil 60 data terakhir agar ringan di VPS
            const timestamp = new Date().getTime();
            const response = await fetch(`/api/v1/history?limit=60&_t=${timestamp}`);
            if (!response.ok) return;
            const historyData = await response.json();
            
            if (historyData.length === 0) {
                console.warn("⚠️ Data History Kosong! Pastikan zona waktu VPS sudah sesuai WIB (Asia/Jakarta) atau Cronjob MQTT berjalan baik.");
                return;
            }

            const labels = historyData.map(d => formatChartDate(d.created_at || d.timestamp));

            // Logika Auto-Scroll 30 data terakhir
            let minLabel = undefined;
            let maxLabel = undefined;
            if (!isUserInteract && labels.length > 30) {
                minLabel = labels[labels.length - 30];
                maxLabel = labels[labels.length - 1];
            }

            pvChart.data.labels = labels;
            pvChart.data.datasets[0].data = historyData.map(d => parseFloat(d.pvpower || d.pv_power || 0));
            pvChart.data.datasets[1].data = historyData.map(d => parseFloat(d.pvvolt || d.pv_voltage || 0));
            pvChart.data.datasets[2].data = historyData.map(d => parseFloat(d.pvcurrent || d.pv_charge_current || 0));
            
            if (minLabel) {
                pvChart.options.scales.x.min = minLabel;
                pvChart.options.scales.x.max = maxLabel;
            }
            pvChart.update('none');

        } catch (e) {
            console.error('Error fetching chart history:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initCharts();
        
        async function startPolling() {
            await fetchLatestStatus();
            await fetchHistoryForCharts();
            setTimeout(startPolling, 3000);
        }
        
        startPolling();
    });
</script>
@endpush
