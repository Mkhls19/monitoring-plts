@extends('layouts.app')

@section('title', 'Data Historis')

@push('extra_css')
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    .filter-container {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
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
    .btn-secondary-custom {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: var(--text-secondary);
        padding: 0.75rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        height: 45px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-secondary-custom:hover {
        background: #f1f5f9;
        color: var(--text-primary);
    }
    .info-badge {
        background: #eff6ff;
        color: var(--accent-blue);
        border-radius: 0.5rem;
        box-shadow: inset 3.5px 0 0 var(--accent-blue);
        padding: 0.85rem 1.25rem;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Custom Toolbar Layout (Flexbox Left & Right) */
    .table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .custom-dropdown-container {
        position: relative;
        display: inline-block;
    }
    .btn-custom-dropdown {
        background: var(--accent-blue);
        color: white;
        border: none;
        border-radius: 0.5rem;
        padding: 0.6rem 1.25rem;
        font-weight: 700;
        font-size: 0.875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
        transition: all 0.2s;
    }
    .btn-custom-dropdown:hover {
        background: #1d4ed8;
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.35);
    }
    .custom-dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15);
        z-index: 1000;
        min-width: 170px;
        padding: 0.4rem;
    }
    .custom-dropdown-menu.show {
        display: block;
        animation: fadeIn 0.15s ease-in-out;
    }
    .custom-dropdown-menu a {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.6rem 0.85rem;
        color: #0f172a;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        border-radius: 0.375rem;
        transition: background 0.15s;
    }
    .custom-dropdown-menu a:hover {
        background: #f1f5f9;
        color: var(--accent-blue);
    }
    .custom-search-container {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--text-secondary);
    }
    .form-control-search {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        padding: 0.55rem 0.85rem;
        border-radius: 0.5rem;
        outline: none;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s;
        width: 220px;
    }
    .form-control-search:focus {
        border-color: var(--accent-blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* DataTables Light Styling */
    .card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
    }
    .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_processing, .dataTables_wrapper .dataTables_paginate {
        color: var(--text-primary);
        margin-bottom: 1rem;
        font-size: 0.9rem;
        font-weight: 600;
    }
    table.dataTable.display tbody tr.odd > .sorting_1, table.dataTable.order-column.stripe tbody tr.odd > .sorting_1 { background-color: #f8fafc; }
    table.dataTable.display tbody tr.even > .sorting_1, table.dataTable.order-column.stripe tbody tr.even > .sorting_1 { background-color: transparent; }
    table.dataTable tbody tr { background-color: transparent; border-bottom: 1px solid #f1f5f9; }
    table.dataTable thead th, table.dataTable thead td { border-bottom: 2px solid #e2e8f0; color: var(--accent-blue); background-color: #f8fafc; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; }
    table.dataTable tbody th, table.dataTable tbody td { padding: 12px 10px; color: #334155; font-weight: 600; }
    .dataTables_wrapper .dataTables_paginate .paginate_button { color: var(--text-primary) !important; border-radius: 0.375rem !important; font-weight: 600 !important; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: var(--accent-blue) !important; color: white !important; border: none !important; }
    
    /* Sembunyikan tombol DataTables asli agar digantikan oleh Custom Toolbar */
    .dt-buttons { display: none !important; }
    .dataTables_filter { display: none !important; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1>Data Historis</h1>
        <p class="subtitle">Catatan data energi PLTS</p>
    </div>
</div>

<!-- FILTER RENTANG WAKTU -->
<div class="filter-container">
    <form id="historyFilterForm" style="display: flex; gap: 1.25rem; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="margin-bottom: 0;">
            <label for="start_date">Tanggal Mulai (Start Date)</label>
            <input type="date" id="start_date" class="form-control" required>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label for="end_date">Tanggal Akhir (End Date)</label>
            <input type="date" id="end_date" class="form-control" required>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <button type="submit" class="btn-primary" style="height: 45px; padding: 0 1.25rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 700;">
                <i class="ph ph-funnel" style="font-size: 1.1rem;"></i> Filter Data
            </button>
            <button type="button" id="btnResetFilter" class="btn-secondary-custom">
                <i class="ph ph-arrow-counter-clockwise" style="font-size: 1.1rem;"></i> Data Hari Ini
            </button>
        </div>
    </form>
</div>

<!-- BADGE INDIKATOR RENTANG WAKTU DATA -->
<div class="info-badge" id="historyInfoBadge">
    <i class="ph ph-calendar-blank" style="font-size: 1.2rem; color: var(--accent-blue);"></i>
    <span id="infoBadgeText">Memuat data sensor...</span>
</div>

<!-- TABEL DATA HISTORIS -->
<div class="card" style="overflow-x: auto;">
    <!-- CUSTOM TOOLBAR (Export Dropdown Kiri & Search Kanan) -->
    <div class="table-toolbar">
        <div class="custom-dropdown-container">
            <button type="button" class="btn-custom-dropdown" id="btnExportDropdown" onclick="toggleExportMenu(event)">
                <i class="ph ph-download-simple" style="font-size: 1.15rem;"></i> Export Data <i class="ph ph-caret-down" style="font-size: 0.85rem; margin-left: 0.2rem;"></i>
            </button>
            <div class="custom-dropdown-menu" id="exportMenu">
                <a href="javascript:void(0)" onclick="triggerExport('excel')"><i class="ph ph-file-xls" style="color: #059669; font-size: 1.1rem;"></i> Export Excel</a>
                <a href="javascript:void(0)" onclick="triggerExport('csv')"><i class="ph ph-file-csv" style="color: #2563eb; font-size: 1.1rem;"></i> Export CSV</a>
                <a href="javascript:void(0)" onclick="triggerExport('pdf')"><i class="ph ph-file-pdf" style="color: #dc2626; font-size: 1.1rem;"></i> Export PDF</a>
            </div>
        </div>
        <div class="custom-search-container">
            <label for="customSearchInput"><i class="ph ph-magnifying-glass" style="font-size: 1.1rem; vertical-align: middle;"></i> Cari:</label>
            <input type="text" id="customSearchInput" class="form-control-search" placeholder="Ketik pencarian...">
        </div>
    </div>

    <table id="historyTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>PV (V)</th>
                <th>PV (A)</th>
                <th>PV (W)</th>
                <th>Bat (V)</th>
                <th>Bat (%)</th>
                <th>AC In (V)</th>
                <th>AC Out (V)</th>
                <th>AC Load (%)</th>
            </tr>
        </thead>
        <tbody>
            <!-- Diisi via AJAX -->
        </tbody>
    </table>
</div>
@endsection

@push('extra_js')
<!-- jQuery & DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<script>
let dataTableInstance;
let isFilteredData = false;
let latestDatasetArray = [];

function formatDateIndo(dateObj) {
    if (!dateObj || isNaN(dateObj.getTime())) return '-';
    return dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function updateInfoBadge(data, isFiltered = false, startDateVal = '', endDateVal = '') {
    const badgeText = document.getElementById('infoBadgeText');
    if (!data || data.length === 0) {
        badgeText.innerHTML = `<strong>Tidak ada data ditemukan</strong> pada rentang waktu tersebut.`;
        return;
    }

    const totalRows = data.length.toLocaleString('id-ID');
    
    // Ambil tanggal paling awal & paling akhir dari array data
    const times = data.map(d => new Date(d.created_at || d.timestamp)).filter(d => !isNaN(d.getTime()));
    let minDateStr = '-';
    let maxDateStr = '-';

    if (times.length > 0) {
        const minTime = new Date(Math.min(...times));
        const maxTime = new Date(Math.max(...times));
        minDateStr = formatDateIndo(minTime);
        maxDateStr = formatDateIndo(maxTime);
    }

    if (isFiltered) {
        badgeText.innerHTML = `Hasil Filter: Menampilkan <strong>${totalRows} baris data</strong> dari rentang <strong>${startDateVal}</strong> s/d <strong>${endDateVal}</strong>`;
    } else {
        if (minDateStr === maxDateStr || maxDateStr === '-') {
            badgeText.innerHTML = `Prioritas Data Terbaru: Menampilkan <strong>${totalRows} baris data</strong> pada tanggal <strong>${minDateStr}</strong>`;
        } else {
            badgeText.innerHTML = `Prioritas Data Terbaru: Menampilkan <strong>${totalRows} baris data</strong> dari <strong>${minDateStr}</strong> s/d <strong>${maxDateStr}</strong>`;
        }
    }
}

// Handler Dropdown Custom Tanpa Layar Meredup/Berkedip
function toggleExportMenu(e) {
    e.stopPropagation();
    $('#exportMenu').toggleClass('show');
}

$(document).on('click', function(e) {
    if (!$(e.target).closest('.custom-dropdown-container').length) {
        $('#exportMenu').removeClass('show');
    }
});

// Pemicu Export Excel, CSV, PDF
function triggerExport(type) {
    $('#exportMenu').removeClass('show');
    if (!dataTableInstance) return;

    if (type === 'excel') dataTableInstance.button('.buttons-excel').trigger();
    else if (type === 'csv') dataTableInstance.button('.buttons-csv').trigger();
    else if (type === 'pdf') dataTableInstance.button('.buttons-pdf').trigger();
}

function loadHistoryData(apiUrl, isFiltered = false, startDateVal = '', endDateVal = '') {
    isFilteredData = isFiltered;

    if (dataTableInstance) {
        dataTableInstance.destroy();
    }

    dataTableInstance = $('#historyTable').DataTable({
        "ajax": {
            "url": apiUrl,
            "dataSrc": function(json) {
                latestDatasetArray = json || [];
                updateInfoBadge(json, isFiltered, startDateVal, endDateVal);
                return json;
            }
        },
        "columns": [
            { "data": "created_at", "render": function(data) { return new Date(data).toLocaleString('id-ID'); } },
            { "data": "pv_voltage", "render": $.fn.dataTable.render.number(',', '.', 2, '') },
            { "data": "pv_charge_current", "render": $.fn.dataTable.render.number(',', '.', 2, '') },
            { 
                "data": "pv_power", 
                "render": function(data) {
                    return `<span style="color: var(--accent-blue); font-weight: 800;">${parseFloat(data || 0).toFixed(2)}</span>`;
                } 
            },
            { "data": "battery_voltage", "render": $.fn.dataTable.render.number(',', '.', 2, '') },
            { "data": "battery_capacity_percent", "render": $.fn.dataTable.render.number(',', '.', 0, '', '%') },
            { "data": "ac_input_voltage", "render": $.fn.dataTable.render.number(',', '.', 2, '') },
            { "data": "ac_output_voltage", "render": $.fn.dataTable.render.number(',', '.', 2, '') },
            { "data": "inverter_load_percent", "render": $.fn.dataTable.render.number(',', '.', 0, '', '%') }
        ],
        "order": [[ 0, "desc" ]],
        "dom": 'Bfrtip',
        "buttons": [
            {
                extend: 'excelHtml5',
                className: 'buttons-excel',
                exportOptions: {
                    rows: function(idx, data, node) {
                        return filterRowsForExport(data);
                    }
                }
            },
            {
                extend: 'csvHtml5',
                className: 'buttons-csv',
                exportOptions: {
                    rows: function(idx, data, node) {
                        return filterRowsForExport(data);
                    }
                }
            },
            {
                extend: 'pdfHtml5',
                className: 'buttons-pdf',
                exportOptions: {
                    rows: function(idx, data, node) {
                        return filterRowsForExport(data);
                    }
                }
            }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        }
    });

    // Reset pencarian custom saat reload
    $('#customSearchInput').val('');
}

// Logika Penyaringan Baris untuk Export (Filter vs Hari Ini saja)
function filterRowsForExport(dataRow) {
    // 1. Jika pengguna menggunakan filter rentang tanggal: Export SELURUH data hasil filter
    if (isFilteredData) {
        return true;
    }

    // 2. Jika TANPA FILTER: Export HANYA data di HARI INI saja
    const rowDate = new Date(dataRow.created_at || dataRow.timestamp);
    if (isNaN(rowDate.getTime())) return true;

    const todayStr = new Date().toISOString().split('T')[0];
    const rowDateStr = rowDate.toISOString().split('T')[0];

    // Jika ada data di hari ini, export baris tersebut
    if (rowDateStr === todayStr) {
        return true;
    }

    // Fallback: Jika di DB belum ada data di hari ini, export data dari tanggal terbaru yang tersedia di dataset
    if (latestDatasetArray && latestDatasetArray.length > 0) {
        const latestRowDate = new Date(latestDatasetArray[0].created_at || latestDatasetArray[0].timestamp);
        if (!isNaN(latestRowDate.getTime())) {
            const latestDateStr = latestRowDate.toISOString().split('T')[0];
            return rowDateStr === latestDateStr;
        }
    }

    return true;
}

$(document).ready(function() {
    // Set default tanggal input ke HARI INI (Today)
    const todayStr = new Date().toISOString().split('T')[0];
    document.getElementById('end_date').value = todayStr;
    document.getElementById('start_date').value = todayStr;

    // Load default (Prioritas Data Hari Ini / Terbaru)
    loadHistoryData("{{ url('api/v1/history') }}");

    // Event Submit Form Filter
    $('#historyFilterForm').on('submit', function(e) {
        e.preventDefault();
        const startVal = $('#start_date').val();
        const endVal = $('#end_date').val();
        const filterUrl = `{{ url('api/v1/history') }}?start_date=${startVal}&end_date=${endVal}`;
        loadHistoryData(filterUrl, true, startVal, endVal);
    });

    // Event Reset Filter ke Data Hari Ini
    $('#btnResetFilter').on('click', function() {
        document.getElementById('end_date').value = todayStr;
        document.getElementById('start_date').value = todayStr;
        loadHistoryData("{{ url('api/v1/history') }}");
    });

    // Custom Live Search Box Input Event
    $('#customSearchInput').on('keyup change clear', function() {
        if (dataTableInstance) {
            dataTableInstance.search(this.value).draw();
        }
    });
});
</script>
@endpush
