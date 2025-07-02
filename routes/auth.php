<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Password reset routes
Route::post('/auth/reset-password', [AuthController::class, 'changePassword']);
Route::post('/auth/reset-password/verify-token', [AuthController::class, 'verifyPasswordResetToken']);
Route::post('/auth/reset-password/request', [AuthController::class, 'requestChangePassword']);

// Authentication routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/refresh', [AuthController::class, 'refreshToken']);
Route::post('/auth/revoke-refresh', [AuthController::class, 'revokeRefreshToken']);
Route::get('/auth/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum', 'token.refresh']);

// OAuth routes
Route::post('/auth/{driver}/callback', [AuthController::class, 'callback']);
Route::get('/auth/{driver}', [AuthController::class, 'oauth']);
Route::post('/auth/{driver}/revoke', [AuthController::class, 'revokeOAuth'])->middleware(['auth:sanctum', 'token.refresh']);
