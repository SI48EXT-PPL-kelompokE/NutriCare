# Panduan UI NutriCare

## Pengantar
Dokumentasi ini menjelaskan cara menggunakan template layout dan komponen UI NutriCare yang telah dibuat berdasarkan prototipe Figma.

## Struktur Layout

### Layout Tersedia
1. **`layouts.app`** - Layout utama untuk pengguna yang sudah login
2. **`layouts.guest`** - Layout untuk halaman login/register
3. **`layouts.print`** - Layout minimal untuk cetak dokumen

### Menggunakan Layout App

```blade
@extends('layouts.app')

@section('title', 'Judul Halaman')

@section('breadcrumb')
    <span>Modul</span>
    <span>/</span>
    <span>Sub Modul</span>
    <span>/</span>
    <span class="text-neutral-700 font-semibold">Halaman Aktif</span>
@endsection

@section('page_title', 'Judul Halaman')
@section('page_subtitle', 'Deskripsi singkat halaman ini')

@section('actions')
    <button class="btn-primary">
        <x-heroicon-o-plus class="w-4 h-4" />
        Tambah Data
    </button>
@endsection

@section('content')
    <!-- Konten halaman Anda di sini -->
@endsection

@push('scripts')
    <script>
        // Script khusus halaman ini
    </script>
@endpush
```

### Menggunakan Layout Guest

```blade
@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6">Login</h1>
        <!-- Form login -->
    </div>
@endsection
```

## Komponen UI

### 1. Stat Card (KPI Card)

Menampilkan metrik/statistik dengan ikon.

```blade
<x-stat-card 
    label="Total Stok PMT Tersedia"
    value="1.420"
    unit="paket"
    icon="archive-box"
    tone="default"
/>

<x-stat-card 
    label="Mendekati Kedaluwarsa (< 30 Hari)"
    value="150"
    unit="paket"
    icon="exclamation-triangle"
    tone="warning"
/>

<x-stat-card 
    label="Tersalurkan Bulan Ini (Okt 2026)"
    value="850"
    unit="paket"
    icon="check-circle"
    tone="success"
/>
```

**Props:**
- `label` - Label deskripsi
- `value` - Nilai angka utama
- `unit` - Satuan (opsional)
- `icon` - Nama Heroicon (tanpa prefix)
- `tone` - Varian warna: `default`, `success`, `warning`, `danger`, `info`

### 2. Status Pill

Menampilkan status dengan warna dan dot indicator.

```blade
<x-status-pill tone="warning">
    Prioritas FEFO
</x-status-pill>

<x-status-pill tone="info">
    Antrean Berikutnya
</x-status-pill>

<x-status-pill tone="success">
    Stok Aman
</x-status-pill>
```

**Tones:**
- `success` - Hijau (Stok Aman, Approved)
- `warning` - Orange (Prioritas, Pending)
- `info` - Biru (Informasi, Processing)
- `danger` - Merah (Kritis, Rejected)
- `neutral` - Abu-abu (Default)

### 3. Code Chip

Untuk menampilkan kode/nomor batch dengan style monospace.

```blade
<x-code-chip>BTC-20260810</x-code-chip>
```

### 4. Tabs

Navigasi tab untuk sub-halaman dalam satu modul.

```blade
<x-tabs>
    <x-tab 
        :active="request()->routeIs('pmt.stok.*')"
        href="{{ route('pmt.stok.index') }}"
        icon="archive-box"
    >
        Stok Gudang
    </x-tab>
    
    <x-tab 
        :active="request()->routeIs('pmt.paket.*')"
        href="{{ route('pmt.paket.index') }}"
        icon="cube"
    >
        Master Paket
    </x-tab>
    
    <x-tab 
        :active="request()->routeIs('pmt.distribusi.*')"
        href="{{ route('pmt.distribusi.index') }}"
        icon="document-text"
    >
        Riwayat Distribusi
    </x-tab>
</x-tabs>
```

### 5. Card & Table Card

#### Basic Card
```blade
<x-card>
    <h3 class="font-semibold text-lg mb-4">Judul Card</h3>
    <p>Konten card...</p>
</x-card>
```

