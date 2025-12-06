<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bus_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('route_id')->nullable()->constrained()->onDelete('set null');
            $table->string('subject');
            $table->text('message');
            $table->enum('type', ['complaint', 'suggestion', 'praise', 'general'])->default('general');
            $table->integer('rating')->nullable(); // 1-5 rating (optional)
            $table->enum('status', ['pending', 'reviewed', 'resolved', 'rejected'])->default('pending');
            $table->text('admin_response')->nullable(); // Admin's response to feedback
            $table->foreignId('responded_by')->nullable()->constrained('users')->onDelete('set null'); // Admin who responded
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('user_id');
            $table->index('status');
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedback');
    }
};
