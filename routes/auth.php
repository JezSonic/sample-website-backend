<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Password reset routes
Route::post('/reset-password', [AuthController::class, 'changePassword']);
Route::post('/reset-password/verify-token', [AuthController::class, 'verifyPasswordResetToken']);
Route::post('/reset-password/request', [AuthController::class, 'requestChangePassword']);

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/refresh', [AuthController::class, 'refreshToken']);
Route::post('/revoke-refresh', [AuthController::class, 'revokeRefreshToken']);
Route::get('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum', 'token.refresh']);

// OAuth routes
Route::post('/{driver}/callback', [AuthController::class, 'callback']);
Route::get('/{driver}', [AuthController::class, 'oauth']);
Route::post('/{driver}/revoke', [AuthController::class, 'revokeOAuth'])->middleware(['auth:sanctum', 'token.refresh']);
