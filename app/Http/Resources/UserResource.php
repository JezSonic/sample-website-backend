<?php

namespace App\Http\Resources;

use App\Models\GitHubUserData;
use App\Models\GoogleUserData;
use App\Models\UserProfileSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $google_data = GoogleUserData::where('user_id', '=', $this->id)->first();
        $github_data = GitHubUserData::where('user_id', '=', $this->id)->first();
        $settings_data = UserProfileSettings::where('user_id', '=', $this->id)->first();
        return [
            /** User ID */
            'id' => $this->id,
            /** User's full name */
            'name' => $this->name,
            /** User's email address */
            'email' => $this->email,
            /** User's email address verification timestamp */
            'email_verified_at' => $this->email_verified_at,
            /** Date and time when the account was created */
            'created_at' => $this->created_at,
            /** Date and time when the account was last updated */
            'updated_at' => $this->updated_at,
            /** Data for a connected Google account */
            'google' => new GoogleUserDataResource($this->whenNotNull($google_data)),
            /** Data for a connected GitHub account */
            'github' => new GitHubUserDataResource($this->whenNotNull($github_data)),
            /** User profile settings */
            'profile_settings' => new UserProfileSettingsResource($this->whenNotNull($settings_data)),
            /** Whether the user has a password set */
            'has_password' => !($this->getSalt() == null),
            /** Whether two-factor authentication is enabled for the user */
            'has_two_factor_enabled' => $this->hasTwoFactorEnabled(),
        ];
    }
}
