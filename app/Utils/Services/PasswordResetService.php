<?php

namespace App\Utils\Services;

use App\Exceptions\Auth\OAuth\InvalidTokenException;
use App\Mail\ResetPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Random\RandomException;

class PasswordResetService {
    /**
     * Generate a password reset token for a user
     *
     * @param User $user The user to generate a token for
     * @return string The generated token
     * @throws RandomException
     */
    public static function generateToken(User $user): string {
        $_token = bin2hex(random_bytes(16));
        $user->password_reset_token = $_token;
        $user->password_reset_token_valid_for = now()->addMinutes(15);
        $user->save();
        return $_token;
    }

    /**
     * Request a password reset for a user
     *
     * @param string $email The email of the user requesting a password reset
     * @return bool True if the request was successful
     * @throws RandomException
     */
    public static function requestPasswordReset(string $email): bool {
        $user = User::where('email', '=', $email)->first();

        if (!$user) {
            return false;
        }

        if ($user->password_reset_token != null) {
            if (time() < strtotime($user->password_reset_token_valid_for)) {
                $valid_until = $user->password_reset_token_valid_for;
                $token = $user->password_reset_token;
            } else {
                $token = self::generateToken($user);
                $valid_until = now()->addMinutes(15);
            }
        } else {
            $token = self::generateToken($user);
            $valid_until = now()->addMinutes(15);
        }

        $resetPasswordUrl = env("APP_DOMAIN") . '/auth/reset-password/' . $token;
        Mail::to($email)->send(new ResetPassword($resetPasswordUrl, $valid_until));
        return true;
    }

    /**
     * Change a user's password using a reset token
     *
     * @param string $token The password reset token
     * @param string $newPassword The new password
     * @return bool True if the password was changed successfully
     * @throws InvalidTokenException If the token is invalid or expired
     */
    public static function changePassword(string $token, string $newPassword): bool {
        $user = User::where('password_reset_token', '=', $token)->first();

        if ($user == null) {
            throw new InvalidTokenException();
        }

        if (time() > strtotime($user->password_reset_token_valid_for)) {
            throw new InvalidTokenException();
        }

        $salt = Str::random();
        $hashed = Hash::make($newPassword . $salt);
        $user->password = $hashed;
        $user->salt = $salt;
        $user->password_reset_token = null;
        $user->password_reset_token_valid_for = null;
        $user->save();

        return true;
    }

    /**
     * Verify if a password reset token is valid
     *
     * @param string $token The token to verify
     * @return array Information about the token validity and if it's for creating a new password
     * @throws InvalidTokenException If the token is invalid
     */
    public static function verifyToken(string $token): array {
        $user = User::where('password_reset_token', '=', $token)->first();

        if ($user == null) {
            throw new InvalidTokenException();
        }

        $is_creating_password = ($user->getSalt() == null);
        $success = false;

        if (time() > strtotime($user->password_reset_token_valid_for)) {
            $user->password_reset_token = null;
            $user->password_reset_token_valid_for = null;
            $user->save();
        } else {
            $success = true;
        }


        return [
            /** Determines success of the verification */
            'content' => $success,

            /** Whether the token is for creating a new password */
            'creating_password' => $is_creating_password
        ];
    }
}
