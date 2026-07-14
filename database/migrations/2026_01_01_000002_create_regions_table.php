<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('name');
            $table->string('code', 32)->unique();
            $table->enum('level', ['provinsi', 'kota', 'kabupaten', 'kecamatan', 'kelurahan'])->index();
            $table->decimal('center_latitude', 10, 7);
            $table->decimal('center_longitude', 10, 7);
            $table->json('boundary_geojson');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE regions ADD boundary POLYGON NOT NULL SRID 4326 AFTER center_longitude');
        DB::statement('CREATE SPATIAL INDEX regions_boundary_spatial_index ON regions (boundary)');

        Schema::table('users', function (Blueprint $table): void {
            $table->foreign('region_id')->references('id')->on('regions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['region_id']);
        });
        Schema::dropIfExists('regions');
    }
};
