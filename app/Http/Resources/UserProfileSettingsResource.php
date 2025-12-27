<?php

namespace App\Http\Resources;

use App\Utils\Enums\UserAvatarSource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileSettingsResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            /** Preferred UI theme */
            'theme' => $this->theme,
            /** Preferred language */
            'language' => $this->language,
            /** Preferred timezone */
            'timezone' => $this->timezone,
            /** Avatar source for the user
             * @var UserAvatarSource
             */
            'avatar_source' => ($this->avatar_source ?? UserAvatarSource::Default),
            /** Whether the user's profile is publicly visible */
            'is_public' => boolval($this->is_public),
            /** User's notification settings */
            'notifications' => [
                /** Whether the user wants to receive email notifications */
                'email_notifications' => boolval($this->email_notifications),
                /** Whether the user wants to receive email marketing notifications */
                'email_marketing' => boolval($this->email_marketing),
                /** Whether the user wants to receive email security alerts notifications */
                'email_security_alerts' => boolval($this->email_security_alerts)
            ]
        ];
    }
}
