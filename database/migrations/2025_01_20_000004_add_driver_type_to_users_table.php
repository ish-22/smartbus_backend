<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add driver_type column if it doesn't exist
            if (!Schema::hasColumn('users', 'driver_type')) {
                $table->enum('driver_type', ['expressway', 'normal'])->nullable()->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'driver_type')) {
                $table->dropColumn('driver_type');
            }
        });
    }
};

