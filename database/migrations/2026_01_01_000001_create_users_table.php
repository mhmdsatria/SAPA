<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('region_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone', 24)->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->enum('role', ['masyarakat', 'super_admin', 'admin_daerah'])->default('masyarakat')->index();
            $table->string('provider')->nullable()->index();
            $table->string('provider_id')->nullable()->index();
            $table->string('avatar_url')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
