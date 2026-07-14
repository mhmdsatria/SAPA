# LaporKota GIS

LaporKota GIS adalah platform pelaporan publik berbasis Laravel 13, Livewire 4, Tailwind CSS 4, Leaflet, MySQL Spatial, dan PWA. Versi ini mencakup dashboard pelapor dan admin dengan pola sidebar kiri, galeri publik, multiunggah foto dan video, sinkronisasi GPS dan alamat, kategori dinamis, moderasi, interaksi publik, serta peta berjenjang tanpa poligon kotak dekoratif.

## Fitur utama

### Publik dan pelapor

- Navbar publik: Beranda, Galeri, Buat Laporan, Dashboard Pelapor, dan akses Admin bagi pengguna berwenang.
- Galeri modern pada `/galeri` dengan filter kata kunci, wilayah, kategori, dan tipe media. Setiap kartu membuka detail laporan.
- Linimasa laporan terbaru dapat difilter berdasarkan provinsi, kota, kabupaten, kategori, dan kata kunci.
- Wizard pelaporan tiga langkah dengan maksimal delapan foto atau video.
- Format media: JPEG, PNG, WebP, MP4, MOV, dan WebM. Batas aplikasi 50 MB per berkas.
- Foto dikompresi ke WebP dan diperiksa metadata EXIF. Foto berumur lebih dari tujuh hari diberi indikator untuk moderator.
- GPS diambil melalui Geolocation API browser dengan mode akurasi tinggi. Latitude dan longitude dikunci pada properti Livewire dan tidak tersedia sebagai input teks.
- Reverse geocoding memakai Mapbox, lalu Google Maps sebagai fallback. Pelapor dapat mengoreksi teks alamat tanpa menggeser koordinat GPS.
- Laporan berstatus `pending` atau `rejected` dapat diedit dan dihapus oleh pemilik. Setelah `approved`, laporan terkunci.
- Detail laporan menampilkan grid media, video player, peta mini, alamat, akurasi GPS, komentar, dan upvote.
- Dashboard pelapor memakai sidebar kiri dan konten di kanan, dengan animasi ringan dan dark mode.

### Admin

- Dashboard admin memakai tema portal yang sama dengan dashboard pelapor.
- Moderasi cepat: Approve, Reject, dan Edit & Approve.
- Indikator media EXIF lama dan kemungkinan duplikat spasial.
- Pengelolaan kategori pada `/admin/kategori`: tambah, edit, urutkan, aktifkan, nonaktifkan, dan hapus kategori yang belum dipakai.
- Arsip laporan dengan filter tanggal, status, wilayah, dan kategori serta ekspor PDF dan Excel.
- Peta pantau dengan ringkasan wilayah pada zoom rendah dan titik laporan berklaster pada zoom tinggi.
- Admin daerah dibatasi berdasarkan assignment wilayah. Struktur ini tetap future-ready.

### GIS dan stabilitas peta

- MySQL `POINT SRID 4326` dan spatial index pada laporan.
- MySQL `POLYGON SRID 4326` dan spatial index pada wilayah.
- Seluruh konstruksi geometry memakai `axis-order=long-lat` agar longitude dan latitude tidak tertukar.
- Deteksi duplikat pada radius 15 meter menggunakan `ST_Distance_Sphere` untuk laporan pada hari yang sama.
- Ringkasan wilayah ditampilkan sebagai gelembung proporsional pada centroid wilayah, bukan persegi atau kotak.
- Marker clustering menggunakan `Leaflet.markercluster`.
- Peta memiliki fallback tile, `ResizeObserver`, `invalidateSize`, lifecycle cleanup, marker lokasi awal, dan lingkaran akurasi GPS.

## Requirement

- PHP 8.3 atau lebih baru
- Composer 2.x
- Node.js 20 atau lebih baru
- npm 10 atau lebih baru
- MySQL 8.x atau lebih baru
- Ekstensi PHP: `exif`, `fileinfo`, `gd`, `mbstring`, `pdo_mysql`
- HTTPS pada production agar Geolocation API dan PWA dapat berjalan. `localhost` dan `127.0.0.1` dapat digunakan saat pengembangan.

