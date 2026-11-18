#!/usr/bin/env python3
"""
Generator kasus uji z-score independen (tidak menyalin kode PHP)

Script ini mengimplementasi rumus LMS WHO dari awal untuk menghasilkan
kasus uji yang diverifikasi secara independen dari implementasi PHP.
"""

import csv
import json
import math
from typing import Dict, List, Tuple


def hitung_sd(l: float, m: float, s: float, k: float) -> float:
    """
    Hitung nilai SD pada z-score tertentu
    
    Rumus:
    - L != 0: SD(k) = M * (1 + L*S*k)^(1/L)
    - L == 0: SD(k) = M * exp(S*k)
    """
    if abs(l) > 0.0001:
        return m * pow(1 + l * s * k, 1 / l)
    else:
        return m * math.exp(s * k)


def hitung_z_dari_nilai(y: float, l: float, m: float, s: float, koreksi_ekstrem: bool = False) -> float:
    """
    Hitung z-score dari nilai pengukuran
    
    Rumus LMS:
    - L != 0: Z = ((y/M)^L - 1) / (L*S)
    - L == 0: Z = ln(y/M) / S
    
    Koreksi ekstrem WHO untuk z > 3 atau z < -3:
    - Z > 3: Z = 3 + (y - SD(3)) / (SD(3) - SD(2))
    - Z < -3: Z = -3 + (y - SD(-3)) / (SD(-2) - SD(-3))
    """
    # Rumus LMS standar
    if abs(l) > 0.0001:
        z = (pow(y / m, l) - 1) / (l * s)
    else:
        z = math.log(y / m) / s
    
    # Koreksi nilai ekstrem
    if koreksi_ekstrem:
        if z > 3:
            sd3 = hitung_sd(l, m, s, 3)
            sd2 = hitung_sd(l, m, s, 2)
            z = 3 + (y - sd3) / (sd3 - sd2)
        elif z < -3:
            sd_minus3 = hitung_sd(l, m, s, -3)
            sd_minus2 = hitung_sd(l, m, s, -2)
            z = -3 + (y - sd_minus3) / (sd_minus2 - sd_minus3)
    
    # Bulatkan 2 desimal
    result = round(z, 2)
    
    # Normalkan "-0.00" menjadi "0.00"
    if result == -0.0:
        result = 0.0
    
    return result


def hitung_nilai_dari_z(z: float, l: float, m: float, s: float) -> float:
    """
    Hitung nilai pengukuran dari z-score target (kebalikan rumus LMS)
    
    Untuk menghasilkan kasus uji, kita perlu nilai y yang menghasilkan z tertentu
    """
    if abs(l) > 0.0001:
        # Z = ((y/M)^L - 1) / (L*S)
        # L*S*Z = (y/M)^L - 1
        # (y/M)^L = 1 + L*S*Z
        # y/M = (1 + L*S*Z)^(1/L)
        # y = M * (1 + L*S*Z)^(1/L)
        return m * pow(1 + l * s * z, 1 / l)
    else:
        # Z = ln(y/M) / S
        # S*Z = ln(y/M)
        # y/M = exp(S*Z)
        # y = M * exp(S*Z)
        return m * math.exp(s * z)


def load_lms_data(csv_path: str) -> List[Dict]:
    """Muat data LMS dari CSV"""
    data = []
    with open(csv_path, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            data.append({
                'indikator': row['indikator'],
                'jenis_kelamin': row['jenis_kelamin'],
                'acuan_nilai': float(row['umur_bulan']),
                'l': float(row['l']),
                'm': float(row['m']),
                's': float(row['s']),
            })
    return data


def generate_test_cases() -> List[Dict]:
    """Generate kasus uji z-score"""
    
    # Load data LMS BB_U
    csv_path = '../../database/seeders/data/standard_lms_bb_u.csv'
    lms_data = load_lms_data(csv_path)
    
    # Umur yang akan diuji (bulan)
    test_ages = [0, 1, 6, 12, 24, 36, 48, 60]
    
    # Z-score target untuk diuji
    z_targets = [-4, -3, -2, -1, 0, 1, 2, 3, 4]
    
    test_cases = []
    
    for jenis_kelamin in ['L', 'P']:
        for umur in test_ages:
            # Cari baris LMS yang sesuai
            lms_row = next((r for r in lms_data 
                           if r['jenis_kelamin'] == jenis_kelamin 
                           and r['acuan_nilai'] == umur), None)
            
            if not lms_row:
                continue
            
            l = lms_row['l']
            m = lms_row['m']
            s = lms_row['s']
            
            for z_target in z_targets:
                # Hitung nilai yang menghasilkan z_target
                nilai = hitung_nilai_dari_z(z_target, l, m, s)
                
                # Verifikasi dengan menghitung z dari nilai (tanpa koreksi)
                z_hasil_tanpa_koreksi = hitung_z_dari_nilai(nilai, l, m, s, koreksi_ekstrem=False)
                
                # Hitung dengan koreksi ekstrem
                z_hasil_dengan_koreksi = hitung_z_dari_nilai(nilai, l, m, s, koreksi_ekstrem=True)
                
                # Untuk z dalam rentang normal (-3 sampai 3), hasilnya harus sama
                # Untuk z ekstrem (< -3 atau > 3), koreksi akan mengubah hasil
                
                test_cases.append({
                    'deskripsi': f"{jenis_kelamin}, {umur} bulan, target z={z_target}",
                    'jenis_kelamin': jenis_kelamin,
                    'umur_bulan': umur,
                    'berat_badan': round(nilai, 2),
                    'l': l,
                    'm': m,
                    's': s,
                    'z_target': z_target,
                    'z_expected_tanpa_koreksi': round(z_hasil_tanpa_koreksi, 2),
                    'z_expected_dengan_koreksi': round(z_hasil_dengan_koreksi, 2),
                    'is_ekstrem': abs(z_target) > 3,
                })
    
    return test_cases


def main():
    """Generate dan simpan fixture JSON"""
    print("Generating z-score test cases...")
    
    test_cases = generate_test_cases()
    
    output_path = 'zscore_expected.json'
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(test_cases, f, indent=2, ensure_ascii=False)
    
    print(f"✓ Generated {len(test_cases)} test cases")
    print(f"✓ Saved to {output_path}")
    
    # Tampilkan beberapa contoh
    print("\nContoh kasus uji:")
    for case in test_cases[:5]:
        print(f"  {case['deskripsi']}: nilai={case['berat_badan']} kg → z={case['z_expected_dengan_koreksi']}")


if __name__ == '__main__':
    main()
