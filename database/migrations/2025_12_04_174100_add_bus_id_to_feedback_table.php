<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('feedback', function (Blueprint $table) {
            // Add bus_id column if it doesn't exist
            if (!Schema::hasColumn('feedback', 'bus_id')) {
                $table->foreignId('bus_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('feedback', function (Blueprint $table) {
            if (Schema::hasColumn('feedback', 'bus_id')) {
                $table->dropForeign(['bus_id']);
                $table->dropColumn('bus_id');
            }
        });
    }
};

