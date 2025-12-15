<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('driver_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('bus_id');
            $table->enum('driver_type', ['expressway', 'normal'])->default('normal');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('bus_id')->references('id')->on('buses')->onDelete('cascade');
            
            // Index for faster queries
            $table->index(['driver_id', 'assigned_at']);
            $table->index(['bus_id', 'assigned_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('driver_assignments');
    }
};

