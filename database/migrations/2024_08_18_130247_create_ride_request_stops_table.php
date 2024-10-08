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
        Schema::create('ride_request_stops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ride_request_id');
            $table->string('stop_location');
            $table->string('lng');
            $table->string('lat');
            $table->timestamps();

            $table->foreign('ride_request_id')->references('id')->on('ride_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_request_stops');
    }
};
