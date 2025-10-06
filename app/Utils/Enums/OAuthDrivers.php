<?php

namespace App\Utils\Enums;

/**
 * Enumeration representing supported OAuth driver types.
 */
enum OAuthDrivers: string {
    /**
     * Google OAuth driver
     * @var string
     */
    case GOOGLE = "google";

    /**
     * Google One Tap OAuth driver
     * @var string
     */
    case GOOGLE_ONE_TAP = "google-one-tap";

    /**
     * GitHub OAuth driver
     * @var string
     */
    case GITHUB = 'github';
}
