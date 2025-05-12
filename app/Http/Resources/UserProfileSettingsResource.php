<?php

namespace App\Http\Resources;

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
            'theme' => $this->theme,
            'language' => $this->language,
            'timezone' => $this->timezone,
            'avatar_source' => $this->avatar_source,
            'is_public' => boolval($this->is_public),
            'notifications' => [
                'email_notifications' => boolval($this->email_notifications),
                'email_marketing' => boolval($this->email_marketing),
                'email_security_alerts' => boolval($this->email_security_alerts)
            ]
        ];
    }
}
