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
            /**
             * Google's account name.
             * @var string
             */
            'name' => $this->google_name,

            /**
             * Google's account email. If email is the same as the email of the user's account in the database - then it is considered and immediately marked as verified.
             * @var string
             */
            'email' => $this->google_email,

            /**
             * Google's account nickname.
             * @var string
             */
            'nickname' => $this->google_nickname,

            /**
             * Google's account avatar URL.
             * @var string
             */
            'avatar_url' => $this->google_avatar_url
        ];
    }
}
