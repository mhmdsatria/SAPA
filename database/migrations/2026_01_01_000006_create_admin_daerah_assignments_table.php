<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_daerah_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'region_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_daerah_assignments');
    }
};
