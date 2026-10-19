@extends('layouts.app')

@section('title', 'Laporan & Rekapitulasi')

@push('extra_css')
<style>
    .filter-container {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
    }
    .form-group label {
        display: block;
        color: var(--text-secondary);
        font-size: 0.75rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
    }
    .form-control {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: var(--text-primary);
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-family: inherit;
        font-weight: 600;
        outline: none;
        width: 200px;
    }
    .form-control:focus {
        border-color: var(--accent-blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
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
    }
    .status-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
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
        box-shadow: var(--shadow-sm);
    }
    table.report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    table.report-table th, table.report-table td {
        padding: 0.8rem 0.6rem;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
    }
    table.report-table th {
        color: var(--accent-blue);
        font-weight: 700;
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    table.report-table tbody tr:hover {
        background: #f8fafc;
    }
    
    /* Print Styles */
    @media print {
        body, .layout-body, .app-layout, .main-content { 
            height: auto !important; 
            min-height: auto !important;
            overflow: visible !important; 
            position: static !important;
        }
        body { background: white !important; color: black !important; }
        .sidebar, .page-header button, .filter-container { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        .page-header h1 { color: black !important; }
        .page-header p { color: #333 !important; }
        .status-card, .chart-container, .table-container { 
            background: white !important; 
            border: 1px solid #ddd !important; 
            box-shadow: none !important; 
            color: black !important;
            page-break-inside: avoid;
            overflow: visible !important;
        }
        .status-label { color: #555 !important; }
        .status-val { color: black !important; }
        table.report-table th { background: #f0f0f0 !important; color: black !important; border-bottom: 2px solid #ccc !important; }
        table.report-table td { border-bottom: 1px solid #ddd !important; color: black !important; }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1>Laporan & Rekapitulasi Data</h1>
        <p class="subtitle" id="report-subtitle">Rekap data energi dan performa peramalan akademik</p>
    </div>
    <div>
        <button class="btn-primary" onclick="window.print()" style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700;">
            <i class="ph ph-printer" style="font-size: 1.1rem;"></i> Cetak Laporan PDF
        </button>
    </div>
</div>

<!-- FILTER RENTANG WAKTU -->
<div class="filter-container">
    <form id="filterForm" style="display: flex; gap: 1.5rem; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group">
            <label for="start_date">Tanggal Mulai (Start Date)</label>
            <input type="date" id="start_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="end_date">Tanggal Akhir (End Date)</label>
            <input type="date" id="end_date" class="form-control" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn-primary" style="height: 45px; padding: 0 1.5rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 700;">
                <i class="ph ph-funnel" style="font-size: 1.1rem;"></i> Filter Data
            </button>
        </div>
    </form>
</div>

<!-- EXECUTIVE SUMMARY -->
<div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 2rem;">
    <div class="status-card" style="border-top: 4px solid var(--accent-green);">
        <div class="status-label">Total Energi PV</div>
        <div class="status-val" id="sum-total-energi" style="color: var(--accent-green);">0.00 <span style="font-size: 1rem; color: var(--text-secondary); font-weight: 700;">kWh</span></div>
    </div>
    <div class="status-card" style="border-top: 4px solid var(--accent-blue);">
        <div class="status-label">Rata-rata Harian</div>
        <div class="status-val" id="sum-avg-energi" style="color: var(--accent-blue);">0.00 <span style="font-size: 1rem; color: var(--text-secondary); font-weight: 700;">kWh/hari</span></div>
    </div>
    <div class="status-card" style="border-top: 4px solid var(--accent-yellow);">
        <div class="status-label">Rata-rata PSH</div>
        <div class="status-val" id="sum-avg-psh" style="color: var(--accent-yellow);">0.00 <span style="font-size: 1rem; color: var(--text-secondary); font-weight: 700;">Jam</span></div>
    </div>
    <div class="status-card" style="border-top: 4px solid var(--accent-red);">
        <div class="status-label">Rata-rata Error (MAPE)</div>
        <div class="status-val" id="sum-avg-mape" style="color: var(--accent-red);">0.00 <span style="font-size: 1rem; font-weight: 700;">%</span></div>
    </div>
</div>

<!-- BAR CHART KOMPARATIF -->
<div class="chart-container">
    <h3 style="margin-bottom: 1.5rem; color: var(--text-primary); font-size: 1.05rem; font-weight: 800;"><i class="ph ph-chart-bar"></i> Perbandingan Energi Aktual vs Prediksi DES</h3>
    <div style="position: relative; height: 350px; width: 100%;">
        <canvas id="reportChart"></canvas>
    </div>
</div>

<!-- TABEL REKAPITULASI -->
<div class="table-container">
    <h3 style="margin-bottom: 1.5rem; color: var(--text-primary); font-size: 1.05rem; font-weight: 800;"><i class="ph ph-table"></i> Rincian Data Harian</h3>
    <table class="report-table" id="reportTable">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Energi Aktual (kWh)</th>
                <th>Prediksi DES (kWh)</th>
                <th>PSH (Jam)</th>
                <th>Selisih Error (kWh)</th>
                <th>Error (%)</th>
            </tr>
        </thead>
        <tbody>
            <!-- Diisi lewat JS -->
        </tbody>
    </table>
</div>

@endsection

@push('extra_js')
<script>
    let reportChart;

    // Set Default Tanggal (30 Hari Terakhir)
    function setDefaultDates() {
        const end = new Date();
        const start = new Date();
        start.setDate(end.getDate() - 30);

        document.getElementById('end_date').value = end.toISOString().split('T')[0];
        document.getElementById('start_date').value = start.toISOString().split('T')[0];
    }

    function initChart() {
        const ctx = document.getElementById('reportChart').getContext('2d');
        reportChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Energi Aktual (kWh)',
                        backgroundColor: '#2563eb',
                        data: []
                    },
                    {
                        label: 'Prediksi DES (kWh)',
                        backgroundColor: '#d97706',
                        data: []
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { color: 'rgba(0, 0, 0, 0.05)' }, ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans', weight: '600' } } },
                    y: { grid: { color: 'rgba(0, 0, 0, 0.06)' }, ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans', weight: '600' } } }
                },
                plugins: {
                    legend: { labels: { color: '#0f172a', font: { family: 'Plus Jakarta Sans', weight: '700' } } }
                }
            }
        });
    }

    async function fetchReportData(startDate, endDate) {
        try {
            const response = await fetch(`{{ url('api/v1/report') }}?start_date=${startDate}&end_date=${endDate}`);
            const result = await response.json();

            // Update Subtitle
            document.getElementById('report-subtitle').textContent = `Rekapitulasi data dari ${startDate} hingga ${endDate}`;

            // Update Summary
            document.getElementById('sum-total-energi').innerHTML = `${result.summary.total_energi.toFixed(2)} <span style="font-size: 1rem; color: var(--text-secondary); font-weight: 700;">kWh</span>`;
            document.getElementById('sum-avg-energi').innerHTML = `${result.summary.avg_energi.toFixed(2)} <span style="font-size: 1rem; color: var(--text-secondary); font-weight: 700;">kWh/hari</span>`;
            document.getElementById('sum-avg-psh').innerHTML = `${result.summary.avg_psh.toFixed(2)} <span style="font-size: 1rem; color: var(--text-secondary); font-weight: 700;">Jam</span>`;
            document.getElementById('sum-avg-mape').innerHTML = `${result.summary.avg_mape.toFixed(2)} <span style="font-size: 1rem; font-weight: 700;">%</span>`;

            // Update Chart
            const labels = result.data.map(d => d.tanggal);
            const aktual = result.data.map(d => d.aktual);
            const prediksi = result.data.map(d => d.prediksi);

            reportChart.data.labels = labels;
            reportChart.data.datasets[0].data = aktual;
            reportChart.data.datasets[1].data = prediksi;
            reportChart.update();

            // Update Table
            const tbody = document.querySelector('#reportTable tbody');
            tbody.innerHTML = '';
            
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; font-weight: 600;">Tidak ada data pada rentang tanggal tersebut.</td></tr>';
                return;
            }

            result.data.forEach(d => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-weight: 600;">${d.tanggal}</td>
                    <td style="color: #2563eb; font-weight: 700;">${d.aktual.toFixed(2)}</td>
                    <td style="color: #d97706; font-weight: 700;">${d.prediksi.toFixed(2)}</td>
                    <td style="font-weight: 600;">${d.psh.toFixed(2)}</td>
                    <td style="color: #dc2626; font-weight: 700;">${d.error_abs.toFixed(2)}</td>
                    <td style="font-weight: 700;">${d.error_pct.toFixed(2)}%</td>
                `;
                tbody.appendChild(tr);
            });

        } catch (error) {
            console.error("Gagal mengambil data laporan", error);
            alert("Gagal memuat data laporan dari server.");
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        setDefaultDates();
        initChart();
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        fetchReportData(startDate, endDate);

        document.getElementById('filterForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const start = document.getElementById('start_date').value;
            const end = document.getElementById('end_date').value;
            fetchReportData(start, end);
        });
    });
</script>
@endpush
