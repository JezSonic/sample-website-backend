<?php

namespace App\Utils\Enums;

enum OAuthDrivers: string {
    case GOOGLE = "google";

    case GOOGLE_ONE_TAP = "google-one-tap";
    case GITHUB = 'github';
}
