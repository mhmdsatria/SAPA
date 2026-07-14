<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('category', ['jalan', 'kebersihan', 'penerangan', 'lainnya'])->index();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('gps_accuracy', 8, 2)->nullable();
            $table->string('location_source', 40)->default('browser_geolocation');
            $table->string('address_text', 1000);
            $table->string('landmark', 500)->nullable();
            $table->string('image_path');
            $table->string('image_original_name')->nullable();
            $table->string('image_mime', 100)->nullable();
            $table->unsignedBigInteger('image_size')->nullable();
            $table->string('image_hash', 64)->nullable()->index();
            $table->timestamp('image_taken_at')->nullable()->index();
            $table->unsignedInteger('image_age_days')->nullable();
            $table->boolean('exif_is_stale')->default(false)->index();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->boolean('is_anonymous')->default(false)->index();
            $table->boolean('is_duplicate_flag')->default(false)->index();
            $table->foreignId('duplicate_of_id')->nullable()->constrained('complaints')->nullOnDelete();
            $table->string('rejected_reason', 1000)->nullable();
            $table->timestamp('approved_at')->nullable()->index();
            $table->timestamp('rejected_at')->nullable()->index();
            $table->unsignedInteger('upvotes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['category', 'status']);
            $table->index(['region_id', 'status']);
            $table->index(['latitude', 'longitude']);
        });

        DB::statement('ALTER TABLE complaints ADD location POINT NOT NULL SRID 4326 AFTER longitude');
        DB::statement('CREATE SPATIAL INDEX complaints_location_spatial_index ON complaints (location)');
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
