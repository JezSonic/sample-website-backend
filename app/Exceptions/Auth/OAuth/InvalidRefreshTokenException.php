<?php

namespace App\Exceptions\Auth\OAuth;

use Exception;

class InvalidRefreshTokenException extends Exception {
    public function __construct(string $message = 'invalid_refresh_token', int $code = 400) {
        parent::__construct($message, $code);
    }
}
