#!/usr/bin/env bash
set -Eeuo pipefail

PATCH_NAME="admin-auth-seeder-fix"
EXPECTED_PAYLOAD_SHA256="912cbd629be1c5daaad85e1e27404d49fc6ce2e352f471c8eecf7de29c16c3ab"
ROOT_DIR="$(pwd)"
BACKUP_DIR="storage/app/patch-backups/${PATCH_NAME}-$(date +%Y%m%d-%H%M%S)"
TMP_DIR="$(mktemp -d)"

cleanup() {
    rm -rf "$TMP_DIR"
}
trap cleanup EXIT

fail() {
    printf '\n[ERROR] %s\n' "$1" >&2
    exit 1
}

[[ -f artisan ]] || fail "Jalankan skrip ini dari folder root Laravel yang berisi file artisan."
[[ -f composer.json ]] || fail "composer.json tidak ditemukan di folder saat ini."
command -v php >/dev/null 2>&1 || fail "PHP CLI tidak ditemukan."
command -v tar >/dev/null 2>&1 || fail "Perintah tar tidak ditemukan."

printf '\n[1/7] Menyiapkan payload perbaikan...\n'
cat > "$TMP_DIR/payload.b64" <<'PAYLOAD'
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
PAYLOAD

php -r '
    $encoded = file_get_contents($argv[1]);
    $decoded = base64_decode($encoded, true);
    if ($decoded === false) {
        fwrite(STDERR, "Payload base64 tidak valid.\n");
        exit(1);
    }
    file_put_contents($argv[2], $decoded);
' "$TMP_DIR/payload.b64" "$TMP_DIR/payload.tar.gz"

ACTUAL_PAYLOAD_SHA256="$(php -r 'echo hash_file("sha256", $argv[1]);' "$TMP_DIR/payload.tar.gz")"
[[ "$ACTUAL_PAYLOAD_SHA256" == "$EXPECTED_PAYLOAD_SHA256" ]] || fail "Checksum payload tidak sesuai. Unduh ulang skrip."

tar -xzf "$TMP_DIR/payload.tar.gz" -C "$TMP_DIR"

printf '[2/7] Membuat backup source lama...\n'
mkdir -p "$BACKUP_DIR"
TARGETS=(
    "database/seeders/DatabaseSeeder.php"
    "app/Models/User.php"
    "app/Http/Requests/Auth/LoginRequest.php"
    "app/Http/Controllers/Auth/SessionController.php"
    "app/Http/Controllers/Auth/OtpController.php"
    "app/Http/Controllers/Auth/GoogleAuthController.php"
    "lang/id/validation.php"
)
EXISTING_TARGETS=()
for target in "${TARGETS[@]}"; do
    if [[ -e "$target" ]]; then
        EXISTING_TARGETS+=("$target")
    fi
done
if [[ ${#EXISTING_TARGETS[@]} -gt 0 ]]; then
    tar -czf "$BACKUP_DIR/source-before-patch.tar.gz" "${EXISTING_TARGETS[@]}"
fi

printf '[3/7] Menerapkan file perbaikan...\n'
tar -xzf "$TMP_DIR/payload.tar.gz" -C "$ROOT_DIR"

printf '[4/7] Memvalidasi sintaks PHP...\n'
while IFS= read -r -d '' file; do
    php -l "$file" >/dev/null
done < <(find "$TMP_DIR" -type f -name '*.php' -print0)

if [[ "${PATCH_SKIP_ARTISAN:-0}" == "1" ]]; then
    printf '[5/7] Artisan dilewati karena PATCH_SKIP_ARTISAN=1.\n'
    printf '[6/7] Seeder dilewati karena PATCH_SKIP_ARTISAN=1.\n'
else
    [[ -d vendor ]] || fail "Folder vendor tidak ditemukan. Jalankan composer install terlebih dahulu."

    printf '[5/7] Membersihkan cache dan menjalankan migration...\n'
    php artisan optimize:clear
    php artisan migrate --force

    printf '[6/7] Menjalankan seeder idempoten...\n'
    if ! php artisan db:seed --force; then
        printf '\nSeeder masih gagal karena kondisi database lain. File perbaikan tetap dipertahankan.\n' >&2
        printf 'Periksa error terakhir, lalu jalankan kembali: php artisan db:seed --force\n' >&2
        exit 1
    fi
fi

printf '[7/7] Selesai.\n'
printf '\nPerbaikan berhasil diterapkan.\n'
printf 'Backup source: %s\n' "$BACKUP_DIR/source-before-patch.tar.gz"
printf '\nAkun Super Admin:\n'
printf '  Email    : admin@laporkota.test\n'
printf '  Password : Admin123!\n'
printf '\nAkun Admin Daerah:\n'
printf '  Email    : admin.bandung@laporkota.test\n'
printf '  Password : Admin123!\n'
printf '\nSilakan logout dari sesi lama, hapus cookie sesi bila perlu, lalu login kembali.\n'
