<?php

namespace App\Exceptions\Auth\OAuth;

class OAuthAccountPasswordLoginException extends AuthOAuthException {
    public function __construct() {
        parent::__construct('This account was created using OAuth and cannot be accessed with email/password. Please use OAuth login instead.', 422);
    }
}
