SIPPD Kota Tasikmalaya

Sistem Informasi Data Profil Pembangunan Daerah Kota Tasikmalaya

Aplikasi web Sistem Informasi Data Profil Pembangunan Daerah (SIPPD / Data Sektoral) untuk pengelolaan, pemantauan, visualisasi, dan analisis indikator capaian pembangunan daerah (Bappelitbangda Kota Tasikmalaya).


Fitur Utama

- Dashboard Data Sektoral Interaktif:
  - Ringkasan KPI dan statistik utama (Kelompok Urusan A s.d. H, Urusan Pemerintahan, Indikator Sektoral / Tabel Aktif, dan Persentase Kelengkapan Pengisian Data).
  - Filter tahun instan terintegrasi secara realtime.
  - Visualisasi grafik interaktif berbasis Chart.js (Grafik batang perbandingan kelengkapan data per kelompok urusan & grafik garis tren pengisian data tahun ke tahun).
  - Rangkuman hierarki lengkap dengan pencarian cepat dan filter urusan aktif.

- Manajemen Tabel & Data Sektoral Dinamis:
  - Struktur data berjenjang: Kelompok Urusan, Urusan Pemerintahan, Multi-Tabel Indikator, Baris Induk, Sub-Baris bertingkat.
  - Kolom kustom dan sub-kolom bersarang dengan dukungan pengurutan interaktif.
  - Penyesuaian ukuran kolom dan baris tabel secara dinamis.
  - Perhitungan otomatis nilai total/jumlah secara horizontal (kolom jumlah) maupun vertikal (baris total/footer).
  - Input & pembaruan nilai cepat secara inline (single cell maupun batch update).

- Ekspor, Impor & Pelaporan Terintegrasi:
  - Ekspor data tabel ke format Excel (.xlsx) lengkap dengan format resmi, border, header merge, dan baris total.
  - Ekspor laporan resmi ke format PDF siap cetak sesuai standar tata naskah dinas.
  - Unduh format Template Excel otomatis yang menyesuaikan struktur kolom unik pada setiap tabel.
  - Impor massal data indikator dari file Excel (.xlsx) dengan validasi otomatis.

- Multi-Role & Keamanan Berlapis:
  - Tiga tingkatan peran pengguna: Super Admin, Admin, dan Pengguna Publik (User).
  - Portal masuk terpisah untuk masing-masing hak akses.
  - Pengaturan URL Slug Login Admin & Super Admin yang dapat diubah secara dinamis melalui panel pengaturan untuk proteksi dari serangan brute-force.
  - Registrasi mandiri untuk akun pengguna publik dengan proteksi rate limiting / throttle.
  - Rekam jejak audit yang mencatat setiap riwayat perubahan dan aktivitas penting pada sistem.

- Manajemen Master Data & Tahun Anggaran:
  - Pengelolaan Master Kelompok Urusan (Folder Utama A-H) dan Urusan Bidang Pemerintahan (Pendidikan, Kesehatan, Pekerjaan Umum, dll.).
  - Pengelolaan Master Tahun Anggaran (penambahan tahun, pengaturan tahun default, dan kontrol status aktif/nonaktif).
  - Manajemen Pengguna oleh Super Admin (aktivasi akun, manajemen peran, ubah kata sandi).

- Performa Cepat & Desain Responsif:
  - Antarmuka modern berbasis Bootstrap 5 dengan tata letak clean, tipografi Inter, dan palet warna standar dinas.
  - Sidebar dinamis dengan penyimpanan preferensi lebar sidebar dan mode collapse otomatis pada perangkat seluler.
  - Navigasi cepat bergaya SPA (Single Page Application) yang meminimalkan waktu muat halaman.

---

## Pemeliharaan & Hardening Produksi (Security Hardening)

Aplikasi SIPPD telah dilengkapi konfigurasi hardening sesuai rekomendasi Vulnerability Assessment (VA) Diskominfo:

### 1. HTTP Security Headers & Web Server Hardening (Pilar 2)
- **Middleware `SecurityHeaders`** secara otomatis menyematkan header keamanan pada setiap respons HTTP:
  - `X-Frame-Options: SAMEORIGIN` (Pencegahan clickjacking)
  - `X-Content-Type-Options: nosniff` (Pencegahan MIME type sniffing)
  - `X-XSS-Protection: 1; mode=block`
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()`
  - `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload` (HSTS pada koneksi HTTPS)
  - Penghapusan header `X-Powered-By` untuk menyamarkan identitas server PHP.
- **Konfigurasi Web Server**:
  - Apache: Telah diatur melalui `public/.htaccess` (proteksi file tersembunyi `.*`, penonaktifan directory indexing).
  - Nginx: Template konfigurasi produksi siap pakai tersedia di [`deploy/nginx/sippd.conf`](deploy/nginx/sippd.conf).

### 2. Otomatisasi Backup & Retensi Audit Log (Pilar 4)
- **Otomatisasi Backup Database**:
  ```bash
  # Melakukan backup database manual (disimpan di storage/app/backups/)
  php artisan db:backup --keep=14
  ```
  Menyimpan snapshot data database dan secara otomatis merotasi file backup dengan mempertahankan 14 file terbaru.
- **Pembersihan Log Aktivitas Usang**:
  ```bash
  # Membersihkan log aktivitas yang lebih lama dari 180 hari
  php artisan activity-log:prune --days=180
  ```
- **Laravel Scheduler (Cron Job Server Produksi)**:
  Tambahkan cron job berikut pada server Linux (Ubuntu/Debian) Diskominfo:
  ```bash
  * * * * * cd /var/www/sippd && php artisan schedule:run >> /dev/null 2>&1
  ```
  Scheduler akan menjalankan:
  - Backup database harian setiap pukul 02:00 dini hari.
  - Pembersihan log aktivitas lama setiap hari Minggu pukul 03:00 dini hari.

