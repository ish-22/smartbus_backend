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
            if (Schema::hasColumn('routes', 'start_location')) {
                $table->string('start_location')->nullable()->change();
            }
            if (Schema::hasColumn('routes', 'end_location')) {
                $table->string('end_location')->nullable()->change();
            }
            if (Schema::hasColumn('routes', 'distance')) {
                $table->decimal('distance', 8, 2)->nullable()->change();
            }
            if (Schema::hasColumn('routes', 'fare')) {
                $table->decimal('fare', 8, 2)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            if (Schema::hasColumn('routes', 'start_location')) {
                $table->string('start_location')->nullable(false)->change();
            }
            if (Schema::hasColumn('routes', 'end_location')) {
                $table->string('end_location')->nullable(false)->change();
            }
            if (Schema::hasColumn('routes', 'distance')) {
                $table->decimal('distance', 8, 2)->nullable(false)->change();
            }
            if (Schema::hasColumn('routes', 'fare')) {
                $table->decimal('fare', 8, 2)->nullable(false)->change();
            }
        });
    }
};
