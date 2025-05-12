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
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'google' => new GoogleUserDataResource($this->whenNotNull($google_data)),
            'github' => new GitHubUserDataResource($this->whenNotNull($github_data)),
            'profile_settings' => new UserProfileSettingsResource($this->whenNotNull($settings_data)),
        ];
    }
}
