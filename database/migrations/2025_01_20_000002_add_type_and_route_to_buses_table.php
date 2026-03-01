<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('buses', function (Blueprint $table) {
            // Add type column if it doesn't exist
            if (!Schema::hasColumn('buses', 'type')) {
                $table->enum('type', ['expressway', 'normal'])->default('normal')->after('status');
            }
            
            // Add route_id column if it doesn't exist
            if (!Schema::hasColumn('buses', 'route_id')) {
                $table->foreignId('route_id')->nullable()->after('type')->constrained()->onDelete('set null');
            }
            
            // Add driver_id column if it doesn't exist (for backward compatibility)
            if (!Schema::hasColumn('buses', 'driver_id')) {
                $table->foreignId('driver_id')->nullable()->after('route_id')->constrained('users')->onDelete('set null');
            }

            // Live GPS tracking fields
            if (!Schema::hasColumn('buses', 'current_latitude')) {
                $table->decimal('current_latitude', 10, 7)->nullable()->after('driver_id');
            }
            if (!Schema::hasColumn('buses', 'current_longitude')) {
                $table->decimal('current_longitude', 10, 7)->nullable()->after('current_latitude');
            }
            if (!Schema::hasColumn('buses', 'last_location_update')) {
                $table->timestamp('last_location_update')->nullable()->after('current_longitude');
            }
        });
    }

    public function down()
    {
        Schema::table('buses', function (Blueprint $table) {
            if (Schema::hasColumn('buses', 'last_location_update')) {
                $table->dropColumn('last_location_update');
            }
            if (Schema::hasColumn('buses', 'current_longitude')) {
                $table->dropColumn('current_longitude');
            }
            if (Schema::hasColumn('buses', 'current_latitude')) {
                $table->dropColumn('current_latitude');
            }
            if (Schema::hasColumn('buses', 'driver_id')) {
                $table->dropForeign(['driver_id']);
                $table->dropColumn('driver_id');
            }
            if (Schema::hasColumn('buses', 'route_id')) {
                $table->dropForeign(['route_id']);
                $table->dropColumn('route_id');
            }
            if (Schema::hasColumn('buses', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};

