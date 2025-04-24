<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\GitHubUserData;
use App\Models\GoogleUserData;
use App\Models\User;
use App\Utils\Enums\OAuthDrivers;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Nette\NotImplementedException;

class UserController extends Controller {
    /**
     * Display a currently logged-in user.
     */
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
            Log::info('github_data', $github->toArray());
            //@TODO: Read token validity time and refresh if expired
        }
        if (array_key_exists(OAuthDrivers::GOOGLE->value, $user_data)) {
            $google = $user->googleData()->first();
            $token_check = check_token($user, $google->google_token, $google->google_refresh_token);
            if ($token_check) {
                if (time() > $google->google_token_expires_in) {
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user) {
        throw new NotImplementedException();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        throw new NotImplementedException();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        throw new NotImplementedException();
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): UserResource {
        return new UserResource($user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user) {
        throw new NotImplementedException();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user) {
        throw new NotImplementedException();
    }
}
