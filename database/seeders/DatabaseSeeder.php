<?php

namespace Database\Seeders;

use App\Models\AdminDaerahAssignment;
use App\Models\Comment;
use App\Models\Complaint;
use App\Models\Region;
use App\Models\Upvote;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $regions = $this->seedRegions();
        $users = $this->seedUsers($regions);
        $this->seedImages();
        $complaints = $this->seedComplaints($regions, $users);
        $this->seedInteractions($complaints, $users);
        $this->call(UpgradeFeatureSeeder::class);
    }

    private function seedRegions(): array
    {
        $westJava = $this->createRegion(
            'Jawa Barat',
            '32',
            'provinsi',
            -6.9147,
            107.6098,
            [[
                [106.35, -7.82], [108.85, -7.82], [108.85, -5.90],
                [106.35, -5.90], [106.35, -7.82],
            ]]
        );

        $bandung = $this->createRegion(
            'Kota Bandung',
            '32.73',
            'kota',
            -6.9175,
            107.6191,
            [[
                [107.5450, -6.9850], [107.7400, -6.9850], [107.7400, -6.8200],
                [107.5450, -6.8200], [107.5450, -6.9850],
            ]],
            $westJava->id
        );

        $cimahi = $this->createRegion(
            'Kota Cimahi',
            '32.77',
            'kota',
            -6.8841,
            107.5413,
            [[
                [107.5000, -6.9500], [107.5900, -6.9500], [107.5900, -6.8200],
                [107.5000, -6.8200], [107.5000, -6.9500],
            ]],
            $westJava->id
        );

        return compact('westJava', 'bandung', 'cimahi');
    }

    private function createRegion(
        string $name,
        string $code,
        string $level,
        float $latitude,
        float $longitude,
        array $coordinates,
        ?int $parentId = null
    ): Region {
        $pairs = collect($coordinates[0])
            ->map(fn (array $point): string => number_format($point[0], 7, '.', '').' '.number_format($point[1], 7, '.', ''))
            ->implode(',');

        $region = Region::query()->firstOrNew(['code' => $code]);
        $region->fill([
            'parent_id' => $parentId,
            'name' => $name,
            'level' => $level,
            'center_latitude' => $latitude,
            'center_longitude' => $longitude,
            'boundary_geojson' => [
                'type' => 'Polygon',
                'coordinates' => $coordinates,
            ],
            'is_active' => true,
        ]);
        $region->boundary = DB::raw("ST_GeomFromText('POLYGON(({$pairs}))', 4326, 'axis-order=long-lat')");
        $region->save();

        return $region;
    }

    private function seedUsers(array $regions): array
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@laporkota.test'],
            [
                'name' => 'Super Admin LaporKota',
                'phone' => '628111111111',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'password' => Hash::make('Admin123!'),
                'role' => User::ROLE_SUPER_ADMIN,
            ]
        );

        $regionalAdmin = User::query()->updateOrCreate(
            ['email' => 'admin.bandung@laporkota.test'],
            [
                'region_id' => $regions['bandung']->id,
                'name' => 'Admin Daerah Bandung',
                'phone' => '628122222222',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'password' => Hash::make('Admin123!'),
                'role' => User::ROLE_ADMIN_DAERAH,
            ]
        );

        AdminDaerahAssignment::query()->updateOrCreate(
            ['user_id' => $regionalAdmin->id, 'region_id' => $regions['bandung']->id],
            ['is_primary' => true]
        );

        $citizen = User::query()->updateOrCreate(
            ['email' => 'warga@laporkota.test'],
            [
                'name' => 'Warga Bandung',
                'phone' => '628133333333',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'password' => Hash::make('Warga123!'),
                'role' => User::ROLE_MASYARAKAT,
            ]
        );

        $otherCitizens = collect(range(1, 5))->map(function (int $number): User {
            return User::query()->updateOrCreate(
                ['email' => "warga{$number}@laporkota.test"],
                [
                    'name' => "Warga Demo {$number}",
                    'phone' => '62814000000'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'password' => Hash::make('Warga123!'),
                    'role' => User::ROLE_MASYARAKAT,
                ]
            );
        });

        return compact('admin', 'regionalAdmin', 'citizen', 'otherCitizens');
    }

    private function seedImages(): void
    {
        $categories = [
            'jalan' => ['#dc2626', 'Kerusakan Jalan'],
            'kebersihan' => ['#16a34a', 'Masalah Kebersihan'],
            'penerangan' => ['#eab308', 'Penerangan Jalan'],
            'lainnya' => ['#2563eb', 'Fasilitas Publik'],
        ];

        foreach ($categories as $category => [$color, $label]) {
            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720">
  <rect width="1280" height="720" fill="#e2e8f0"/>
  <rect x="60" y="60" width="1160" height="600" rx="36" fill="{$color}" opacity="0.92"/>
  <circle cx="640" cy="285" r="110" fill="#ffffff" opacity="0.22"/>
  <path d="M590 285h100M640 235v100" stroke="#ffffff" stroke-width="24" stroke-linecap="round"/>
  <text x="640" y="490" text-anchor="middle" font-family="Arial, sans-serif" font-size="58" font-weight="700" fill="#ffffff">{$label}</text>
  <text x="640" y="555" text-anchor="middle" font-family="Arial, sans-serif" font-size="30" fill="#ffffff">Data demonstrasi LaporKota GIS</text>
</svg>
SVG;
            Storage::disk('public')->put("seed/{$category}.svg", $svg);
        }
    }

    private function seedComplaints(array $regions, array $users): array
    {
        $items = [
            [
                'title' => 'Lubang besar membahayakan pengendara di Jalan Asia Afrika',
                'description' => 'Terdapat lubang cukup dalam pada lajur kiri yang berisiko menyebabkan pengendara motor kehilangan keseimbangan, terutama saat hujan.',
                'category' => 'jalan', 'lat' => -6.9218460, 'lng' => 107.6071320,
                'address' => 'Jalan Asia Afrika, Braga, Sumur Bandung, Kota Bandung',
                'landmark' => 'Sekitar 80 meter dari Gedung Merdeka',
                'status' => 'approved', 'region' => $regions['bandung'], 'days' => 4,
            ],
            [
                'title' => 'Sampah menumpuk di dekat Pasar Kosambi',
                'description' => 'Tumpukan sampah meluber ke bahu jalan dan menimbulkan bau menyengat sejak dua hari terakhir. Kondisi ini mengganggu pejalan kaki dan pedagang sekitar.',
                'category' => 'kebersihan', 'lat' => -6.9171260, 'lng' => 107.6279500,
                'address' => 'Jalan Ahmad Yani, Kebon Pisang, Sumur Bandung, Kota Bandung',
                'landmark' => 'Pintu timur Pasar Kosambi',
                'status' => 'approved', 'region' => $regions['bandung'], 'days' => 3,
            ],
            [
                'title' => 'Lampu penerangan padam di sekitar Taman Lansia',
                'description' => 'Tiga titik lampu penerangan tidak menyala sehingga jalur pejalan kaki menjadi gelap pada malam hari dan mengurangi rasa aman pengunjung.',
                'category' => 'penerangan', 'lat' => -6.9026100, 'lng' => 107.6204700,
                'address' => 'Jalan Cisangkuy, Citarum, Bandung Wetan, Kota Bandung',
                'landmark' => 'Sisi utara Taman Lansia',
                'status' => 'approved', 'region' => $regions['bandung'], 'days' => 2,
            ],
            [
                'title' => 'Trotoar rusak di kawasan Dago',
                'description' => 'Beberapa ubin trotoar pecah dan terangkat sehingga menyulitkan pengguna kursi roda serta berpotensi membuat pejalan kaki tersandung.',
                'category' => 'lainnya', 'lat' => -6.8879200, 'lng' => 107.6131700,
                'address' => 'Jalan Ir. H. Juanda, Dago, Coblong, Kota Bandung',
                'landmark' => 'Depan pertokoan Dago',
                'status' => 'pending', 'region' => $regions['bandung'], 'days' => 1,
            ],
            [
                'title' => 'Genangan berulang di Jalan Pagarsih',
                'description' => 'Air menggenang setelah hujan sedang dan menutup sebagian lajur. Saluran drainase tampak tersumbat oleh sedimen dan sampah plastik.',
                'category' => 'jalan', 'lat' => -6.9258100, 'lng' => 107.5945300,
                'address' => 'Jalan Pagarsih, Cibadak, Astanaanyar, Kota Bandung',
                'landmark' => 'Dekat persimpangan Gang Nata',
                'status' => 'pending', 'region' => $regions['bandung'], 'days' => 9,
                'stale' => true,
            ],
            [
                'title' => 'Kontainer sampah penuh di Antapani',
                'description' => 'Kontainer sampah sudah penuh dan sebagian sampah tercecer ke jalan. Diperlukan pengangkutan agar tidak menghambat akses warga.',
                'category' => 'kebersihan', 'lat' => -6.9142400, 'lng' => 107.6618300,
                'address' => 'Jalan Terusan Jakarta, Antapani Tengah, Kota Bandung',
                'landmark' => 'Samping lapangan olahraga',
                'status' => 'approved', 'region' => $regions['bandung'], 'days' => 5,
            ],
            [
                'title' => 'Lampu jalan berkedip di Cibeureum Cimahi',
                'description' => 'Lampu jalan berkedip dan beberapa kali padam total. Kondisi malam cukup gelap karena titik lampu berikutnya berjarak jauh.',
                'category' => 'penerangan', 'lat' => -6.9068500, 'lng' => 107.5355400,
                'address' => 'Jalan Cibeureum, Cibeureum, Cimahi Selatan, Kota Cimahi',
                'landmark' => 'Dekat halte Cibeureum',
                'status' => 'approved', 'region' => $regions['cimahi'], 'days' => 6,
            ],
            [
                'title' => 'Marka penyeberangan memudar di Alun-alun Cimahi',
                'description' => 'Marka zebra cross hampir tidak terlihat sehingga pengendara sering terlambat memberi jalan kepada pejalan kaki.',
                'category' => 'jalan', 'lat' => -6.8723700, 'lng' => 107.5428700,
                'address' => 'Jalan Jenderal Amir Machmud, Cimahi Tengah, Kota Cimahi',
                'landmark' => 'Depan Alun-alun Cimahi',
                'status' => 'rejected', 'region' => $regions['cimahi'], 'days' => 10,
                'reason' => 'Foto tidak menunjukkan lokasi yang dapat diverifikasi dan pelapor belum memberikan bukti tambahan.',
            ],
            [
                'title' => 'Lubang kedua pada ruas Asia Afrika',
                'description' => 'Lubang lain terlihat pada ruas yang sama dan berada sangat dekat dengan laporan sebelumnya. Mohon dilakukan pemeriksaan menyeluruh.',
                'category' => 'jalan', 'lat' => -6.9219000, 'lng' => 107.6071900,
                'address' => 'Jalan Asia Afrika, Braga, Sumur Bandung, Kota Bandung',
                'landmark' => 'Dekat Gedung Merdeka',
                'status' => 'pending', 'region' => $regions['bandung'], 'days' => 0,
                'duplicate' => true,
            ],
            [
                'title' => 'Rambu petunjuk arah tertutup ranting pohon',
                'description' => 'Rambu petunjuk arah tertutup ranting sehingga sulit dibaca dari jarak aman. Pemangkasan ringan diperlukan untuk memulihkan visibilitas.',
                'category' => 'lainnya', 'lat' => -6.8958700, 'lng' => 107.6421300,
                'address' => 'Jalan PHH Mustofa, Neglasari, Cibeunying Kaler, Kota Bandung',
                'landmark' => 'Sebelum simpang Surapati',
                'status' => 'approved', 'region' => $regions['bandung'], 'days' => 7,
            ],
        ];

        $created = [];
        $firstComplaint = null;

        foreach ($items as $index => $item) {
            $user = $index % 3 === 0
                ? $users['citizen']
                : $users['otherCitizens'][$index % $users['otherCitizens']->count()];
            $status = $item['status'];
            $takenAt = now()->subDays($item['days'])->subHours(2);
            $path = 'seed/'.$item['category'].'.svg';
            $slug = Str::slug($item['title']).'-'.($index + 1);
            $complaint = Complaint::query()->firstOrNew(['slug' => $slug]);
            $complaint->fill([
                'user_id' => $user->id,
                'region_id' => $item['region']->id,
                'moderated_by' => $status === Complaint::STATUS_PENDING ? null : $users['admin']->id,
                'title' => $item['title'],
                'slug' => $slug,
                'description' => $item['description'],
                'category' => $item['category'],
                'latitude' => $item['lat'],
                'longitude' => $item['lng'],
                'gps_accuracy' => 7.5 + $index,
                'location_source' => 'browser_geolocation',
                'address_text' => $item['address'],
                'landmark' => $item['landmark'],
                'image_path' => $path,
                'image_original_name' => basename($path),
                'image_mime' => 'image/svg+xml',
                'image_size' => Storage::disk('public')->size($path),
                'image_hash' => hash('sha256', $item['title']),
                'image_taken_at' => $takenAt,
                'image_age_days' => $item['days'],
                'exif_is_stale' => (bool) ($item['stale'] ?? false),
                'status' => $status,
                'is_anonymous' => $index === 5,
                'is_duplicate_flag' => (bool) ($item['duplicate'] ?? false),
                'duplicate_of_id' => ($item['duplicate'] ?? false) ? $firstComplaint?->id : null,
                'rejected_reason' => $item['reason'] ?? null,
                'approved_at' => $status === Complaint::STATUS_APPROVED ? now()->subDays(max(0, $item['days'] - 1)) : null,
                'rejected_at' => $status === Complaint::STATUS_REJECTED ? now()->subDays(1) : null,
                'upvotes_count' => 0,
                'comments_count' => 0,
                'created_at' => now()->subDays($item['days']),
                'updated_at' => now()->subDays(max(0, $item['days'] - 1)),
            ]);
            $complaint->location = Complaint::pointExpression($item['lat'], $item['lng']);
            $complaint->save();

            if ($index === 0) {
                $firstComplaint = $complaint;
            }

            $created[] = $complaint;
        }

        return $created;
    }

    private function seedInteractions(array $complaints, array $users): void
    {
        $citizens = collect([$users['citizen']])->merge($users['otherCitizens']);

        foreach ($complaints as $index => $complaint) {
            if ($complaint->status !== Complaint::STATUS_APPROVED) {
                continue;
            }

            $voters = $citizens->take(($index % 4) + 2);
            foreach ($voters as $voter) {
                Upvote::query()->firstOrCreate([
                    'complaint_id' => $complaint->id,
                    'user_id' => $voter->id,
                ]);
            }

            $commentTexts = [
                'Saya melewati lokasi ini kemarin dan kondisinya memang perlu segera ditangani.',
                'Terima kasih sudah melaporkan. Semoga petugas dapat melakukan verifikasi lapangan.',
            ];

            foreach ($commentTexts as $commentIndex => $text) {
                Comment::query()->firstOrCreate(
                    [
                        'complaint_id' => $complaint->id,
                        'user_id' => $citizens[($index + $commentIndex) % $citizens->count()]->id,
                        'content' => $text,
                    ],
                    ['is_hidden' => false]
                );
            }

            $complaint->update([
                'upvotes_count' => $complaint->upvotes()->count(),
                'comments_count' => $complaint->comments()->where('is_hidden', false)->count(),
            ]);
        }
    }
}
