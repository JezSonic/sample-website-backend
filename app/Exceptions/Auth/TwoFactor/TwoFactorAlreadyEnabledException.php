<?php

namespace App\Exceptions\Auth\TwoFactor;

use Exception;

class TwoFactorAlreadyEnabledException extends Exception {
    public function __construct(string $message = '2fa_already_enabled', int $code = 409) {
        parent::__construct($message, $code);
    }
}
