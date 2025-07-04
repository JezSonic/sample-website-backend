<?php

namespace App\Exceptions\Auth\OAuth;

use Exception;

class NoRefreshTokenException extends Exception
{
    public function __construct()
    {
        parent::__construct('no_refresh_token_provided');
    }
}
