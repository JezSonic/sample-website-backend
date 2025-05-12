<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileSettingsUpdateRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'avatar_source' => 'required|string',
            'is_public' => 'required|boolean',
            'language' => 'required|string',
            'theme' => 'required|string',
            'name' => 'required|string',
            'notifications' => 'required|array',
            'notifications.email_notifications' => 'required|boolean',
            'notifications.email_marketing' => 'required|boolean',
            'notifications.email_security_alerts' => 'required|boolean',
        ];
    }
}
