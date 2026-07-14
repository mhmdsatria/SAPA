#!/usr/bin/env bash
set -Eeuo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

log() {
    printf '\033[1;34m[LaporKota]\033[0m %s\n' "$1"
}

fail() {
    printf '\033[1;31m[ERROR]\033[0m %s\n' "$1" >&2
    exit 1
}

command -v php >/dev/null 2>&1 || fail "PHP tidak ditemukan. Pasang PHP 8.3 atau lebih baru."
command -v composer >/dev/null 2>&1 || fail "Composer tidak ditemukan. Pasang Composer 2.x terlebih dahulu."
command -v node >/dev/null 2>&1 || fail "Node.js tidak ditemukan. Pasang Node.js 20 atau lebih baru."
command -v npm >/dev/null 2>&1 || fail "npm tidak ditemukan."
command -v mysql >/dev/null 2>&1 || fail "MySQL client tidak ditemukan. Pasang MySQL 8.x client dan server."

PHP_VERSION="$(php -r 'echo PHP_VERSION;')"
php -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' \
    || fail "PHP ${PHP_VERSION} tidak didukung. Minimal PHP 8.3."

for extension in exif fileinfo gd mbstring pdo_mysql; do
    php -m | grep -qi "^${extension}$" || fail "Ekstensi PHP ${extension} belum aktif."
done

MYSQL_ARGS=(-h127.0.0.1 -P3306 -uroot)
if ! mysql "${MYSQL_ARGS[@]}" -e "SELECT 1" >/dev/null 2>&1; then
    MYSQL_ARGS=(-uroot)
fi

mysql "${MYSQL_ARGS[@]}" -e "SELECT 1" >/dev/null 2>&1 \
    || fail "Tidak dapat masuk ke MySQL sebagai root tanpa password. Sesuaikan install.sh atau buat database secara manual."

MYSQL_VERSION="$(mysql "${MYSQL_ARGS[@]}" -Nse "SELECT VERSION();")"
MYSQL_MAJOR="${MYSQL_VERSION%%.*}"
[[ "$MYSQL_MAJOR" =~ ^[0-9]+$ ]] || fail "Versi MySQL tidak dapat dibaca: ${MYSQL_VERSION}"
(( MYSQL_MAJOR >= 8 )) || fail "MySQL ${MYSQL_VERSION} tidak didukung. Minimal MySQL 8.x."

log "Membuat database laravel_keluhan"
mysql "${MYSQL_ARGS[@]}" -e "CREATE DATABASE IF NOT EXISTS laravel_keluhan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if [[ ! -f .env ]]; then
    cp .env.example .env
    log "Membuat .env dari .env.example"
fi

log "Memasang dependensi PHP"
composer install --no-interaction --prefer-dist --optimize-autoloader

log "Memasang dependensi frontend"
npm install

log "Membuat application key"
php artisan key:generate --force

log "Menyiapkan direktori dan symbolic link storage"
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs
chmod -R ug+rwX storage bootstrap/cache
php artisan storage:link >/dev/null 2>&1 || true

log "Menjalankan migration MySQL Spatial dan seeder"
php artisan migrate --force
php artisan db:seed --force

log "Membangun aset production"
npm run build

log "Membersihkan cache aplikasi"
php artisan optimize:clear

cat <<'INFO'

Instalasi selesai.

Jalankan aplikasi:
  php artisan serve

URL:
  http://127.0.0.1:8000

Super Admin:
  Email    : admin@laporkota.test
  Password : Admin123!

Akun warga:
  Email    : warga@laporkota.test
  Password : Warga123!

Untuk pengembangan frontend, jalankan pada terminal lain:
  npm run dev
INFO