MySQL 8.x wajib karena aplikasi memakai SRID, spatial index, `ST_Contains`, dan `ST_Distance_Sphere`.

## Perbaikan otomatis pada proyek lama

Letakkan `perbaikan_full_gis_media.sh` di folder root proyek Laravel yang berisi `artisan` dan `composer.json`, lalu jalankan:

```bash
chmod +x perbaikan_full_gis_media.sh
bash perbaikan_full_gis_media.sh
```

Skrip akan:

1. Memvalidasi folder proyek, PHP, Composer, Node.js, dan npm.
2. Membuat backup source lama di `storage/app/upgrade-backups/`.
3. Menimpa source aplikasi dengan paket perbaikan yang konsisten.
4. Menjalankan `composer install` dan autoload optimization.
5. Menjalankan migration kategori dinamis, complaint media, dan peningkatan complaint.
6. Menjalankan seeder upgrade yang aman untuk data lama.
7. Menjalankan `storage:link`.
8. Menjalankan `npm install` dan `npm run build`.
9. Membersihkan lalu membangun ulang cache konfigurasi, route, dan view.

Skrip tidak menimpa file `.env` dan tidak menghapus media di `storage/app/public`.

## Instalasi proyek baru

```bash
unzip laravel_keluhan.zip
cd laravel_keluhan
chmod +x install.sh
bash install.sh
```

Atau instalasi manual:

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Untuk development frontend:

```bash
npm run dev
```

## Konfigurasi `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_keluhan
DB_USERNAME=root
DB_PASSWORD=

MAPBOX_API_KEY=
GOOGLE_MAPS_API_KEY=
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local
```

Gunakan salah satu geocoding key. Mapbox diprioritaskan, sedangkan Google Maps menjadi fallback.

- Mapbox access token: https://docs.mapbox.com/help/getting-started/access-tokens/
- Google Geocoding API: https://developers.google.com/maps/documentation/geocoding/get-api-key
- Google OAuth: https://console.cloud.google.com/apis/credentials

## Batas unggahan server

Aplikasi telah menyertakan `public/.user.ini`:

```ini
upload_max_filesize=50M
post_max_size=420M
max_file_uploads=12
memory_limit=512M
max_execution_time=300
max_input_time=300
```

Pada Nginx, tambahkan `client_max_body_size 420M;`. Pada PHP-FPM yang tidak membaca `.user.ini`, terapkan nilai yang sama di `php.ini` atau pool configuration, kemudian restart PHP-FPM dan web server.

## Akun awal

Seeder proyek baru membuat akun berikut:

```text
Email: admin@laporkota.test
Password: Admin123!
Role: super_admin
```

Ganti password segera setelah login pertama.

## Route penting

```text
/                              Beranda dan peta publik
/galeri                        Galeri foto dan video
/lapor                         Wizard laporan
/lapor/{slug}/edit             Edit laporan belum disetujui
/keluhan/{slug}                Detail laporan
/profil                        Dashboard pelapor
/admin/dashboard               Dashboard admin
/admin/laporan/masuk           Antrean moderasi
/admin/laporan/arsip           Arsip dan ekspor
/admin/kategori                Pengelolaan kategori
/admin/peta                    Peta pantau admin
```

## Catatan deployment

- Jalankan queue worker apabila OTP atau notifikasi diubah menjadi asynchronous.
- Pastikan `storage` dan `bootstrap/cache` dapat ditulis oleh user web server.
- Pastikan symlink `public/storage` tersedia.
- Atur `APP_DEBUG=false` pada production.
- Batasi key Mapbox dan Google berdasarkan domain production.
- Gunakan HTTPS agar GPS browser, kamera, dan PWA konsisten.
# SAPA
# SAPA
# SAPA
