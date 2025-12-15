<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('bus_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['traffic_delay', 'mechanical_issue', 'accident', 'emergency', 'other'])->default('other');
            $table->string('title')->nullable();
            $table->text('description');
            $table->string('location')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->enum('status', ['reported', 'in_progress', 'resolved', 'closed'])->default('reported');
            $table->text('admin_response')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // Indexes for faster queries
            $table->index(['driver_id', 'created_at']);
            $table->index(['status', 'severity']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};

