<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table): void {
            if (! Schema::hasColumn('complaints', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('category')->constrained('categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('complaints', 'geocoded_address')) {
                $table->string('geocoded_address', 1000)->nullable()->after('address_text');
            }
            if (! Schema::hasColumn('complaints', 'address_is_edited')) {
                $table->boolean('address_is_edited')->default(false)->after('geocoded_address')->index();
            }
            if (! Schema::hasColumn('complaints', 'last_edited_at')) {
                $table->timestamp('last_edited_at')->nullable()->after('rejected_at')->index();
            }
            if (! Schema::hasColumn('complaints', 'edit_count')) {
                $table->unsignedInteger('edit_count')->default(0)->after('last_edited_at');
            }
        });

        DB::statement('UPDATE complaints c JOIN categories cat ON cat.slug = c.category SET c.category_id = cat.id WHERE c.category_id IS NULL');
        DB::statement('UPDATE complaints SET geocoded_address = address_text WHERE geocoded_address IS NULL');
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table): void {
            if (Schema::hasColumn('complaints', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }
            $columns = array_values(array_filter([
                Schema::hasColumn('complaints', 'geocoded_address') ? 'geocoded_address' : null,
                Schema::hasColumn('complaints', 'address_is_edited') ? 'address_is_edited' : null,
                Schema::hasColumn('complaints', 'last_edited_at') ? 'last_edited_at' : null,
                Schema::hasColumn('complaints', 'edit_count') ? 'edit_count' : null,
            ]));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
