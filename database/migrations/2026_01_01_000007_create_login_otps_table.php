<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_otps', function (Blueprint $table): void {
            $table->id();
            $table->string('identifier', 190)->index();
            $table->enum('channel', ['email', 'whatsapp'])->index();
            $table->string('code_hash');
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['identifier', 'channel', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_otps');
    }
};
