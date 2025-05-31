<?php

namespace App\Http\Controllers;

use App\Exceptions\User\AccountNotFoundException;
use App\Exceptions\User\InvalidTokenException;
use App\Exceptions\User\PrivateProfileException;
use App\Http\Requests\ProfileSettingsUpdateRequest;
use App\Http\Resources\UserResource;
use App\Mail\VerifyEmailAddress;
use App\Models\GitHubUserData;
use App\Models\GoogleUserData;
use App\Models\User;
use App\Models\UserProfileSettings;
use App\Utils\Enums\OAuthDrivers;
use App\Utils\Traits\Response;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Nette\NotImplementedException;
use Random\RandomException;

class UserController extends Controller {
    use Response;

    /**
     * @throws Exception
     */
    public function index(Request $request): UserResource {
        function check_token($user, $token, $refresh_token, $has_refresh_token = true): bool {
            if (is_null($user)) {
                throw new Exception('No user provided');
            }

            if (is_null($token)) {
                throw new Exception('No token provided');
            }

            if (is_null($refresh_token) && $has_refresh_token) {
                throw new Exception('No refresh token provided');
            }
            return true;
        }

        $user_resource = new UserResource($request->user());
        $user_data = json_decode(json_encode($user_resource), true);
        $user = new User()->where('id', '=', $request->user()->id)->first();
        if (array_key_exists(OAuthDrivers::GITHUB->value, $user_data)) {
            $github = $user->gitHubData()->first();
            $token_check = check_token($user, $github->github_token, $github->github_refresh_token, false);
            if ($token_check && time() > $github->github_token_expires_in) {
                //$newToken = Socialite::driver(OAuthDrivers::GITHUB->value)->refreshToken($github->github_refresh_token);
                $new_user_data = Socialite::driver(OAuthDrivers::GITHUB->value)->userFromToken($github->github_token);
                GitHubUserData::updateOrCreate(
                [
                    'id' => $new_user_data->id,
                ],
                [
                    'github_token' => $new_user_data->token,
                    'github_refresh_token' => $new_user_data->refreshToken,
                    'github_token_expires_in' => time() + $new_user_data->expiresIn,
                    'github_name' => $new_user_data->name,
                    'github_email' => $new_user_data->email,
                    'github_avatar_url' => $new_user_data->avatar,
                    'github_login' => $new_user_data->nickname,
                ]);
            }
        }
        if (array_key_exists(OAuthDrivers::GOOGLE->value, $user_data)) {
            $google = $user->googleData()->first();
            $token_check = check_token($user, $google->google_token, $google->google_refresh_token);
            if ($token_check && time() > $google->google_token_expires_in) {
                $newToken = Socialite::driver(OAuthDrivers::GOOGLE->value)->refreshToken($google->google_refresh_token);
                $new_user_data = Socialite::driver(OAuthDrivers::GOOGLE->value)->userFromToken($newToken->token);
                GoogleUserData::updateOrCreate(
                [
                    'id' => $new_user_data->id,
                ],
                [
                    'google_token' => $newToken->token,
                    'google_refresh_token' => $newToken->refreshToken,
                    'google_name' => $new_user_data->name,
                    'google_email' => $new_user_data->email,
                    'google_avatar_url' => $new_user_data->avatar,
                ]);
            }
        }
        return $user_resource;
    }

    public function update(ProfileSettingsUpdateRequest $request): JsonResponse {
        $data = $request->all();
        $user = User::where('id', '=', Auth::user()->id)->first();
        $user_profile_settings = $user->profileSettings()->first();
        if ($user_profile_settings == null) {
            $user_profile_settings = new UserProfileSettings();
            $user_profile_settings->user_id = $user->id;
        }
        if ($data['name'] != '' && $data['name'] != null) {
            $user->name = $data['name'];
            $user->save();
        }

        $user_profile_settings->avatar_source = $data['avatar_source'];
        $user_profile_settings->is_public = $data['is_public'];
        $user_profile_settings->language = $data['language'];
        $user_profile_settings->theme = $data['theme'];
        $user_profile_settings->email_notifications = $data['notifications']['email_notifications'];
        $user_profile_settings->email_marketing = $data['notifications']['email_marketing'];
        $user_profile_settings->email_security_alerts = $data['notifications']['email_security_alerts'];
        $user_profile_settings->save();
        return $this->boolResponse(true);
    }

    public function create() {
        throw new NotImplementedException();
    }

    public function store(Request $request) {
        throw new NotImplementedException();
    }

    /**
     * @throws PrivateProfileException
     * @throws AccountNotFoundException
     */
    public function show(User $user): UserResource {
        if (User::where('id', '=', $user->id)->first() == null) {
            throw new AccountNotFoundException();
        }

        if (!$user->profileSettings()->first()->is_public) {
            $authUser = Auth::user();
            if (!is_null($authUser) && $authUser->id != $user->id) {
                throw new PrivateProfileException();
            } else if (is_null($authUser)) {
                throw new PrivateProfileException();
            }
        }

        return new UserResource($user);
    }

    public function edit(User $user) {
        throw new NotImplementedException();
    }

    public function destroy(Request $request): JsonResponse {
        $user = Auth::user();
        $db_user = User::where('id', '=', $user->id);
        if ($db_user == null) {
            return $this->invalidCredentialsResponse();
        }

        $db_user->googleData()->delete();
        $db_user->profileSettings()->delete();
        $db_user->githubData()->delete();
        $db_user->loginActivities()->delete();
        $db_user->delete();
        return $this->boolResponse(true);
    }

    /**
     * @throws RandomException
     */
    public function sendVerificationEmail(Request $request): JsonResponse {
        $user = User::where('id', '=', Auth::user()->id)->first();
        if ($user == null) {
            return $this->invalidCredentialsResponse();
        }

        $token = null;
        function generate_token(User $user): void {
            $token = bin2hex(random_bytes(16));
            $user->email_verification_token = $token;
            $user->email_verification_token_valid_for = now()->addMinutes(15);
            $user->save();
        }

        if ($user->email_verification_token != null) {
            if (time() < strtotime($user->email_verification_token_valid_for)) {
                $token = $user->email_verification_token;
            } else {
                generate_token($user);
            }
        } else {
            generate_token($user);
        }

        $verificationUrl = url('/api/user/verify-email/' . $token);
        Mail::to($user)->send(new VerifyEmailAddress($verificationUrl));
        return $this->boolResponse(true);
    }

    /**
     * @throws InvalidTokenException
     */
    public function verifyEmail(string $token): JsonResponse {
        // Find user by token
        $user = User::where('email_verification_token', '=', $token)->first();
        if ($user == null) {
            throw new InvalidTokenException();
        }

        // Mark email as verified
        $user->email_verified_at = now();
        // Clear the verification token
        $user->email_verification_token = null;
        $user->email_verification_token_valid_for = null;
        $user->save();

        return $this->boolResponse(true);
    }
}
