<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserLoginActivityController;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', [UserController::class, 'index'])->middleware('auth:sanctum');
Route::patch('/user', [UserController::class, 'update'])->middleware('auth:sanctum');
Route::post('/user/verify-email', [UserController::class, 'sendVerificationEmail'])->middleware('auth:sanctum');
Route::post('/user/verify-email/{token}', [UserController::class, 'sendVerificationEmail'])->middleware('auth:sanctum');
Route::apiResource('/user', UserController::class, ['except' => ['index', 'update', 'destroy', 'edit']]);;
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/auth/{driver}/callback', [AuthController::class, 'callback']);
Route::get('/auth/{driver}', [AuthController::class, 'oauth']);
Route::post('/auth/{driver}/revoke', [AuthController::class, 'revokeOAuth'])->middleware('auth:sanctum');
Route::get('/user/activity/login', [UserLoginActivityController::class, 'index'])->middleware('auth:sanctum');
