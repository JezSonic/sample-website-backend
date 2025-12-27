<?php

namespace App\Utils\Enums;
/**
 * Enum representing the status of a user data export
 */
enum LoginMethods: string {
    /**
     * Email login method
     */
    case Email = 'email';

    /**
     * Google OAuth login method
     */
    case Google = 'google';

    /**
     * GitHub OAuth login method
     */
    case GitHub = 'github';
}
