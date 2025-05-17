<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLoginActivityResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'login_method' => $this->login_method,
            'location' => $this->location,
            'performed_at' => $this->created_at
        ];
    }
}
