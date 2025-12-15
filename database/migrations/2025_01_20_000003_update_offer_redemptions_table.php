<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if unique constraint already exists by trying to add it
        // If it fails, it means it already exists (which is fine)
        try {
            Schema::table('offer_redemptions', function (Blueprint $table) {
                $table->unique(['user_id', 'offer_id'], 'offer_redemptions_user_offer_unique');
            });
        } catch (\Exception $e) {
            // Constraint might already exist, which is fine
            // Just continue
        }
    }

    public function down(): void
    {
        Schema::table('offer_redemptions', function (Blueprint $table) {
            $table->dropUnique('offer_redemptions_user_offer_unique');
        });
    }
};

