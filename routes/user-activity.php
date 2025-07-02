<?php

use App\Http\Controllers\UserLoginActivityController;
use Illuminate\Support\Facades\Route;

// User login activity routes
Route::get('/user/activity/login', [UserLoginActivityController::class, 'index'])->middleware(['auth:sanctum', 'token.refresh']);
