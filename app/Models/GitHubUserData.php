<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, string $operator, mixed $value)
 * @method static updateOrCreate(array $array, array $array1)
 */
class GitHubUserData extends Model {
    protected $table = 'github_user_data';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'id',
        'github_login',
        'github_avatar_url',
        'github_gravatar_id',
        'github_url',
        'github_html_url',
        'github_followers_url',
        'github_following_url',
        'github_gists_url',
        'github_starred_url',
        'github_subscriptions_url',
        'github_organizations_url',
        'github_repos_url',
        'github_events_url',
        'github_received_events_url',
        'github_type',
        'github_user_view_type',
        'github_site_admin',
        'github_name',
        'github_company',
        'github_blog',
        'github_location',
        'github_email',
        'github_hireable',
        'github_bio',
        'github_twitter_username',
        'github_notification_email',
        'public_repos',
        'public_gists',
        'public_followers',
        'public_following',
        'github_token',
        'github_token_expires_in',
        'github_refresh_token'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
