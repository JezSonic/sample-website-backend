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
        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('email');
            $table->index('email_verification_token');
            $table->index('password_reset_token');
        });

        // Add indexes to user_login_activities table
        Schema::table('user_login_activities', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('login_method');
        });

        // Add indexes to users_profile_settings table
        Schema::table('users_profile_settings', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('is_public');
        });

        // Add indexes to google_user_data table
        Schema::table('google_user_data', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('google_email');
            $table->index('google_token');
            $table->index('google_refresh_token');
        });

        // Add indexes to github_user_data table
        Schema::table('github_user_data', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('github_login');
            $table->index('github_email');
            $table->index('github_token');
            $table->index('github_refresh_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['email_verification_token']);
            $table->dropIndex(['password_reset_token']);
        });

        // Remove indexes from user_login_activities table
        Schema::table('user_login_activities', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['login_method']);
        });

        // Remove indexes from users_profile_settings table
        Schema::table('users_profile_settings', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['is_public']);
        });

        // Remove indexes from google_user_data table
        Schema::table('google_user_data', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['google_email']);
            $table->dropIndex(['google_token']);
            $table->dropIndex(['google_refresh_token']);
        });

        // Remove indexes from github_user_data table
        Schema::table('github_user_data', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['github_login']);
            $table->dropIndex(['github_email']);
            $table->dropIndex(['github_token']);
            $table->dropIndex(['github_refresh_token']);
        });
    }
};
