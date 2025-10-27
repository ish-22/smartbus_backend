<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('start_point')->nullable();
            $table->string('end_point')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->enum('type',['expressway','normal'])->default('normal');
            $table->foreignId('route_id')->nullable()->constrained('routes');
            $table->integer('capacity')->default(50);
            $table->foreignId('driver_id')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes');
            $table->string('name');
            $table->decimal('lat',10,7)->nullable();
            $table->decimal('lng',10,7)->nullable();
            $table->integer('sequence')->default(0);
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('bus_id')->constrained('buses');
            $table->foreignId('route_id')->nullable()->constrained('routes');
            $table->string('seat_number')->nullable();
            $table->string('ticket_category')->nullable();
            $table->enum('status',['pending','confirmed','cancelled'])->default('pending');
            $table->decimal('total_amount',10,2)->default(0);
            $table->string('payment_method')->default('pay_on_bus');
            $table->timestamps();
        });

        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('bus_id')->nullable()->constrained('buses');
            $table->tinyInteger('rating')->default(5);
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('bookings');
            $table->decimal('amount',10,2)->default(0);
            $table->enum('status',['pending','completed','failed'])->default('pending');
            $table->string('gateway')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('stops');
        Schema::dropIfExists('buses');
        Schema::dropIfExists('routes');
    }
};
