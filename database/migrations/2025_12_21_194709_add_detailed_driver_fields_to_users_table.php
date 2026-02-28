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
        Schema::table('users', function (Blueprint $table) {
            // Driver-specific detailed fields
            $table->string('license_number')->nullable()->after('driver_type');
            $table->date('license_expiry_date')->nullable()->after('license_number');
            $table->text('address')->nullable()->after('license_expiry_date');
            $table->string('nic_number')->nullable()->after('address');
            $table->date('date_of_birth')->nullable()->after('nic_number');
            $table->string('emergency_contact_name')->nullable()->after('date_of_birth');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->integer('experience_years')->nullable()->after('emergency_contact_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'license_number',
                'license_expiry_date',
                'address',
                'nic_number',
                'date_of_birth',
                'emergency_contact_name',
                'emergency_contact_phone',
                'experience_years'
            ]);
        });
    }
};
