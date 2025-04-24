<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('github_user_data', function (Blueprint $table) {
            $table->integer('github_token_expires_in')->after('github_token')->nullable();
        });
        Schema::table('google_user_data', function (Blueprint $table) {
            $table->integer('google_token_expires_in')->after('google_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
    }
};
