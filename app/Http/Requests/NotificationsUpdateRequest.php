<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NotificationsUpdateRequest extends FormRequest {
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
             * Whether the user wants to receive marketing emails
             */
            'email_marketing' => 'required|boolean',

            /**
             * Whether the user wants to receive email notifications
             */
            'email_notifications' => 'required|boolean',

            /**
             * Whether the user wants to receive security alerts emails
             */
            'email_security_alerts' => 'required|boolean',
        ];
    }
}
