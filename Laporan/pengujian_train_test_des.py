"""
Script Pengujian 80:20 Train-Test Split Metode Double Exponential Smoothing (Holt's Method)
Dataset: Laporan/energi_harians.csv (1.049 Hari)
Penulis: Muhlisin (NIM 2218055) - ITN Malang
"""

import pandas as pd
import numpy as np
import os

try:
    import matplotlib.pyplot as plt
    HAS_MATPLOTLIB = True
except ImportError:
    HAS_MATPLOTLIB = False

# 1. READ DATASET
csv_path = os.path.join(os.path.dirname(__file__), 'energi_harians.csv') if '__file__' in locals() else 'Laporan/energi_harians.csv'
if not os.path.exists(csv_path) and os.path.exists('energi_harians.csv'):
    csv_path = 'energi_harians.csv'

df = pd.read_csv(csv_path)
df['tanggal'] = pd.to_datetime(df['tanggal'])
df = df.sort_values('tanggal').reset_index(drop=True)

total_rows = len(df)
print("=" * 70)
print(f"  PENGUJIAN TRAIN-TEST SPLIT (80:20) DOUBLE EXPONENTIAL SMOOTHING  ")
print("=" * 70)
print(f"Total Dataset: {total_rows} Baris Data Energi PV Harian")

# 2. SPLIT DATASET 80% TRAIN : 20% TEST (Chronological Split)
train_size = int(total_rows * 0.8)
train_df = df.iloc[:train_size].copy().reset_index(drop=True)
test_df = df.iloc[train_size:].copy().reset_index(drop=True)

train_start = train_df['tanggal'].iloc[0].strftime('%Y-%m-%d')
train_end = train_df['tanggal'].iloc[-1].strftime('%Y-%m-%d')
test_start = test_df['tanggal'].iloc[0].strftime('%Y-%m-%d')
test_end = test_df['tanggal'].iloc[-1].strftime('%Y-%m-%d')

print(f"\n1. Pembagian Dataset (80:20):")
print(f"   - Data Latih (Training 80%) : {len(train_df)} baris ({train_start} s/d {train_end})")
print(f"   - Data Uji   (Testing  20%) : {len(test_df)} baris ({test_start} s/d {test_end})")


# 3. FUNGSI HOLT'S DOUBLE EXPONENTIAL SMOOTHING
def run_des(data, alpha, beta):
    n = len(data)
    if n < 2:
        return 0, []
    
    L = data[0]
    T = data[1] - data[0]
    
    forecasts = [L] # t=0
    sum_ape = 0
    count_ape = 0
    
    for i in range(1, n):
        actual = data[i]
        forecast = L + T
        forecasts.append(forecast)
        
        if actual > 0:
            ape = abs((actual - forecast) / actual) * 100
            sum_ape += ape
            count_ape += 1
            
        last_L = L
        L = alpha * actual + (1 - alpha) * (last_L + T)
        T = beta * (L - last_L) + (1 - beta) * T
        
    mape = sum_ape / count_ape if count_ape > 0 else 0
    return mape, forecasts


# 4. GRID SEARCH (81 KOMBINASI) HANYA PADA DATA LATIH (TRAIN SET)
print("\n2. Melakukan Grid Search 81 Kombinasi pada Data Latih (80%)...")

grid_results = []
best_alpha = None
best_beta = None
lowest_train_mape = float('inf')
no = 1

train_vals = train_df['energi_pv_harian'].values

for a in range(1, 10):
    alpha = round(a * 0.1, 1)
    for b in range(1, 10):
        beta = round(b * 0.1, 1)
        mape_train, _ = run_des(train_vals, alpha, beta)
        
        grid_results.append({
            'No': no,
            'Alpha': alpha,
            'Beta': beta,
            'MAPE_Train_%': round(mape_train, 2)
        })
        
        if mape_train < lowest_train_mape:
            lowest_train_mape = mape_train
            best_alpha = alpha
            best_beta = beta
            
        no += 1

grid_results_df = pd.DataFrame(grid_results)

print(f"   >> Parameter Optimal Terpilih : Alpha = {best_alpha}, Beta = {best_beta}")
print(f"   >> MAPE Data Latih (Train MAPE) : {lowest_train_mape:.2f}%")


# 5. EVALUASI MODEL PADA DATA UJI (TEST SET - 20%)
print("\n3. Mengevaluasi Model Optimal pada Data Uji (Testing Set - 20%)...")
full_vals = df['energi_pv_harian'].values
_, full_forecasts = run_des(full_vals, best_alpha, best_beta)

test_actuals = full_vals[train_size:]
test_forecasts = full_forecasts[train_size:]

test_apes = [abs((act - fc) / act) * 100 for act, fc in zip(test_actuals, test_forecasts) if act > 0]
test_mape = np.mean(test_apes)

print(f"   >> MAPE Data Uji (Out-of-Sample Test MAPE) : {test_mape:.2f}%")

print("\n" + "=" * 70)
print("  RINGKASAN HASIL EVALUASI TRAIN-TEST (UNTUK DRAFT JURNAL / SKRIPSI)  ")
print("=" * 70)
print(f"  - Parameter Optimal (Alpha, Beta) : Alpha = {best_alpha}, Beta = {best_beta}")
print(f"  - Data Latih (80% / 839 data)  : MAPE = {lowest_train_mape:.2f}%")
print(f"  - Data Uji   (20% / 210 data)  : MAPE = {test_mape:.2f}%")
print("=" * 70)

# Simpan Ringkasan 81 Kombinasi ke CSV
output_csv = 'Laporan/grid_search_81_kombinasi_train.csv' if os.path.exists('Laporan') else 'grid_search_81_kombinasi_train.csv'
grid_results_df.to_csv(output_csv, index=False)
print(f"\n[INFO] Tabel 81 Kombinasi Grid Search disimpan di: {output_csv}")

# 6. VISUALISASI GRAFIK EVALUASI
if HAS_MATPLOTLIB:
    plt.figure(figsize=(14, 6))
    plt.plot(df['tanggal'][:train_size], train_vals, label='Data Latih (Aktual 80%)', color='#2563eb', alpha=0.7)
    plt.plot(df['tanggal'][train_size:], test_actuals, label='Data Uji (Aktual 20%)', color='#059669', alpha=0.8)
    plt.plot(df['tanggal'], full_forecasts, label=f'Forecast DES (α={best_alpha}, β={best_beta})', color='#d97706', linestyle='--', alpha=0.9)

    plt.axvline(x=df['tanggal'].iloc[train_size], color='red', linestyle=':', linewidth=2, label='Batas Split Train/Test (80:20)')

    plt.title('Evaluasi Model Double Exponential Smoothing (80% Train : 20% Test)', fontsize=14, fontweight='bold')
    plt.xlabel('Tanggal', fontsize=11)
    plt.ylabel('Energi PV Harian (kWh)', fontsize=11)
    plt.legend(loc='upper right')
    plt.grid(True, linestyle='--', alpha=0.5)

    plot_path = 'Laporan/grafik_train_test_split.png' if os.path.exists('Laporan') else 'grafik_train_test_split.png'
    plt.tight_layout()
    plt.savefig(plot_path, dpi=300)
    print(f"[INFO] Grafik visualisasi evaluasi disimpan di: {plot_path}")
    plt.show()
else:
    print("[INFO] Matplotlib belum terinstall. Hasil evaluasi angka dan CSV sudah berhasil dibuat 100%.")
