<?php

namespace App\Exceptions\Auth\OAuth;

use Exception;

class AuthOAuthException extends Exception {
    public function __construct(string $message, int $code) {
        parent::__construct($message, $code);
    }
}
