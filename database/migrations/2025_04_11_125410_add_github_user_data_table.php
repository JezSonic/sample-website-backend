<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('github_user_data', function (Blueprint $table) {
            $table->id('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('id')->nullable();
            $table->string('github_login')->nullable();
            $table->string('github_avatar_url')->nullable();
            $table->string('github_gravatar_id')->nullable();
            $table->string('github_url')->nullable();
            $table->string('github_html_url')->nullable();
            $table->string('github_followers_url')->nullable();
            $table->string('github_following_url')->nullable();
            $table->string('github_gists_url')->nullable();
            $table->string('github_starred_url')->nullable();
            $table->string('github_subscriptions_url')->nullable();
            $table->string('github_organizations_url')->nullable();
            $table->string('github_repos_url')->nullable();
            $table->string('github_events_url')->nullable();
            $table->string('github_received_events_url')->nullable();
            $table->string('github_type')->nullable();
            $table->string('github_user_view_type')->nullable();
            $table->boolean('github_site_admin')->nullable()->default(false);
            $table->string('github_name')->nullable();
            $table->string('github_company')->nullable();
            $table->string('github_blog')->nullable();
            $table->string('github_location')->nullable();
            $table->string('github_email')->nullable();
            $table->boolean('github_hireable')->nullable()->default(false);
            $table->longText('github_bio')->nullable();
            $table->string('github_twitter_username')->nullable();
            $table->string('github_notification_email')->nullable();
            $table->integer('public_repos')->nullable()->default(0);
            $table->integer('public_gists')->nullable()->default(0);
            $table->integer('public_followers')->nullable()->default(0);
            $table->integer('public_following')->nullable()->default(0);
            $table->string('github_token')->nullable();
            $table->string('github_refresh_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
    }
};
