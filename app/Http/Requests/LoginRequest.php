<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array {
        return [
            /**
             * Email address of the user logging in to the application.
             */
            'email' => 'required|string|email|max:255',

            /**
             * Password of the user logging in to the application.
             */
            'password' => 'required|string|min:6',

            /**
             * IP address of the user logging in to the application.
             */
            'ip_address' => 'required|string|ip',

            /**
             * Two-factor authentication code provided by the user. Required only if two-factor authentication is enabled in the user's account.
             */
            'two_factor_code' => 'nullable|string'
        ];
    }
}
