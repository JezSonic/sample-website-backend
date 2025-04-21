<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GitHubUserDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'login' => $this->github_login,
            'avatar_url' => $this->github_avatar_url,
            'gravatar_id' => $this->github_gravatar_id,
            'url' => $this->github_url,
            'html_url' => $this->github_html_url,
            'followers_url' => $this->github_followers_url,
            'following_url' => $this->github_following_url,
            'gists_url' => $this->github_gists_url,
            'starred_url' => $this->github_starred_url,
            'subscriptions_url' => $this->github_subscriptions_url,
            'organizations_url' => $this->github_organizations_url,
            'repos_url' => $this->github_repos_url,
            'events_url' => $this->github_events_url,
            'received_events_url' => $this->github_received_events_url,
            'type' => $this->github_type,
            'user_view_type' => $this->user_view_type,
            /**
             * @phpstan-type bool
             */
            'site_admin' => $this->github_site_admin,
            'name' => $this->github_name,
            'company' => $this->github_company,
            'blog' => $this->github_blog,
            'location' => $this->github_location,
            'email' => $this->github_email,
            /**
             * @phpstan-type bool
             */
            'hireable' => $this->github_hireable,
            'bio' => $this->github_bio,
            'twitter_username' => $this->github_twitter_username,
            'notification_email' => $this->github_notification_email,
            /**
             * @phpstan-type int
             */
            'public_repos' => $this->public_repos,
            /**
             * @phpstan-type int
             */
            'public_gists' => $this->public_gists,
            /**
             * @phpstan-type int
             */
            'followers' => $this->public_followers,
            /**
             * @phpstan-type int
             */
            'following' => $this->public_following,
        ];
    }
}
