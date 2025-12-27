<?php

namespace App\Http\Resources;

use App\Utils\Enums\LoginMethods;
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
            /**
             * IP Address the login was made from.
             */
            'ip_address' => $this->ip_address,

            /**
             * User-Agent header of the login request.
             */
            'user_agent' => $this->user_agent,

            /**
             * Method used for the login (e.g., email, OAuth)
             * @var LoginMethods
             */
            'login_method' => $this->login_method,

            /**
             * Location where the login was made from.
             */
            'location' => $this->location,

            /**
             * Date and time when the login was performed.
             */
            'performed_at' => $this->created_at
        ];
    }
}
