<?php

namespace App\Exceptions\Auth\OAuth;

class UnsupportedDriver extends AuthOAuthException {
    public function __construct(string $message = 'Unsupported OAuth driver', int $code = 403) {
        parent::__construct($message, $code);
    }
}
