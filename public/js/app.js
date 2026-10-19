const UPDATE_INTERVAL = 3000;
const OFFLINE_TIMEOUT = 30000;

const formatNum = (num) => parseFloat(num).toFixed(2);
const formatDate = (dateString) => {
    const d = new Date(dateString);
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

function setOfflineState(message) {
    const statusContainer = document.querySelector('.status-container');
    if(statusContainer) {
        statusContainer.classList.replace('status-online', 'status-offline') || statusContainer.classList.add('status-offline');
    }
    if(document.getElementById('status-text')) {
        document.getElementById('status-text').textContent = message;
        document.getElementById('status-text').style.color = '#ef4444'; // Red
    }
    if(document.getElementById('status-dot')) {
        document.getElementById('status-dot').style.backgroundColor = '#ef4444';
    }
    if(document.getElementById('system-mode')) {
        document.getElementById('system-mode').textContent = 'Tidak Diketahui';
        document.getElementById('system-mode').className = 'status-val';
    }

    const resetIds = [
        'val-energy-today', 'val-energy-month', 'val-energy-total',
        'val-battery-voltage', 'val-battery-capacity', 'val-pv-power', 
        'val-load-percent'
    ];
    
    resetIds.forEach(id => {
        if(document.getElementById(id)) document.getElementById(id).textContent = '0.00';
    });
}

function updateDashboard(data) {
    if (!data || data.message) return;

    const dataTime = new Date(data.created_at || data.timestamp);
    const diff = new Date() - dataTime;

    if (diff > OFFLINE_TIMEOUT) {
        setOfflineState('Offline');
        if(document.getElementById('last-update')) {
            document.getElementById('last-update').textContent = `${formatDate(dataTime)}`;
        }
        return;
    }

    // Set Online Status
    const statusContainer = document.querySelector('.status-container');
    if(statusContainer) {
        statusContainer.classList.replace('status-offline', 'status-online') || statusContainer.classList.add('status-online');
    }
    if(document.getElementById('status-text')) {
        document.getElementById('status-text').textContent = 'Online';
        document.getElementById('status-text').style.color = '#10b981'; // Green
    }
    if(document.getElementById('status-dot')) {
        document.getElementById('status-dot').style.backgroundColor = '#10b981';
    }
    if(document.getElementById('last-update')) {
        document.getElementById('last-update').textContent = `${formatDate(dataTime)}`;
    }

    // Mode Detection
    let modeText = 'Battery Mode';
    let modeClass = 'mode-battery';
    if (parseFloat(data.pv_power) > 0) {
        modeText = 'PV Mode';
        modeClass = 'mode-pv';
    } else if (parseFloat(data.ac_charge_power) > 0) {
        modeText = 'AC Charge Mode';
        modeClass = 'mode-ac';
    }

    if(document.getElementById('system-mode')) {
        document.getElementById('system-mode').textContent = modeText;
        document.getElementById('system-mode').className = `mini-status-value ${modeClass}`;
    }

    // Energy Summary Parameters (menggunakan data mentah payload mqtt)
    if(document.getElementById('val-energy-today') && data.energytoday) {
        document.getElementById('val-energy-today').textContent = formatNum(data.energytoday);
    }
    if(document.getElementById('val-energy-month') && data.energymonth) {
        document.getElementById('val-energy-month').textContent = formatNum(data.energymonth);
    }
    if(document.getElementById('val-energy-total') && data.energytotal) {
        document.getElementById('val-energy-total').textContent = formatNum(data.energytotal);
    }

    // Monitoring Singkat
    if(document.getElementById('val-pv-power')) document.getElementById('val-pv-power').textContent = formatNum(data.pvpower || data.pv_power);
    if(document.getElementById('val-battery-voltage')) document.getElementById('val-battery-voltage').textContent = formatNum(data.batvolt || data.battery_voltage);
    if(document.getElementById('val-battery-capacity')) document.getElementById('val-battery-capacity').textContent = Math.round(data.batcap || data.battery_capacity_percent);
    if(document.getElementById('val-load-percent')) document.getElementById('val-load-percent').textContent = Math.round(data.loadpercen || data.inverter_load_percent);
}

async function fetchLatestData() {
    try {
        const timestamp = new Date().getTime();
        const response = await fetch(`/api/v1/latest?_t=${timestamp}`);
        if (response.ok) {
            const data = await response.json();
            updateDashboard(data);
        } else if (response.status === 404) {
            setOfflineState('Offline');
            if(document.getElementById('last-update')) {
                document.getElementById('last-update').textContent = '-';
            }
        }
    } catch (error) {
        setOfflineState('Server Error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Jalankan segera dan interval dengan aman (mencegah overlapping)
    if(document.getElementById('val-battery-voltage')) {
        async function startPolling() {
            await fetchLatestData();
            setTimeout(startPolling, UPDATE_INTERVAL);
        }
        startPolling();
    }
});
