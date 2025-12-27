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
            /**
             * Avatar source for the user
             */
            'avatar_source' => 'required|in:google,github,default',

            /**
             * Whether the user's profile is publicly visible
             */
            'is_public' => 'required|boolean',

            /**
             * User's preferred UI language
             */
            'language' => 'required|string',

            /**
             * User's preferred UI theme
             */
            'theme' => 'required|string',

            /**
             * User's account name
             */
            'name' => 'nullable|string',

            /**
             * User's notification settings
             */
            'notifications' => 'required|array',

            /**
             * Whether the user wants to receive email notifications
             */
            'notifications.email_notifications' => 'required|boolean',

            /**
             * Whether the user wants to receive marketing emails
             */
            'notifications.email_marketing' => 'required|boolean',

            /**
             * Whether the user wants to receive security alerts emails
             */
            'notifications.email_security_alerts' => 'required|boolean',
        ];
    }
}
