<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('routes', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('routes', 'start_point')) {
                $table->string('start_point')->nullable()->after('name');
            }
            if (!Schema::hasColumn('routes', 'end_point')) {
                $table->string('end_point')->nullable()->after('start_point');
            }
            if (!Schema::hasColumn('routes', 'metadata')) {
                $table->json('metadata')->nullable()->after('end_point');
            }
        });

        // Migrate existing data if columns exist
        if (Schema::hasColumn('routes', 'route_number')) {
            DB::statement('UPDATE routes SET name = route_number WHERE name IS NULL');
        }
        if (Schema::hasColumn('routes', 'start_location')) {
            DB::statement('UPDATE routes SET start_point = start_location WHERE start_point IS NULL');
        }
        if (Schema::hasColumn('routes', 'end_location')) {
            DB::statement('UPDATE routes SET end_point = end_location WHERE end_point IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            if (Schema::hasColumn('routes', 'metadata')) {
                $table->dropColumn('metadata');
            }
            if (Schema::hasColumn('routes', 'end_point')) {
                $table->dropColumn('end_point');
            }
            if (Schema::hasColumn('routes', 'start_point')) {
                $table->dropColumn('start_point');
            }
            if (Schema::hasColumn('routes', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
