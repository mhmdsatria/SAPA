<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ComplaintFactory extends Factory
{
    protected $model = Complaint::class;

    public function definition(): array
    {
        $latitude = fake()->latitude(-6.98, -6.82);
        $longitude = fake()->longitude(107.52, 107.72);
        $title = fake()->sentence(6);

        return [
            'user_id' => User::factory(),
            'region_id' => null,
            'moderated_by' => null,
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(7)),
            'description' => fake()->paragraph(2),
            'category' => fake()->randomElement([
                Complaint::CATEGORY_JALAN,
                Complaint::CATEGORY_KEBERSIHAN,
                Complaint::CATEGORY_PENERANGAN,
                Complaint::CATEGORY_LAINNYA,
            ]),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location' => Complaint::pointExpression($latitude, $longitude),
            'gps_accuracy' => fake()->randomFloat(2, 3, 35),
            'location_source' => 'browser_geolocation',
            'address_text' => fake()->streetAddress().', Bandung, Jawa Barat',
            'landmark' => fake()->optional()->sentence(4),
            'image_path' => 'seed/complaint-placeholder.svg',
            'image_original_name' => 'complaint-placeholder.svg',
            'image_mime' => 'image/svg+xml',
            'image_size' => 1024,
            'image_hash' => hash('sha256', Str::uuid()->toString()),
            'image_taken_at' => now()->subDays(fake()->numberBetween(0, 10)),
            'image_age_days' => fake()->numberBetween(0, 10),
            'exif_is_stale' => false,
            'status' => Complaint::STATUS_PENDING,
            'is_anonymous' => fake()->boolean(20),
            'is_duplicate_flag' => false,
            'duplicate_of_id' => null,
            'rejected_reason' => null,
            'approved_at' => null,
            'rejected_at' => null,
            'upvotes_count' => 0,
            'comments_count' => 0,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'status' => Complaint::STATUS_APPROVED,
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'status' => Complaint::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejected_reason' => 'Informasi lokasi atau bukti tidak dapat diverifikasi.',
        ]);
    }
}
