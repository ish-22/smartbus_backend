<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            // Make route_number nullable and remove unique constraint temporarily
            if (Schema::hasColumn('routes', 'route_number')) {
                $table->string('route_number')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            if (Schema::hasColumn('routes', 'route_number')) {
                $table->string('route_number')->nullable(false)->change();
            }
        });
    }
};

