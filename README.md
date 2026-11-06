# SIPPD Kota Tasikmalaya

**Sistem Informasi Data Profil Pembangunan Daerah (Data Sektoral)**

Aplikasi web Sistem Informasi Data Profil Pembangunan Daerah (SIPPD / Data Sektoral) untuk pengelolaan, pemantauan, visualisasi, dan analisis indikator capaian pembangunan daerah (Bappelitbangda Kota Tasikmalaya).

---

## Fitur Utama

- **Dashboard Data Sektoral Interaktif:**
  - Ringkasan KPI dan statistik utama (Kelompok Urusan A s.d. H, Urusan Pemerintahan, Indikator Sektoral / Tabel Aktif, dan Persentase Kelengkapan Pengisian Data).
  - Filter tahun instan (*year selector*) terintegrasi secara *realtime*.
  - Visualisasi grafik interaktif berbasis Chart.js (Grafik batang perbandingan kelengkapan data per kelompok urusan & grafik garis tren pengisian data tahun ke tahun).
  - Rangkuman hierarki lengkap dengan pencarian cepat (*instant search*) dan filter urusan aktif.

- **Manajemen Tabel & Data Sektoral Dinamis:**
  - Struktur data berjenjang: Kelompok Urusan &rarr; Urusan Pemerintahan &rarr; Multi-Tabel Indikator &rarr; Baris Induk &rarr; Sub-Baris bertingkat (*nested hierarchy*).
  - Kolom kustom dan sub-kolom bersarang (*nested sub-columns*) dengan dukungan pengurutan interaktif (*drag-and-drop* / susun urutan).
  - Penyesuaian ukuran kolom dan baris tabel secara dinamis (*resizable columns & rows* langsung di browser).
  - Perhitungan otomatis nilai total/jumlah secara horizontal (kolom jumlah) maupun vertikal (baris total/footer).
  - Input & pembaruan nilai cepat secara *inline* (*single cell* maupun *batch update*).

- **Ekspor, Impor & Pelaporan Terintegrasi:**
  - Ekspor data tabel ke format **Excel (.xlsx)** lengkap dengan format resmi, border, *header merge*, dan baris total.
  - Ekspor laporan resmi ke format **PDF** siap cetak sesuai standar tata naskah dinas.
  - Unduh format **Template Excel** otomatis yang menyesuaikan struktur kolom unik pada setiap tabel.
  - Impor massal data indikator dari file Excel (.xlsx) dengan validasi otomatis.

- **Multi-Role & Keamanan Berlapis:**
  - Tiga tingkatan peran pengguna: **Super Admin**, **Admin**, dan **Pengguna Publik (User)**.
  - Portal masuk terpisah (*dedicated auth portals*) untuk masing-masing hak akses.
  - Pengaturan URL *Slug* Login Admin & Super Admin yang dapat diubah secara dinamis melalui panel pengaturan untuk proteksi dari serangan *brute-force*.
  - Registrasi mandiri untuk akun pengguna publik dengan proteksi *rate limiting / throttle*.
  - Rekam jejak audit (*Audit Trail & Activity Log*) yang mencatat setiap riwayat perubahan dan aktivitas penting pada sistem.

- **Manajemen Master Data & Tahun Anggaran:**
  - Pengelolaan Master Kelompok Urusan (Folder Utama A-H) dan Urusan Bidang Pemerintahan (Pendidikan, Kesehatan, Pekerjaan Umum, dll.).
  - Pengelolaan Master Tahun Anggaran (penambahan tahun, pengaturan tahun *default*, dan kontrol status aktif/nonaktif).
  - Manajemen Pengguna oleh Super Admin (aktivasi akun, manajemen peran, ubah kata sandi).

- **Performa Cepat & Desain Responsif:**
  - Antarmuka modern berbasis Bootstrap 5 dengan tata letak *clean*, tipografi Inter, dan palet warna standar dinas.
  - Sidebar dinamis dengan penyimpanan preferensi lebar sidebar (*resizable sidebar*) dan mode collapse otomatis pada perangkat seluler.
  - Navigasi cepat bergaya SPA (*Single Page Application*) yang meminimalkan waktu muat halaman.

---

## Teknologi yang Digunakan

- **Backend:** [Laravel 10](https://laravel.com/) (PHP 8.1+)
- **Frontend:** Blade Templating, Bootstrap 5.3, Bootstrap Icons, Chart.js, Vanilla JavaScript
- **Database:** MySQL
- **Spreadsheet & Dokumen:** [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/)
- **Build Tool:** [Vite](https://vitejs.dev/)

---

## Panduan Instalasi

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di lingkungan lokal Anda:

### 1. Klon Repositori
```bash
git clone https://github.com/panipadika/sippd-kotatasikmalaya.git
cd sippd-kotatasikmalaya
```

### 2. Pasang Dependensi
```bash
# Dependensi PHP (Composer)
composer install

# Dependensi Frontend (NPM)
npm install
npm run build
```

### 3. Konfigurasi Lingkungan (.env)
Salin file konfigurasi `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Sesuaikan konfigurasi database pada file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sippd_db
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Kunci Aplikasi & Jalankan Migrasi
```bash
php artisan key:generate
php artisan migrate --seed
```

### 5. Buat Tautan Penyimpanan (*Storage Link*) & Jalankan Server
```bash
php artisan storage:link
php artisan serve
```
Aplikasi dapat diakses melalui browser di: `http://127.0.0.1:8000`

---

## Akun Pengguna Bawaan (Default Seeder)

Setelah menjalankan `php artisan migrate --seed`, akun berikut siap digunakan:

| Peran | Username / Email | Password Default | Portal Login Default |
|---|---|---|---|
| **Super Admin** | `superadmin` / `superadmin@datasektoral.go.id` | `password` | `/superadmin/login` |
| **Admin** | `admin` / `admin@datasektoral.go.id` | `password` | `/admin/login` |
| **User (Publik)** | `user` / `user@datasektoral.go.id` | `password` | `/portal/login` atau `/login` |

> *Catatan: Segera ubah kata sandi dan sesuaikan slug portal login setelah deployment untuk menjaga keamanan sistem.*

---

## Hak Cipta & Lisensi

Dikembangkan untuk kebutuhan pemantauan dan pengelolaan data sektoral di lingkungan **Bappelitbangda Kota Tasikmalaya**.
