<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\UserLoginActivityController;
use Illuminate\Support\Facades\Route;

// User profile routes
Route::get('/', [UserController::class, 'index'])->middleware(['auth:sanctum', 'token.refresh']);
Route::patch('/', [UserController::class, 'update'])->middleware(['auth:sanctum', 'token.refresh']);
Route::delete('/', [UserController::class, 'destroy'])->middleware(['auth:sanctum', 'token.refresh']);

// User data export routes
Route::get('/export-data', [UserController::class, 'exportUserData'])->middleware(['auth:sanctum', 'token.refresh']);
Route::get('/{userId}/export-data/download', [UserController::class, 'downloadExportedData']);
Route::get('/{userId}/export-data/status', [UserController::class, 'checkExportDataStatus']);
Route::put('/notifications', [UserController::class, 'updateNotifications']);

// Email verification routes
Route::post('/verify-email', [UserController::class, 'sendVerificationEmail'])->middleware(['auth:sanctum', 'token.refresh']);
Route::post('/verify-email/{token}', [UserController::class, 'verifyEmail'])->middleware(['auth:sanctum', 'token.refresh']);

// User activity
Route::get('/activity/login', [UserLoginActivityController::class, 'index'])->middleware(['auth:sanctum', 'token.refresh']);

// User API resource routes (excluding already defined routes)
Route::apiResource('/', UserController::class, ['except' => ['index', 'update', 'destroy', 'edit']]);
