<?php

namespace App\Exceptions\User;

use Exception;

class InvalidAvatarSourceException extends Exception {
    public function __construct(string $message = 'invalid_avatar_source', int $code = 400) {
        parent::__construct($message, $code);
    }
}
