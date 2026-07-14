<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hidden_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('content');
            $table->boolean('is_hidden')->default(false)->index();
            $table->timestamp('hidden_at')->nullable();
            $table->timestamps();

            $table->index(['complaint_id', 'is_hidden', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
