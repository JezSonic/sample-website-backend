<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// User profile routes
Route::get('/user', [UserController::class, 'index'])->middleware(['auth:sanctum', 'token.refresh']);
Route::patch('/user', [UserController::class, 'update'])->middleware(['auth:sanctum', 'token.refresh']);
Route::delete('/user', [UserController::class, 'destroy'])->middleware(['auth:sanctum', 'token.refresh']);

// User data export routes
Route::get('/user/export-data', [UserController::class, 'exportUserData'])->middleware(['auth:sanctum', 'token.refresh']);
Route::get('/user/{userId}/export-data/download', [UserController::class, 'downloadExportedData']);
Route::get('/user/{userId}/export-data/status', [UserController::class, 'checkExportDataStatus']);

// Email verification routes
Route::post('/user/verify-email', [UserController::class, 'sendVerificationEmail'])->middleware(['auth:sanctum', 'token.refresh']);
Route::post('/user/verify-email/{token}', [UserController::class, 'verifyEmail'])->middleware(['auth:sanctum', 'token.refresh']);

// User API resource routes (excluding already defined routes)
Route::apiResource('/user', UserController::class, ['except' => ['index', 'update', 'destroy', 'edit']]);
