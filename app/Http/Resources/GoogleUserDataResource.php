<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoogleUserDataResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'name' => $this->google_name,
            'email' => $this->google_email,
            'nickname' => $this->google_nickname,
            'avatar_url' => $this->google_avatar_url
        ];
    }
}
