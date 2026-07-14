<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table): void {
                $table->id();
                $table->string('slug', 80)->unique();
                $table->string('name', 120);
                $table->string('description', 500)->nullable();
                $table->string('color', 7)->default('#2563eb');
                $table->string('icon', 60)->default('tag');
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedSmallInteger('sort_order')->default(0)->index();
                $table->timestamps();
            });
        }

        if (DB::table('categories')->count() === 0) {
            $now = now();
            DB::table('categories')->insert([
                ['slug' => 'jalan', 'name' => 'Jalan Raya', 'description' => 'Kerusakan jalan, drainase, trotoar, dan marka.', 'color' => '#ef4444', 'icon' => 'road', 'is_active' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
                ['slug' => 'kebersihan', 'name' => 'Kebersihan', 'description' => 'Sampah, limbah, dan kebersihan fasilitas publik.', 'color' => '#10b981', 'icon' => 'trash', 'is_active' => true, 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
                ['slug' => 'penerangan', 'name' => 'Penerangan', 'description' => 'Lampu jalan dan penerangan ruang publik.', 'color' => '#f59e0b', 'icon' => 'lightbulb', 'is_active' => true, 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
                ['slug' => 'lainnya', 'name' => 'Lainnya', 'description' => 'Keluhan fasilitas publik lainnya.', 'color' => '#6366f1', 'icon' => 'circle-dot', 'is_active' => true, 'sort_order' => 40, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