#### Table Card
```blade
<x-table-card 
    title="Daftar Stok Gudang"
    icon="list-bullet"
>
    <x-slot:meta>
        <span class="flex items-center gap-2 text-xs text-neutral-500">
            <span class="w-2 h-2 rounded-full bg-success-500"></span>
            Terakhir diperbarui: 24 Okt 2026, 09:15 WIB
        </span>
    </x-slot:meta>
    
    <x-data-table>
        <x-slot:header>
            <th>Nama Paket PMT</th>
            <th>Nomor Batch</th>
            <th>Tanggal Masuk</th>
            <th>Kedaluwarsa (ED)</th>
            <th class="text-right">Sisa Stok</th>
            <th>Status FEFO</th>
            <th>Aksi</th>
        </x-slot:header>
        
        <x-slot:body>
            <tr>
                <td>
                    <div class="font-semibold">PKT-001 Biskuit Balita 6-23 Bln</div>
                    <div class="text-xs text-neutral-500">Standar Kemenkes RI - Rasa Kacang Hijau</div>
                </td>
                <!-- ... -->
            </tr>
        </x-slot:body>
    </x-data-table>
    
    <x-slot:footer>
        <div class="flex items-center justify-between">
            <span class="text-xs text-neutral-500">
                Menampilkan <strong>1-5</strong> dari <strong>5</strong> batch aktif di gudang
            </span>
            <div class="flex gap-2">
                <button class="btn-sm">Sebelumnya</button>
                <button class="btn-primary btn-sm">1</button>
                <button class="btn-sm">Selanjutnya</button>
            </div>
        </div>
    </x-slot:footer>
</x-table-card>
```

### 6. Data Table

Tabel data dengan styling konsisten.

```blade
<x-data-table>
    <x-slot:header>
        <th>Kolom 1</th>
        <th>Kolom 2</th>
        <th class="text-right">Kolom Angka</th>
        <th>Aksi</th>
    </x-slot:header>
    
    <x-slot:body>
        @foreach($items as $item)
            <tr class="{{ $item->is_priority ? 'bg-warning-50' : '' }}">
                <td>{{ $item->name }}</td>
                <td>{{ $item->value }}</td>
                <td class="text-right">{{ number_format($item->qty) }}</td>
                <td>
                    <button class="btn-sm btn-outline-primary">Aksi</button>
                </td>
            </tr>
        @endforeach
    </x-slot:body>
</x-data-table>
```

**Tips:**
- Gunakan `class="text-right"` untuk kolom angka
- Tambahkan `bg-warning-50` pada `<tr>` untuk highlight baris prioritas
- Mobile: otomatis horizontal scroll

### 7. Filter Bar

Bar untuk pencarian dan filter.

```blade
<x-filter-bar>
    <x-search-input 
        name="search" 
        placeholder="Cari nomor batch, nama paket..."
        :value="request('search')"
    />
    
    <select class="form-select">
        <option>Semua Kategori</option>
        <option>Biskuit Balita</option>
        <option>Formula Pemulihan</option>
    </select>
    
    <select class="form-select">
        <option>Status (Semua)</option>
        <option>Stok Aman</option>
        <option>Mendekati ED</option>
    </select>
    
    <x-slot:actions>
        <button class="btn-outline">
            <x-heroicon-o-plus class="w-4 h-4" />
            Tambah Barang
        </button>
        <button class="btn-primary">
            <x-heroicon-o-qr-code class="w-4 h-4" />
            Catat Distribusi
        </button>
    </x-slot:actions>
</x-filter-bar>
```

### 8. Buttons

#### Button Classes
```blade
<!-- Primary Button (Teal) -->
<button class="btn-primary">
    <x-heroicon-o-plus class="w-4 h-4" />
    Simpan
</button>

<!-- Outline Button -->
<button class="btn-outline">
    Batal
</button>

<!-- Outline Primary (Teal Border) -->
<button class="btn-outline-primary">
    <x-heroicon-o-document class="w-4 h-4" />
    Catat Distribusi
</button>

<!-- Danger Button -->
<button class="btn-danger">
    <x-heroicon-o-trash class="w-4 h-4" />
    Hapus
</button>

<!-- Small Size -->
<button class="btn-primary btn-sm">Kecil</button>

<!-- Icon Only -->
<button class="btn-icon">
    <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
</button>
```

