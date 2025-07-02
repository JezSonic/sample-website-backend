<?php

namespace App\Exceptions\Auth\OAuth;

class OAuthAccountPasswordLoginException extends AuthOAuthException {
    public function __construct() {
        parent::__construct('oauth_no_password', 422);
    }
}
