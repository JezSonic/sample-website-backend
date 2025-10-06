<?php

namespace App\Exceptions\Auth\TwoFactor;

use Exception;

class TwoFactorRequiredException extends Exception {
    public function __construct(string $message = '2fa_required', int $code = 409) {
        parent::__construct($message, $code);
    }
}