### 9. Form Components

```blade
<x-form-group>
    <x-form-label for="nama" required>
        Nama Lengkap
    </x-form-label>
    
    <x-form-input 
        id="nama" 
        name="nama" 
        type="text"
        placeholder="Masukkan nama lengkap"
        :value="old('nama', $user->nama ?? '')"
        required
    />
    
    <x-form-error name="nama" />
</x-form-group>

<x-form-group>
    <x-form-label for="kategori">
        Kategori
    </x-form-label>
    
    <x-form-select id="kategori" name="kategori">
        <option value="">Pilih kategori</option>
        <option value="1">Kategori 1</option>
        <option value="2">Kategori 2</option>
    </x-form-select>
</x-form-group>

<x-form-group>
    <x-form-label for="keterangan">
        Keterangan
    </x-form-label>
    
    <x-form-textarea 
        id="keterangan" 
        name="keterangan" 
        rows="4"
        placeholder="Masukkan keterangan..."
    />
</x-form-group>

<x-form-group>
    <x-form-checkbox id="setuju" name="setuju" value="1">
        Saya setuju dengan syarat dan ketentuan
    </x-form-checkbox>
</x-form-group>
```

### 10. Alert & Flash Messages

```blade
<x-alert tone="success">
    Data berhasil disimpan!
</x-alert>

<x-alert tone="warning">
    Perhatian: Stok hampir habis
</x-alert>

<x-alert tone="danger">
    Terjadi kesalahan saat menyimpan data
</x-alert>

<x-alert tone="info">
    Informasi: Sistem akan maintenance besok
</x-alert>
```

Flash messages otomatis tampil jika ada session:
```php
// Di controller
return redirect()->back()->with('success', 'Data berhasil disimpan');
return redirect()->back()->with('warning', 'Perhatian!');
return redirect()->back()->with('error', 'Terjadi kesalahan');
```

### 11. Modal

```blade
<div x-data="{ open: false }">
    <button @click="open = true" class="btn-primary">
        Buka Modal
    </button>
    
    <x-modal title="Konfirmasi Hapus" x-show="open" @close="open = false">
        <p>Apakah Anda yakin ingin menghapus data ini?</p>
        
        <x-slot:footer>
            <button @click="open = false" class="btn-outline">
                Batal
            </button>
            <button class="btn-danger">
                Hapus
            </button>
        </x-slot:footer>
    </x-modal>
</div>
```

### 12. Empty State

```blade
<x-empty-state 
    icon="inbox"
    title="Belum ada data"
    description="Silakan tambahkan data baru untuk memulai"
>
    <button class="btn-primary">
        <x-heroicon-o-plus class="w-4 h-4" />
        Tambah Data Pertama
    </button>
</x-empty-state>
```

### 13. Pagination

```blade
<!-- Di controller -->
$items = Model::paginate(10);

<!-- Di view -->
{{ $items->links() }}
```

Pagination otomatis menggunakan style NutriCare (hijau teal, dengan info "Menampilkan X-Y dari Z").

## Token Warna

### Primary (Teal/Hijau)
```
primary-50, primary-100, ..., primary-900
Utama: primary-600 (#0d9488)
```

### Neutral (Gray/Slate)
```
neutral-50, neutral-100, ..., neutral-900
Background: neutral-50 (#f8fafc)
Border: neutral-200 (#e2e8f0)
Text: neutral-800 (#1e293b)
```

### Status Colors
```
success-* (hijau)
warning-* (orange)
danger-* (merah)
info-* (biru)
```

## Breakpoints Responsif

```
sm: 640px   - Tablet kecil
md: 768px   - Tablet
lg: 1024px  - Desktop
xl: 1280px  - Desktop besar
2xl: 1536px - Wide screen
```

### Utility Classes Responsif
```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <!-- 1 kolom di mobile, 2 di tablet, 3 di desktop -->
</div>

<div class="hidden lg:block">
    <!-- Hanya tampil di desktop -->
</div>

<div class="block lg:hidden">
    <!-- Hanya tampil di mobile/tablet -->
</div>
```

