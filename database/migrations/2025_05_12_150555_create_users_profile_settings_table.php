<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('users_profile_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('theme')->default('dark');
            $table->string('language')->default('en');
            $table->string('timezone')->default('Europe/London');
            $table->string('avatar_source')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('email_notifications')->default(false);
            $table->boolean('email_marketing')->default(false);
            $table->boolean('email_security_alerts')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('users_profile_settings');
    }
};
