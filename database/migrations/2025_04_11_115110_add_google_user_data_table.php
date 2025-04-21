<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('google_user_data', function (Blueprint $table) {
            $table->id('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('id')->nullable();
            $table->string('google_token')->nullable();
            $table->string('google_refresh_token')->nullable();
            $table->string('google_nickname')->nullable();
            $table->string('google_name')->nullable();
            $table->string('google_email')->nullable();
            $table->string('google_avatar_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
    }
};