## Menambah Menu Navigasi

Edit file `config/navigation.php`:

```php
return [
    [
        'label' => 'Dashboard',
        'route' => 'dashboard',
        'icon' => 'view-grid',
        'roles' => ['admin', 'nakes', 'kader'],
    ],
    [
        'label' => 'Data Balita',
        'route' => 'balita.index',
        'icon' => 'users',
        'roles' => ['admin', 'nakes', 'kader'],
    ],
    // ... tambahkan menu lain
];
```

**Field:**
- `label` - Teks menu
- `route` - Route name Laravel
- `icon` - Nama Heroicon (outline, tanpa prefix `o-`)
- `roles` - Array role yang boleh akses (admin, nakes, kader, warga)

## Best Practices

### 1. Tanpa CDN
Semua aset (font, ikon, CSS, JS) harus melalui npm/Composer dan Vite. Jangan gunakan CDN.

### 2. Aksesibilitas
- Kontras minimal WCAG AA
- Focus states terlihat (ring-2)
- Area sentuh minimal 44x44px
- Status tidak hanya warna (ada ikon/teks)

### 3. Responsive
- Test di 360px (mobile kecil)
- No horizontal scroll
- Touch-friendly (button size, spacing)

### 4. Konsistensi
- Gunakan komponen yang tersedia
- Ikuti token warna
- Spacing konsisten (4px increment)

## Contoh Halaman Lengkap

```blade
@extends('layouts.app')

@section('title', 'Stok Gudang PMT')

@section('breadcrumb')
    <span>Logistik PMT</span>
    <span>/</span>
    <span class="text-neutral-700 font-semibold">Stok Gudang</span>
@endsection

@section('page_title', 'Logistik PMT')
@section('page_subtitle', 'Puskesmas Kecamatan Sukajadi - Gudang Farmasi & Distribusi Posyandu')

@section('content')
    <!-- Tabs -->
    <x-tabs>
        <x-tab :active="true" href="#" icon="archive-box">
            Stok Gudang
        </x-tab>
        <x-tab :active="false" href="#" icon="cube">
            Master Paket
        </x-tab>
        <x-tab :active="false" href="#" icon="document-text">
            Riwayat Distribusi
        </x-tab>
    </x-tabs>
    
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-stat-card 
            label="Total Stok PMT Tersedia"
            value="1.420"
            unit="paket"
            icon="archive-box"
        />
        <x-stat-card 
            label="Mendekati Kedaluwarsa (< 30 Hari)"
            value="150"
            unit="paket"
            icon="exclamation-triangle"
            tone="warning"
        />
        <x-stat-card 
            label="Tersalurkan Bulan Ini"
            value="850"
            unit="paket"
            icon="check-circle"
            tone="success"
        />
    </div>
    
    <!-- Filter Bar -->
    <x-filter-bar>
        <x-search-input 
            name="search" 
            placeholder="Cari nomor batch, nama paket..."
        />
        <!-- ... filters ... -->
    </x-filter-bar>
    
    <!-- Table -->
    <x-table-card title="Daftar Stok Gudang" icon="list-bullet">
        <!-- ... table content ... -->
    </x-table-card>
@endsection
```

## Halaman Pratinjau Development

Untuk melihat semua komponen:
- `/dev/layout` - Galeri komponen
- `/dev/layout/logistik-pmt` - Replika halaman mockup

**Catatan**: Route `/dev/*` hanya tersedia di environment `local`.

## Penyelarasan dengan Kode Tim

Layout dan komponen ini dirancang untuk kompatibel dengan kode yang sudah dibuat oleh:
- **Permata** (Artikel & Konseling)
- **Adhani** (Kependudukan)
- **Fadilyas** (PMT Paket)
- **Syifa** (Jadwal Posyandu)

Jika ada perbedaan atau konflik, prioritaskan konsistensi dengan prototipe Figma.

## Referensi

- Mockup: `docs/mockup/README.md`
- Token warna lengkap: `resources/css/app.css` (bagian `@theme`)
- Heroicons: https://heroicons.com
