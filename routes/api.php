<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserLoginActivityController;
use Illuminate\Support\Facades\Route;

Route::get('/user', [UserController::class, 'index'])->middleware('auth:sanctum');
Route::patch('/user', [UserController::class, 'update'])->middleware('auth:sanctum');
Route::patch('/auth/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
Route::post('/auth/reset-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password/verify-token', [AuthController::class, 'verifyPasswordResetToken']);
Route::post('/auth/reset-password/request', [AuthController::class, 'requestChangePassword']);
Route::post('/user/verify-email', [UserController::class, 'sendVerificationEmail'])->middleware('auth:sanctum');
Route::post('/user/verify-email/{token}', [UserController::class, 'verifyEmail'])->middleware('auth:sanctum');
Route::apiResource('/user', UserController::class, ['except' => ['index', 'update', 'destroy', 'edit']]);;
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/auth/{driver}/callback', [AuthController::class, 'callback']);
Route::get('/auth/{driver}', [AuthController::class, 'oauth']);
Route::post('/auth/{driver}/revoke', [AuthController::class, 'revokeOAuth'])->middleware('auth:sanctum');
Route::get('/user/activity/login', [UserLoginActivityController::class, 'index'])->middleware('auth:sanctum');
