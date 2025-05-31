<?php

namespace App\Exceptions\User;

use Exception;

class AccountNotFoundException extends Exception {
    public function __construct(string $message = 'account_not_found', int $code = 404) {
        parent::__construct($message, $code);
    }
}
