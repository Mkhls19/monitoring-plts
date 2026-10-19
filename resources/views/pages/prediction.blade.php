@extends('layouts.app')

@section('title', 'Forecasting Energi PV Harian')

@push('extra_css')
<style>
    .section-title {
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        border-bottom: 2px solid var(--card-border);
        padding-bottom: 0.5rem;
        color: var(--text-primary);
    }
    .status-card {
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
    .status-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: #cbd5e1;
    }
    .status-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 700;
    }
    .status-val {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--text-primary);
        letter-spacing: -0.03em;
    }
    .chart-container {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
    }
    .table-container {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 1rem;
        padding: 1.5rem;
        overflow-x: auto;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
    }
    table.forecast-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    table.forecast-table th, table.forecast-table td {
        padding: 0.8rem 0.6rem;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
    }
    table.forecast-table th {
        color: var(--accent-blue);
        font-weight: 700;
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    table.forecast-table tbody tr:hover {
        background: #f8fafc;
    }
    .btn-chart-action {
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
    .btn-chart-action:hover {
        background: #f1f5f9;
        color: var(--text-primary);
    }
    .btn-chart-action.active {
        background: #eff6ff !important;
        border-color: var(--accent-blue) !important;
        color: var(--accent-blue) !important;
        font-weight: 700;
        box-shadow: 0 2px 4px rgba(37,99,235,0.15);
    }
    .trend-text {
        font-size: 0.95rem;
        color: var(--accent-blue);
        line-height: 1.6;
        padding: 1.1rem 1.35rem;
        background: #eff6ff;
        border-radius: 0.5rem;
        box-shadow: inset 3.5px 0 0 var(--accent-blue);
        margin-top: 1.5rem;
        margin-bottom: 2rem;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1>Forecasting Energi PV Harian</h1>
        <p class="subtitle">Analisis akademik peramalan energi matahari menggunakan metode Double Exponential Smoothing (DES)</p>
    </div>
</div>

<!-- SECTION 1: FORECAST SUMMARY (Core Parameters) -->
<div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 1rem;">
    <div class="status-card" style="border-top: 4px solid var(--accent-yellow);">
        <div class="status-label">Hasil Prediksi Besok</div>
        <div class="status-val" style="color: var(--accent-yellow);">
            {{ $forecast ? number_format($forecast->hasil_prediksi, 2) : '0.00' }} <span style="font-size: 1.1rem; font-weight: 700;">kWh</span>
        </div>
    </div>
    <div class="status-card" style="border-top: 4px solid var(--accent-red);">
        <div class="status-label">Akurasi Error (MAPE)</div>
        <div class="status-val" style="color: var(--accent-red);">
            {{ number_format($mape, 2) }} <span style="font-size: 1.1rem; font-weight: 700;">%</span>
        </div>
    </div>
    <div class="status-card" style="border-top: 4px solid var(--accent-blue);">
        <div class="status-label">Rata-rata Energi PV</div>
        <div class="status-val" style="color: var(--accent-blue);">
            {{ number_format($avgEnergy, 2) }} <span style="font-size: 1.1rem; font-weight: 700;">kWh</span>
        </div>
    </div>
    <div class="status-card" style="border-top: 4px solid var(--accent-green);">
        <div class="status-label">Parameter Alpha & Beta</div>
        <div class="status-val" style="color: var(--accent-green); font-size: 1.35rem; font-weight: 800;">
            alpha : {{ $alpha }} <span style="color: #cbd5e1;">|</span> beta : {{ $beta }}
        </div>
    </div>
</div>

<!-- ANALISIS SUMMARY -->
<div class="trend-text">
    <i class="ph ph-info" style="font-size: 1.1rem; vertical-align: text-bottom;"></i> <strong>Ringkasan Evaluasi:</strong> Berdasarkan pengujian terhadap <strong>{{ $trainingCount }} data historis harian</strong>, parameter optimal metode <em>Double Exponential Smoothing</em> (DES) pada sistem ini adalah <strong>Alpha = {{ $alpha }}</strong> dan <strong>Beta = {{ $beta }}</strong>, dengan perolehan tingkat akurasi <em>Mean Absolute Percentage Error</em> (MAPE) sebesar <strong style="color: var(--accent-red); font-size: 1.05rem;">{{ number_format($mape, 2) }}%</strong>.
</div>

<!-- SECTION 2: FORECAST TABLE (Raw Data First) -->
<h2 class="section-title"><i class="ph ph-table"></i> Detail Data Peramalan Harian</h2>
<div class="table-container">
    <table class="forecast-table" id="forecastTable">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Energi Aktual (kWh)</th>
                <th>Forecast DES (kWh)</th>
                <th>Error (kWh)</th>
                <th>Error (%)</th>
            </tr>
        </thead>
        <tbody>
            <!-- Populated via JS -->
        </tbody>
    </table>
</div>

<!-- SECTION 3: FORECAST CHART (Visualization Last) -->
<h2 class="section-title"><i class="ph ph-chart-line-up"></i> Forecast Chart Interaktif</h2>
<div class="chart-container" style="padding-bottom: 0.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
        <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text-primary);">Perbandingan Energi Aktual vs Prediksi DES</h3>
        <div class="chart-filters" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <button class="btn-chart-action" id="btn-30" onclick="updateChartRange(30, this)">30 Hari</button>
            <button class="btn-chart-action" onclick="updateChartRange(90, this)">90 Hari</button>
            <button class="btn-chart-action" onclick="updateChartRange(365, this)">1 Tahun</button>
            <button class="btn-chart-action" onclick="updateChartRange('all', this)">Semua Data</button>
            <button class="btn-chart-action" onclick="resetZoom()"><i class="ph ph-arrows-out"></i> Reset Zoom</button>
        </div>
    </div>
    <div style="position: relative; height: 320px; width: 100%;">
        <canvas id="forecastChart"></canvas>
    </div>
    <small style="color: var(--text-secondary); font-weight: 600; display: block; margin-top: 1rem; text-align: center;"><i class="ph ph-mouse"></i> Scroll untuk Zoom | Drag untuk Pan (Menggeser Kiri/Kanan)</small>
</div>

@endsection

@push('extra_js')
<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
<script>
    let forecastChart;
    let allLabels = [];
    let allActualData = [];
    let allForecastData = [];
    let forecastNextLabel = '';
    let forecastNextVal = 0;

    function resetZoom() {
        if(forecastChart) {
            forecastChart.resetZoom();
        }
    }

    function updateChartRange(days, btnElement = null) {
        if (btnElement) {
            document.querySelectorAll('.chart-filters .btn-chart-action').forEach(btn => {
                btn.classList.remove('active');
            });
            btnElement.classList.add('active');
        }

        let sliceStart = 0;
        if (days !== 'all') {
            sliceStart = Math.max(0, allLabels.length - days);
        }

        const currentLabels = allLabels.slice(sliceStart);
        currentLabels.push(forecastNextLabel);

        const currentActual = allActualData.slice(sliceStart);
        currentActual.push(null);

        const currentForecast = allForecastData.slice(sliceStart);
        currentForecast.push(forecastNextVal);
        
        const ptRadius = currentLabels.length > 60 ? 0 : 3;
        const hovRadius = currentLabels.length > 60 ? 4 : 6;

        if (forecastChart) {
            forecastChart.data.labels = currentLabels;
            forecastChart.data.datasets[0].data = currentActual;
            forecastChart.data.datasets[0].pointRadius = ptRadius;
            forecastChart.data.datasets[0].pointHoverRadius = hovRadius;
            forecastChart.data.datasets[1].data = currentForecast;
            forecastChart.data.datasets[1].pointRadius = ptRadius;
            forecastChart.data.datasets[1].pointHoverRadius = hovRadius;
            forecastChart.update();
        } else {
            initChart(currentLabels, currentActual, currentForecast, ptRadius, hovRadius);
        }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const response = await fetch("{{ url('api/v1/forecast') }}");
            if (!response.ok) return;
            const result = await response.json();
            
            if (result.message && !result.historical) {
                alert(result.message);
                return;
            }
            const historical = result.historical;
            const forecastNext = result.forecast; 

            // Simpan Data ke Global Array
            allLabels = historical.map(item => item.tanggal);
            allActualData = historical.map(item => item.aktual);
            allForecastData = historical.map(item => item.prediksi);
            
            forecastNextLabel = forecastNext.tanggal;
            forecastNextVal = forecastNext.prediksi;

            // Tampilkan Tabel (Terbalik, dari yang terbaru)
            const tbody = document.querySelector('#forecastTable tbody');
            const tableData = [...historical].reverse().slice(0, 30); 
            
            // Masukkan data besok di baris pertama
            tbody.innerHTML += `
                <tr style="background: #fffbeb;">
                    <td style="color: #d97706; font-weight: 800;">${forecastNext.tanggal} (Besok)</td>
                    <td style="font-weight: 600;">-</td>
                    <td style="color: #d97706; font-weight: 800;">${forecastNext.prediksi.toFixed(2)}</td>
                    <td style="font-weight: 600;">-</td>
                    <td style="font-weight: 600;">-</td>
                </tr>
            `;

            tableData.forEach(d => {
                const errorAbs = Math.abs(d.aktual - d.prediksi).toFixed(2);
                let errorPct = 0;
                if(d.aktual > 0) {
                    errorPct = ((errorAbs / d.aktual) * 100).toFixed(2);
                }
                
                tbody.innerHTML += `
                    <tr>
                        <td style="font-weight: 600;">${d.tanggal}</td>
                        <td style="color: #2563eb; font-weight: 700;">${d.aktual.toFixed(2)}</td>
                        <td style="color: #d97706; font-weight: 700;">${d.prediksi.toFixed(2)}</td>
                        <td style="color: #dc2626; font-weight: 700;">${errorAbs}</td>
                        <td style="font-weight: 700;">${errorPct}%</td>
                    </tr>
                `;
            });

            // Inisialisasi Chart via Filter Default (30 Hari)
            updateChartRange(30, document.getElementById('btn-30'));

        } catch (err) {
            console.error("Gagal mengambil data peramalan:", err);
        }
    });

    function initChart(labels, actualData, forecastData, ptRadius, hovRadius) {
        const ctx = document.getElementById('forecastChart').getContext('2d');
        forecastChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { 
                        label: 'Energi Aktual PV (kWh)', 
                        borderColor: '#2563eb', 
                        backgroundColor: '#2563eb', 
                        data: actualData,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: ptRadius,
                        pointHoverRadius: hovRadius
                    },
                    { 
                        label: 'Forecast DES (kWh)', 
                        borderColor: '#d97706', 
                        borderDash: [5, 5], 
                        data: forecastData,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: ptRadius,
                        pointHoverRadius: hovRadius
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: { 
                        title: { display: false },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }, 
                        ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans', weight: '600' }, maxTicksLimit: 12, maxRotation: 0 }
                    },
                    y: { 
                        title: { display: true, text: 'Energi PV (kWh)', color: '#64748b', font: { family: 'Plus Jakarta Sans', weight: '700', size: 11 } },
                        type: 'linear', 
                        display: true, 
                        position: 'left', 
                        grid: { color: 'rgba(0, 0, 0, 0.06)' }, 
                        ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans', weight: '600' } },
                        beginAtZero: true
                    }
                },
                plugins: { 
                    legend: { labels: { color: '#0f172a', font: { family: 'Plus Jakarta Sans', weight: '700' }, usePointStyle: true, boxWidth: 8 } },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#38bdf8',
                        bodyColor: '#f8fafc',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 12,
                        titleFont: { family: 'Plus Jakarta Sans', weight: '800' },
                        bodyFont: { family: 'Plus Jakarta Sans', weight: '600' },
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + ' kWh';
                            },
                            afterBody: function(context) {
                                if(context.length === 2) {
                                    const actual = context[0].parsed.y;
                                    const forecast = context[1].parsed.y;
                                    const err = Math.abs(actual - forecast).toFixed(2);
                                    return `\nSelisih Error: ${err} kWh`;
                                }
                                return '';
                            }
                        }
                    },
                    zoom: {
                        pan: { enabled: true, mode: 'x' },
                        zoom: { wheel: { enabled: true }, pinch: { enabled: true }, mode: 'x' }
                    }
                }
            }
        });
    }
</script>
@endpush
