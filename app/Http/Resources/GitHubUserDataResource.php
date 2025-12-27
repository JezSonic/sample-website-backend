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
            /**
             * @var string
             * GitHub login of the user.
             */
            'login' => $this->github_login,

            /**
             * @var string
             * URL of the user's GitHub avatar.
             */
            'avatar_url' => $this->github_avatar_url,

            /**
             * @var string
             * Gravatar ID of the user.
             */
            'gravatar_id' => $this->github_gravatar_id,

            /**
             * @var string
             * URL to user's GitHub profile.
             */
            'url' => $this->github_url,

            /**
             * @var bool
             * Whether the user is a marked as a "Site admin".
             */
            'site_admin' => $this->github_site_admin,

            /**
             * @var string|null
             * Name of the user.
             */
            'name' => $this->github_name,

            /**
             * @var string|null
             * Company the user works for
             */
            'company' => $this->github_company,

            /**
             * @var string|null
             * Blog URL of the user.
             */
            'blog' => $this->github_blog,

            /**
             * @var string|null
             * Location of the user.
             */
            'location' => $this->github_location,

            /**
             * @var string
             * Email of the user. If email is the same as the email of the user's account in the database - then it is considered and immediately marked as verified.
             */
            'email' => $this->github_email,

            /**
             * @var bool
             * Whether the user is marked as "hireable".
             */
            'hireable' => $this->github_hireable,

            /**
             * Biography of the user.
             * @var string|null
             */
            'bio' => $this->github_bio,

            /**
             * Twitter username of the user (applies to a Twitter account connected to the GitHub account).
             * @var string|null
             */
            'twitter_username' => $this->github_twitter_username,

            /**
             * Email used for GitHub notifications.
             * @var string
             */
            'notification_email' => $this->github_notification_email,

            /**
             * @var int
             * Number of public repositories.
             */
            'public_repos' => $this->public_repos,

            /**
             * @var int
             * Number of public gists.
             */
            'public_gists' => $this->public_gists,

            /**
             * @var int
             * Number of followers.
             */
            'followers' => $this->public_followers,

            /**
             * @var int
             * Number of users the authenticated user is following.
             */
            'following' => $this->public_following,
        ];
    }
}
