Product Requirement Document (PRD)
F&B Warehouse Management & Stock Counter System
Kategori: F&B Inventory &
Warehousing
Status: Draft Ready for
Review
Target Platform: Web Responsive (Mobile/
Tablet/PC)
1. Ringkasan Eksekutif (Product Overview)
Sistem F&B Warehouse Management & Stock Counter adalah aplikasi web internal mandiri (standalone) yang
berfokus penuh pada tata kelola fisik bahan baku di gudang. Sistem ini dirancang untuk menyederhanakan pencatatan
masuk-keluar barang, mencegah kerugian akibat kedaluwarsa melalui prinsip FEFO (First Expired, First Out), serta
menyediakan modul stock opname digital yang cepat dan akurat.
Fokus Utama: Pengelolaan fisik barang, pemetaan rak/bin, pelacakan lot batch & expiry date, mitigasi waste/spoilage,
serta rekonsiliasi varians stok tanpa integrasi POS penjualan.
2. Permasalahan & Solusi
Tantangan Operasional Gudang Solusi Sistem
Pencatatan manual di kertas atau spreadsheet rentan hilang
dan sering terjadi salah hitung satuan.
Digitalisasi pencatatan berbasis sistem web terpusat dengan
dukungan konversi satuan bertingkat otomatis.
Bahan baku basi atau kedaluwarsa di tumpukan belakang
karena tidak terpantau masa simpan fisiknya.
Pelacakan Batch & Tanggal Kedaluwarsa dengan sistem
rekomendasi pengeluaran FEFO dan alert early warning.
Proses stock opname bulanan lambat, menghentikan
operasional, dan angka stok sistem sering berbeda jauh.
Form Stock Opname Digital (Mobile/Tablet) mendukung input
bertahap, blind-count, dan rekonsiliasi instan.
Selisih barang sulit diinvestigasi karena tidak ada jejak siapa
yang mengeluarkan atau mengubah data.
Kartu Stok Digital dan Immutable Audit Trail yang mencatat
stempel waktu dan akun penginput.
3. Profil Pengguna & Hak Akses (User Roles)
1. Staff Gudang / Checker
Mencatat penerimaan barang (inbound), melayani pengeluaran bahan (outbound), mendata waste rusak, dan melakukan
penghitungan fisik saat sesi stock opname di gudang.
2. Supervisor Gudang
Memvalidasi surat penerimaan, melakukan approval pengeluaran waste/barang rusak, membuka sesi stock opname, serta
menyetujui rekonsiliasi penyesuaian selisih stok.
3. Admin / Warehouse Manager
Mengelola master data barang, kategori penyimpanan, konversi satuan, master rak/bin, manajemen akun pengguna, serta
memantau audit trail dan laporan valuasi gudang.
PRD - F&B Warehouse Management & Stock Counter System Halaman 1 dari 3
4. Kebutuhan Fungsional (Fitur Utama)
4.1. Master Data Barang, Satuan, & Lokasi Rak
Katalog Barang: Pencatatan SKU, nama bahan baku, deskripsi, ambang batas minimum (safety stock), dan foto
referensi.
Kategori Penyimpanan F&B: Pengelompokan spesifik: Dry Goods (suhu ruang), Chilled (chiller 0-4°C), Frozen
(freezer <-18°C), serta Packaging & Supplies.
Konversi Satuan Bertingkat: Pemisahan antara Satuan Beli/Besar (contoh: Karton, Jeriken, Karung) dan Satuan
Simpan/Hitung (Pack, Botol, Kg). Sistem mengunci rasio konversi (misal: 1 Karton = 12 Pack).
Manajemen Lokasi Rak & Bin: Pemetaan hierarkis penempatan barang di gudang (Zona > Rak > Baris/Tingkat,
contoh: CHILLER-01-A2 atau RAK-DRY-B3).
4.2. Pelacakan Batch & Kedaluwarsa (FEFO Tracking)
Registrasi Lot/Batch: Setiap kali barang diterima dari supplier atau central kitchen, staf wajib menginput nomor
batch dan tanggal kedaluwarsa (Expired Date).
Rekomendasi Pengeluaran FEFO: Saat staf memproses pengeluaran barang, sistem secara otomatis menunjuk
batch dengan tanggal kedaluwarsa terdekat untuk diambil terlebih dahulu.
Peringatan Mendekati Expired: Dashboard visual dengan penanda warna (merah: H-7, kuning: H-30) untuk
barang yang harus segera dialokasikan.
4.3. Mutasi Stok (Inbound, Outbound, Waste)
Penerimaan Barang (Inbound): Form input penerimaan dengan data nomor PO/surat jalan, supplier, kuantitas fisik
yang diterima, alokasi rak tujuan, dan tanggal expired.
Pengeluaran Barang (Outbound): Pengeluaran bahan baku berdasarkan formulir permintaan internal (Dapur
Utama, Bar, Pastry, atau Cabang). Stok terpotong otomatis dari batch yang disetujui.
Pencatatan Waste & Kerusakan: Form khusus mendata barang rusak (kemasan bocor, kontaminasi, basi). Wajib
menyertakan alasan spesifik dan memerlukan persetujuan Supervisor sebelum stok dipotong.
4.4. Stock Opname Digital & Rekonsiliasi Varians
Sesi Opname Terjadwal: Supervisor membuka sesi opname dengan pilihan lingkup: Parsial (per kategori/rak
tertentu) atau Total (seluruh gudang).
Antarmuka Mobile-Friendly: Dioptimalkan untuk tablet/smartphone gudang dengan keypad angka besar dan fitur
auto-save draft per rak.
Mode Hitung Buta (Blind Count): Opsi menyembunyikan angka stok sistem dari layar staf agar penghitungan fisik
tidak terpengaruh angka teoritis.
Kalkulasi Varians & Nilai Kerugian: Sistem otomatis menghitung selisih (Stok Fisik - Stok Sistem),
mengkategorikan varians (Match, Shortage, atau Overage), dan menghitung estimasi kerugian nominal.
Approval Rekonsiliasi: Penyesuaian angka stok sistem dilakukan secara instan setelah supervisor meninjau hasil
investigasi selisih.
4.5. Riwayat Kartu Stok & Jejak Audit (Audit Trail)
Kartu Stok Real-Time: Catatan kronologis mutasi per SKU: tanggal transaksi, nomor dokumen/referensi, jenis
pergerakan (+ Masuk / - Keluar / - Waste / penyesuaian Opname), dan sisa saldo stok fisik.
Immutable Audit Log: Setiap aksi pembuatan, pengubahan, atau penghapusan data tercatat secara permanen
dengan metadata User ID, Timestamp, dan perubahan data sebelum-sesudah.
•
•
•
•
•
•
•
•
•
•
•
•
•
•
•
•
•
PRD - F&B Warehouse Management & Stock Counter System Halaman 2 dari 3
5. Kebutuhan Non-Fungsional
Performa & Kecepatan: Waktu respons formulir input tidak melebihi 1,5 detik pada koneksi Wi-Fi gudang.
Integritas Database: Operasi mutasi stok wajib dijalankan dalam mekanisme atomic database transactions guna
mencegah desinkronisasi kuantitas.
Aksesibilitas Perangkat: Desain responsif fleksibel yang mendukung orientasi vertikal maupun horizontal pada
tablet gudang ukuran 8 hingga 11 inci.
6. Rencana Implementasi & Rilis (Roadmap)
Fase Ruang Lingkup Fitur Hasil / Deliverable
Fase 1 (MVP) Master Data (Barang, Kategori, Satuan), Inbound &
Outbound Manual, Kartu Stok Digital.
Penggantian buku catatan manual gudang ke
sistem pencatatan digital terpusat.
Fase 2 Manajemen Batch & Expired Date (FEFO),
Pemetaan Rak/Bin, Modul Pencatatan Waste &
Approval.
Kontrol kedaluwarsa bahan baku dan
pemantauan barang rusak/tumpah secara ketat.
Fase 3 Sesi Stock Opname Digital (Mobile View), Kalkulasi
Varians Otomatis, Dashboard Alert Expired & Low
Stock.
Efisiensi audit fisik berkala dan otomatisasi
rekonsiliasi varians stok.
•
•
•
PRD - F&B Warehouse Management & Stock Counter System Halaman 3 dari 3