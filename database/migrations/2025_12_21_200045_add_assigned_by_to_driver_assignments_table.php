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
        Schema::table('driver_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_assignments', 'assigned_by')) {
                $table->foreignId('assigned_by')->nullable()->after('bus_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('driver_assignments', 'assignment_date')) {
                $table->date('assignment_date')->after('assigned_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('driver_assignments', 'assigned_by')) {
                $table->dropForeign(['assigned_by']);
                $table->dropColumn('assigned_by');
            }
            if (Schema::hasColumn('driver_assignments', 'assignment_date')) {
                $table->dropColumn('assignment_date');
            }
        });
    }
};
