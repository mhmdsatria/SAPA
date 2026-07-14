# SAPA (Sistem Aduan Publik Terpadu)

SAPA (Sistem Aduan Publik Terpadu) adalah platform pelaporan publik berbasis Laravel 13, Livewire 4, Tailwind CSS 4, Leaflet, MySQL Spatial, dan PWA. Versi ini mencakup dashboard pelapor dan admin dengan pola sidebar kiri, galeri publik, multiunggah foto dan video, sinkronisasi GPS dan alamat, kategori dinamis, moderasi, interaksi publik, serta peta berjenjang tanpa poligon kotak dekoratif.

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

