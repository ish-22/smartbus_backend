<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('offer_redemptions')) {
            Schema::create('offer_redemptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('offer_id')->constrained()->onDelete('cascade');
                $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
                $table->decimal('discount_amount', 10, 2);
                $table->enum('status', ['used', 'expired'])->default('used');
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_redemptions');
    }
};