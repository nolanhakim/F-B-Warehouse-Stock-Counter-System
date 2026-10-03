# F&B Warehouse Management & Stock Counter System

Aplikasi web internal untuk mengelola bahan baku di gudang F&B: pencatatan barang masuk dan keluar, pelacakan batch dan tanggal kedaluwarsa dengan prinsip FEFO, modul stock opname digital, serta jejak audit yang tidak bisa dihapus.

Berjalan di jaringan lokal (XAMPP), tanpa integrasi ke mesin kasir. Fokusnya satu: fisik barang di gudang.

---

## Fitur

### Master Barang
Katalog SKU dengan nama bahan, kategori penyimpanan, ambang batas minimum (safety stock), dan lokasi rak.

- Kategori: **Dry Goods**, **Chilled**, **Frozen**, **Packaging**
- SKU dibuat otomatis berdasar kategori (`DRY-001`, `CHL-001`, `FZN-001`, `PKG-001`)
- Konversi satuan bertingkat: satuan beli besar (Karton, Jeriken, Karung) ke satuan hitung (Pack, Botol, Kg) dengan rasio terkunci, misal 1 Karton = 12 Pack
- Lokasi rak/bin tersimpan per barang, misal `CHILLER-01-A2` atau `RAK-DRY-B3`

### Mutasi Stok
Pencatatan setiap pergerakan barang dengan nomor dokumen otomatis.

| Jenis | Prefix | Pengaruh stok |
|---|---|---|
| Inbound | `INB` | `+` |
| Outbound | `OUT` | `-` |
| Waste | `WST` | `-` (menunggu approval) |

- Stok tidak boleh keluar melebihi saldo tersedia
- Waste wajib melalui persetujuan Supervisor sebelum memotong stok
- Filter per jenis, status, dan rentang tanggal
- Ekspor CSV

### Alert Kedaluwarsa & FEFO
Deteksi otomatis batch yang mendekati dan lewat tanggal kedaluwarsa.

- **Merah (Danger):** sudah lewat tanggal
- **Orange (H-7):** 7 hari atau kurang
- **Kuning (H-30):** 30 hari atau kurang
- Rekomendasi alokasi otomatis: yang expired terdekat keluar lebih dulu (First Expired, First Out)

### Sesi Stock Opname
Penghitungan fisik dengan tampilan mobile/tablet dan simpan draft per rak.

- Sesi dibuka Supervisor, hanya satu sesi aktif pada satu waktu
- Simpan draft berkala, bisa dilanjutkan lain waktu
- Perhitungan otomatis selisih antara stok fisik dan stok sistem
- Kategorikan hasil: **Match**, **Shortage**, atau **Overage**

### Waste & Approval
Pencatatan barang rusak, basi, atauPACKAGING bocor.

- Status `pending` sampai disetujui atau ditolak Supervisor
- Notifikasi jumlah waste menunggu di header halaman

### Kartu Stok & Audit Trail
Riwayat kronologis tiap SKU dengan saldo berjalan.

- Kolom: tanggal, nomor dokumen, jenis, masuk, keluar, saldo, batch, expired, nama penginput
- Ekspor CSV per SKU
- Semua aksi tulis tercatat di log aktivitas dengan email, waktu, dan perubahan data

### Manajemen User & Log Aktivitas
Khusus Super Admin.

- CRUD user, atur peran, aktif/nonaktifkan akun
- Log aktivitas seluruh sistem

### Assistant Gudang (AI)
Chat bot di dalam aplikasi, berbasis Gemini.

- Menjawab stok, safety stock, lokasi rak, batch, dan status expired langsung dari database
- Memandu pakai fitur: cara input inbound, outbound, waste, opname
- Hanya menjawab topik sistem ini, menolak pertanyaan di luar cakupan
- Ganti model otomatis ke cadangan kalau model sedang sibuk (503/429/timeout)

---

## Hak Akses

| Fitur | Staff Gudang | Admin Gudang | Super Admin |
|---|:---:|:---:|:---:|
| Dashboard | ✅ | ✅ | ✅ |
| Master Barang (lihat) | ✅ | ✅ | ✅ |
| Master Barang (kelola) | — | ✅ | ✅ |
| Mutasi Stok | ✅ | ✅ | ✅ |
| Alert & Expired | ✅ | ✅ | ✅ |
| Cari Stok / Rak | ✅ | ✅ | ✅ |
| Kartu Stok & Audit | ✅ | ✅ | ✅ |
| Stock Opname (hitung) | ✅ | ✅ | ✅ |
| Stock Opname (buka sesi) | — | ✅ | ✅ |
| Waste (catat) | ✅ | ✅ | ✅ |
| Waste (approve/reject) | — | ✅ | ✅ |
| Manajemen User | — | — | ✅ |
| Log Aktivitas | — | — | ✅ |

