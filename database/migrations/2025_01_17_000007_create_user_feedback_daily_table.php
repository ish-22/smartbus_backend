<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_feedback_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('feedback_date');
            $table->timestamps();
            
            $table->unique(['user_id', 'feedback_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_feedback_daily');
    }
};