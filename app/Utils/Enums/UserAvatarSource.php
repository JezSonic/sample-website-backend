<?php

namespace App\Utils\Enums;

enum UserAvatarSource: string {
    /**
     * Default avatar source.
     */
    case Default = 'default';

    /**
     * Avatar source from Google.
     */
    case Google = 'google';

    /**
     * Avatar source from GitHub.
     */
    case GitHub = 'github';
}
