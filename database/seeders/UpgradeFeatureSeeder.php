<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Complaint;
use Illuminate\Database\Seeder;

class UpgradeFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['slug' => 'jalan', 'name' => 'Jalan Raya', 'color' => '#ef4444', 'icon' => 'road', 'sort_order' => 10],
            ['slug' => 'kebersihan', 'name' => 'Kebersihan', 'color' => '#10b981', 'icon' => 'trash', 'sort_order' => 20],
            ['slug' => 'penerangan', 'name' => 'Penerangan', 'color' => '#f59e0b', 'icon' => 'lightbulb', 'sort_order' => 30],
            ['slug' => 'lainnya', 'name' => 'Lainnya', 'color' => '#6366f1', 'icon' => 'circle-dot', 'sort_order' => 40],
        ];

        foreach ($defaults as $item) {
            Category::query()->updateOrCreate(['slug' => $item['slug']], [...$item, 'is_active' => true]);
        }

        Complaint::query()->with('media')->chunkById(100, function ($complaints): void {
            foreach ($complaints as $complaint) {
                $category = Category::query()->where('slug', $complaint->category)->first();
                if ($category && ! $complaint->category_id) {
                    $complaint->update(['category_id' => $category->id]);
                }
                if ($complaint->media->isEmpty() && $complaint->image_path) {
                    $complaint->media()->create([
                        'media_type' => str_starts_with((string) $complaint->image_mime, 'video/') ? 'video' : 'image',
                        'path' => $complaint->image_path,
                        'original_name' => $complaint->image_original_name,
                        'mime_type' => $complaint->image_mime ?: 'image/webp',
                        'size_bytes' => $complaint->image_size ?: 0,
                        'sha256' => $complaint->image_hash,
                        'taken_at' => $complaint->image_taken_at,
                        'age_days' => $complaint->image_age_days,
                        'is_stale' => $complaint->exif_is_stale,
                        'sort_order' => 0,
                    ]);
                }
            }
        });
    }
}
