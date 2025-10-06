<?php

namespace App\Exceptions\Auth\TwoFactor;

use Exception;

class InvalidTwoFactorCodeException extends Exception {
    public function __construct(string $message = 'invalid_2fa_code', int $code = 400) {
        parent::__construct($message, $code);
    }
}

