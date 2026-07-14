<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('complaint_media')) {
            return;
        }

        Schema::create('complaint_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->enum('media_type', ['image', 'video'])->index();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('sha256', 64)->nullable()->index();
            $table->timestamp('taken_at')->nullable()->index();
            $table->unsignedInteger('age_days')->nullable();
            $table->boolean('is_stale')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['complaint_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_media');
    }
};