Pendaftaran baru selalu mendapat peran **Staff Gudang**. Promosi peran hanya lewat Super Admin.

---

## Kebutuhan Sistem

- PHP 8.3+
- Composer 2
- Node.js 20+
- MySQL 8 (atau MariaDB)
- XAMPP untuk pengembangan lokal

### Ekstensi PHP

Aplikasi butuh beberapa ekstensi aktif di `php.ini`. Kalau `pdo_mysql` atau `curl` dimatikan, halaman akan gagal dibuka.

```ini
extension=curl
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=fileinfo
extension=tokenizer
extension=xml
```

---

## Instalasi

### 1. Siapkan database

Buat database kosong di MySQL:

```sql
CREATE DATABASE stockopname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Pasang dependency

```bash
composer install
npm install
```

### 3. Siapkan konfigurasi

Salin `.env.example` jadi `.env` lalu sesuaikan:

```ini
APP_URL=http://localhost/fnb-warehouse-system/public

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stockopname
DB_USERNAME=root
DB_PASSWORD=
```

Buat APP_KEY:

```bash
php artisan key:generate
```

### 4. Jalankan migrasi dan build

```bash
php artisan migrate
npm run build
```

### 5. Jalankan

Kalau pakai XAMPP, letakkan folder di `C:\xampp\htdocs\` lalu buka:

```
http://localhost/fnb-warehouse-system/public
```

Alternatif tanpa Apache:

```bash
php artisan serve
```

Buat akun pertama lewat halaman `/register`, lalu masuk.

---

## Konfigurasi AI Assistant

Assistant butuh API key Gemini. Ambil dari [Google AI Studio](https://aistudio.google.com/api-keys), lalu isi di `.env`:

```ini
GEMINI_API_KEY=kunci-api-anda
GEMINI_MODEL=gemini-3.8-flash
```

Kalau `GEMINI_API_KEY` kosong, Assistant tetap jalan dengan jawaban berbasis aturan dan data langsung dari database, tanpa AI.

### Catatan SSL di Windows

XAMPP sering belum punya sertifikat CA, sehingga panggilan HTTPS ke Gemini gagal dengan `cURL error 60`. Perbaiki sekali saja di `php.ini`:

1. Salin `cacert.pem` ke `C:\xampp\php\extras\ssl\cacert.pem`
2. Aktifkan di `[curl]` dan `[openssl]`:

```ini
curl.cainfo = C:\xampp\php\extras\ssl\cacert.pem
openssl.cafile = C:\xampp\php\extras\ssl\cacert.pem
```

3. Restart Apache dari XAMPP Control Panel

### Model cadangan

Kalau model utama sedang experiencing high demand, sistem otomatis mencoba model berikutnya. Urutan cadangan diatur di `config/services.php`:

```php
'fallback_models' => ['gemini-3.6-flash', 'gemini-3.5-flash', 'gemini-3.1-flash-lite'],
```

---

## Perintah yang Tersedia

```bash
php artisan serve       # server pengembangan
npm run dev             # Vite dev server dengan hot reload
npm run build           # build aset untuk produksi
php artisan migrate     # jalankan migrasi database
php artisan test        # jalankan test suite
./vendor/bin/pint       # rapikan format kode
```

Untuk pengembangan, `composer dev` menjalankan server, queue, log, dan Vite sekaligus.

### Test

Test suite memakai SQLite in-memory:

```bash
php artisan test
```

Kalau PHP CLI tidak memuat `pdo_sqlite`, jalankan PHPUnit langsung:

```bash
php vendor/bin/phpunit
```

---

## Struktur Proyek

```
app/
  Http/Controllers/    # Auth, Dashboard, Item, Mutation, Opname, Waste,
                       # KartuStok, Alerts, Log, User, Profile, Chatbot
  Models/              # User, Item, Mutation, OpnameSession, ActivityLog
resources/
  views/
    admin/             # Tampilan untuk Admin Gudang
    staff/             # Tampilan untuk Staff Gudang
    super/             # Tampilan untuk Super Admin
    partials/pages/    # Bagian halaman yang dipakai bersama antar peran
    layouts/           # Layout utama, sidebar, modal
  css/app.css          # Token desain dan utility
  js/app.js            # Interaksi antarmuka dan logika chat
routes/web.php         # Seluruh rute web
database/migrations/   # Skema tabel
tests/                 # Test feature
```

Tampilan dipisah per peran di `resources/views/{role}/`, sementara bagian halaman yang identik dipakai ulang lewat `resources/views/partials/pages/`.

---

## Dokumentasi Lain

- `prd.md` — Product Requirement Document, kebutuhan fungsional dan non-fungsional
- `design.md` — Design system: palet warna, tipografi, pola komponen, dan panduan status

---

## Lisensi

MIT
