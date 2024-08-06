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
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('super_key')->after('gender');
            $table->string('unique_id')->after('super_key');
            $table->integer('rate')->after('unique_id')->nullable();
            $table->integer('successful_rides')->after('rate')->nullable();

            $table->unique(['super_key','unique_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('super_key');
            $table->dropColumn('unique_id');
        });
    }
};
