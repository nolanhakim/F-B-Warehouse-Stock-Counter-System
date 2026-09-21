# Design System & UI/UX Guidelines
**Project:** F&B Warehouse Management & Stock Counter System  
**Design Theme:** Industrial Utility (Functional Minimalist & Data-Dense)  
**Target Devices:** Rugged Tablets (8"-11"), Smartphones (Warehouse Floor), & Desktop Workstations (Supervisor Office)

---

## 1. Filosofi & Pendekatan Desain
Sistem ini dirancang untuk lingkungan kerja gudang F&B yang dinamis, bertempo cepat, dan memiliki kondisi lingkungan spesifik (area penyimpanan dingin/chiller, pencahayaan variatif, staf menggunakan sarung tangan tipis). 

* **Utility First:** Mengeliminasi dekorasi visual yang tidak perlu. Semua komponen visual memiliki fungsi operasional yang jelas.
* **Dual Interaction Mode:**
  * *Desktop View:* Tata letak rapat (*compact/high-density*) dengan tabel komprehensif, multi-filter, dan tombol pintas keyboard (*keyboard shortcuts*) untuk supervisor.
  * *Tablet/Mobile View:* Elemen sentuh besar (minimal 44px–48px), navigasi ibu jari (*thumb-friendly*), dan form input terisolasi untuk staf opname.
* **Scanability & Tabular Precision:** Angka, SKU, dan status wajib dapat dipindai dalam hitungan detik tanpa kebingungan persepsi.

---

## 2. Palet Warna (Color Palette)
Mengadopsi skema warna netral berbasis *Slate/Cool Gray* dengan aksen semantik yang sangat tegas untuk membedakan status risiko bahan baku F&B.

### 2.1. Neutrals (Surface & Typography)
* **Background Canvas:** `#0F172A` (Dark Mode default untuk tablet gudang/chiller) / `#F8FAFC` (Light Mode untuk PC kantor).
* **Card & Surface:** `#1E293B` (Dark) / `#FFFFFF` (Light).
* **Border & Dividers:** `#334155` (Dark) / `#E2E8F0` (Light).
* **Text Primary:** `#F8FAFC` (Dark) / `#0F172A` (Light) - Rasio kontras minimum 7:1.
* **Text Muted / Secondary:** `#94A3B8` (Dark) / `#64748B` (Light).

### 2.2. Primary & Brand Accent
* **Primary Blue:** `#0284C7` (Sky-600) — Tombol aksi utama (Submit Inbound, Simpan Draft, Buka Opname).
* **Primary Hover / Active:** `#0369A1` (Sky-700).

### 2.3. Semantic & Status Indicators (F&B Specific)
* **Danger / Critical Expired / Shortage:**
  * Background: `#FEF2F2` (Light) | Border/Text: `#DC2626`
  * Penggunaan: Barang expired < H-7, selisih fisik kurang (*negative variance*), reject inbound.
* **Warning / Approaching Expired / Low Stock:**
  * Background: `#FFFBEB` (Light) | Border/Text: `#D97706`
  * Penggunaan: Barang mendekati expired (H-30 s/d H-8), stok di bawah *safety stock*.
* **Success / In-Stock / Match:**
  * Background: `#F0FDF4` (Light) | Border/Text: `#16A34A`
  * Penggunaan: Hasil hitung opname akurat (*zero discrepancy*), inbound terverifikasi.
* **Storage Category Badges:**
  * *Dry Goods:* `#E2E8F0` (Slate neutral)
  * *Chilled (0-4°C):* `#E0F2FE` (Ice Blue) | Text: `#0369A1`
  * *Frozen (<-18°C):* `#EDE9FE` (Cool Indigo) | Text: `#6D28D9`
  * *Packaging:* `#FEF3C7` (Warm Amber) | Text: `#B45309`

---

## 3. Tipografi
Kombinasi antara font antarmuka yang bersih dan font *monospace* berjarak tetap untuk seluruh data kuantitatif.

