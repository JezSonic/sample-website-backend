<?php

namespace App\Utils\Services;

use App\Exceptions\Auth\OAuth\InvalidTokenException;
use App\Mail\VerifyEmailAddress;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Random\RandomException;

class EmailVerificationService {
    /**
     * Generate an email verification token for a user
     *
     * @param User $user The user to generate a token for
     * @return string The generated token
     * @throws RandomException
     */
    public static function generateToken(User $user): string {
        $_token = bin2hex(random_bytes(16));
        $user->email_verification_token = $_token;
        $user->email_verification_token_valid_for = now()->addMinutes(15);
        $user->save();
        return $_token;
    }

    /**
     * Send a verification email to a user
     *
     * @param User $user The user to send the verification email to
     * @return bool True if the email was sent successfully
     * @throws RandomException
     */
    public static function sendVerificationEmail(User $user): bool {

        if ($user->email_verification_token != null) {
            if (time() < strtotime($user->email_verification_token_valid_for)) {
                $valid_until = $user->email_verification_token_valid_for;
                $token = $user->email_verification_token;
            } else {
                $token = self::generateToken($user);
                $valid_until = now()->addMinutes(15);
            }
        } else {
            $token = self::generateToken($user);
            $valid_until = now()->addMinutes(15);
        }

        $verificationUrl = env("APP_DOMAIN") . '/auth/verify-email/' . $token;
        Mail::to($user)->send(new VerifyEmailAddress($verificationUrl, $valid_until));
        return true;
    }

    /**
     * Verify a user's email using a verification token
     *
     * @param string $token The verification token
     * @return bool True if the email was verified successfully
     * @throws InvalidTokenException If the token is invalid
     */
    public static function verifyEmail(string $token): bool {
        // Find user by token
        $user = User::where('email_verification_token', '=', $token)->first();
        if ($user == null) {
            throw new InvalidTokenException();
        }

        // Check if token is expired
        if (time() > strtotime($user->email_verification_token_valid_for)) {
            throw new InvalidTokenException('token_has_expired');
        }

        // Mark email as verified
        $user->email_verified_at = now();
        // Clear the verification token
        $user->email_verification_token = null;
        $user->email_verification_token_valid_for = null;
        $user->save();

        return true;
    }
}
