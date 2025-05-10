<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Dedoc\Scramble\Scramble;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        JsonResource::withoutWrapping();
        Scramble::routes(function (Route $route) {
            return Str::startsWith($route->uri, 'api/') ||
                Str::startsWith($route->uri, 'auth/') ||
                Str::startsWith($route->uri, 'sanctum/') ||
                Str::startsWith($route->uri, 'assets/');
        });
    }
}