* **UI Font Family:** `Inter`, `Plus Jakarta Sans`, atau `system-ui, -apple-system, sans-serif`.
* **Data & Numeral Font Family:** `JetBrains Mono`, `Roboto Mono`, atau tabular numbers (`font-variant-numeric: tabular-nums`). Wajib diterapkan pada:
  * Angka kuantitas stok & selisih opname.
  * Kode SKU dan Batch ID.
  * Tanggal Expired (format standar ISO: `YYYY-MM-DD`).

### Skala Tipografi
* **Display / Page Header:** 18pt / 24px — Bold (700)
* **Section Title:** 14pt / 18px — SemiBold (600)
* **Body Regular:** 9.5pt / 13px — Regular (400)
* **Table Cell / Data Entry:** 9pt / 12px — Regular / Tabular Medium
* **Badges & Microcopy:** 7.5pt / 10px — SemiBold (600) Caps

---

## 4. Pola Komponen Antarmuka (Component Patterns)

### 4.1. Data Table (Desktop View)
* **Tinggi Baris (Row Height):** 36px–40px (kompak untuk memuat minimal 15 baris per viewport).
* **Zebra Striping:** Alternasi warna latar baris genap/ganjil untuk mempermudah pembacaan horizontal.
* **Sticky Header & Fixed Columns:** Kolom SKU dan Nama Barang tetap terkunci saat tabel digulir secara horizontal.
* **Alignment Rule:**
  * Teks (Nama Barang, Kategori): Rata Kiri (*Left-aligned*).
  * Angka (Qty Masuk, Qty Keluar, Saldo): Rata Kanan (*Right-aligned*).
  * Status Badge & Tanggal: Rata Tengah (*Center-aligned*).

### 4.2. Mobile & Tablet Opname Sheet
* **Card-Based Entry:** Setiap item fisik ditampilkan dalam bentuk kartu terisolasi dengan informasi utama: Nama, Kode Rak, Satuan Hitung.
* **Large Touch Numeric Pad:** 
  * Tombol angka berukuran minimal 48px × 48px.
  * Tombol stepper cepat: `+1`, `+5`, `+10`, `+1 Karton (x12)` untuk mempercepat penghitungan multi-pack.
* **Blind Mode Toggle:** Penutup angka stok sistem (ditampilkan sebagai `***` atau tombol *Reveal*) untuk menjaga objektivitas checker.

### 4.3. Badge & Status Tag
* Komponen berbentuk pil atau persegi berujung melengkung (*rounded-md*, radius 4px).
* Format: `[Dot Indikator] + [Label Teks]`. Contoh: `● EXP: 7 Hari` atau `● Match (0)`.

---

## 5. Layout & Sistem Navigasi

### 5.1. Desktop (Supervisor / Admin)
* **Sidebar Navigasi (Kiri):** Lebar 240px, dapat diciutkan (*collapsible*) menjadi 64px (ikon saja).
  * Dashboard Ringkasan Gudang
  * Master Barang & Satuan
  * Mutasi (Inbound, Outbound, Waste)
  * Sesi Stock Opname
  * Kartu Stok & Audit Trail
* **Top Bar (Atas):** Indikator gudang aktif, pencarian global (tekan `/` untuk fokus ke input search SKU), notifikasi alert expired, dan profil pengguna.

### 5.2. Tablet & Mobile (Staff Checker)
* **Bottom Navigation Bar:** Memuat 3 menu utama:
  1. *Hitung (Opname)*
  2. *Inbound / Outbound*
  3. *Cari Stok / Rak*
* **Header Sederhana:** Menampilkan nama sesi aktif, progres hitung (contoh: `Item 24 dari 80`), dan tombol *Simpan Draft*.

---

## 6. Penanganan Status Kosong, Pemuatan, & Error (State Guidelines)
* **Loading State:** Menggunakan *Skeleton Loading* berbentuk baris tabel abu-abu berkedip halus, bukan spinner putar tunggal, agar struktur tabel tidak lompat saat data selesai dimuat.
* **Empty State:** Ilustrasi minimalis dengan pesan instruktif dan tombol tindakan langsung (contoh: *"Belum ada barang di Rak A-02. Mulai input barang masuk?"*).
* **Offline / Weak Connection State:** Banner peringatan di bagian atas layar: *"Koneksi terputus. Data hitungan tersimpan lokal di browser (IndexedDB) dan akan disinkronkan saat online."*
