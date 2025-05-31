<?php

namespace App\Exceptions\User;

use Exception;

class PrivateProfileException extends Exception {
    public function __construct(string $message = 'private_profile', int $code = 403) {
        parent::__construct($message, $code);
    }
}
