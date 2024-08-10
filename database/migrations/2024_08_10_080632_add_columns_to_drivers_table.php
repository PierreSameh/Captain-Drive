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
            $table->renameColumn('status','social_status');
            $table->string('status')->default('offline')->after('gender');
            $table->string('lng')->nullable()->after('status');
            $table->string('lat')->nullable()->after('lng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->renameColumn('social_status','status');
            $table->dropColumn('status');
            $table->dropColumn('lng');
            $table->dropColumn('lat');
        });
    }
};
