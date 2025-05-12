<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Nette\NotImplementedException;
use Random\RandomException;

class UserController extends Controller {
    use Response;

    public function index(Request $request): UserResource {
        function check_token($user, $token, $refresh_token): bool {
            if (is_null($user)) {
                throw new Exception('No user provided');
            }

            if (is_null($token)) {
                throw new Exception('No token provided');
            }

            if (is_null($refresh_token)) {
                throw new Exception('No refresh token provided');
            }
            return true;
        }

        $user_resource = new UserResource($request->user());
        $user_data = json_decode(json_encode($user_resource), true);
        $user = new User()->where('id', '=', $request->user()->id)->first();
        if (array_key_exists(OAuthDrivers::GITHUB->value, $user_data)) {
            $github = $user->gitHubData()->first();
            //@TODO: Read token validity time and refresh if expired
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
//            } elseif ($provider === 'github') {
//                $response = Http::post('https://github.com/login/oauth/access_token', [
//                    'client_id' => config('services.github.client_id'),
//                    'client_secret' => config('services.github.client_secret'),
//                    'grant_type' => 'refresh_token',
//                    'refresh_token' => $user->refresh_token,
//                ]);
//
//                if ($response->successful()) {
//                    $data = $response->json();
//                    $user->update([
//                            'access_token' => $data['access_token'],
//                            'refresh_token' => $data['refresh_token']]
//                    );
//                } else {
//                    Auth::logout();
//                    throw new Exception('Failed to refresh GitHub token.');
//                }
//            }
//        }
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

        $user->name = $data['name'];
        $user->save();
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

    public function show(User $user): UserResource {
        return new UserResource($user);
    }

    public function edit(User $user) {
        throw new NotImplementedException();
    }

    public function destroy(User $user) {
        throw new NotImplementedException();
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
        if ($user->email_verification_token != null) {
            //TODO: CHeck token validity and return old if still valid
            $token_valid = false;
            if (!$token_valid) {
                $token = bin2hex(random_bytes(16));
                $user->email_verification_token = $token;
                $user->save();
            }
        } else {
            $token = bin2hex(random_bytes(16));
            $user->email_verification_token = $token;
            $user->save();
        }

        $verificationUrl = url('/api/user/verify-email/' . $token);
        Mail::to($user)->send(new VerifyEmailAddress($verificationUrl));
        return $this->boolResponse(true);
    }
}
