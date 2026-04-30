WebGIS Tutupan Lahan

Aplikasi WebGIS berbasis Laravel yang menampilkan data tutupan lahan dan administrasi desa dalam bentuk peta interaktif dan visualisasi grafik. Pengguna dapat melakukan filtering wilayah hingga tingkat desa serta melihat distribusi kategori tutupan lahan secara dinamis.


Fitur Utama

- Peta interaktif menggunakan Leaflet
- Visualisasi polygon wilayah (Kabupaten, Kecamatan, Desa)
- Visualisasi titik tutupan lahan
- Filter wilayah bertingkat:
  - Kabupaten
  - Kecamatan
  - Desa
- Zoom otomatis ke area yang dipilih
- Highlight wilayah terpilih
- Grafik:
  - Pie Chart (distribusi kategori)
  - Bar Chart (jumlah data)
- Sidebar interaktif
- Legend kategori tutupan lahan

Teknologi yang Digunakan

- Laravel (Backend)
- PostgreSQL + PostGIS
- Leaflet.js
- Chart.js
- Tailwind CSS

---

Cara Instalasi
1. Clone repository:
```bash
git clone https://github.com/adzrhm/Jobtest

2. Masuk ke folder project:
cd nama-repo

3. Install dependency:
composer install

4. Copy file environment:
cp .env.example .env

5. Generate key:
php artisan key:generate

6. Konfigurasi database di file .env:
DB_DATABASE=nama_database
DB_USERNAME=postgres
DB_PASSWORD=your_password

Setup Database
1. Pastikan PostgreSQL dan PostGIS sudah terinstall
2. Aktifkan PostGIS
3. Import data spasial (polygon dan titik)

Deployment Guide
1. Upload project ke server
2. Jalankan:
composer install
php artisan key:generate

3. Konfigurasi .env sesuai server
4. Pastikan:

Database PostgreSQL aktif
PostGIS aktif
Folder storage dan bootstrap/cache writable
