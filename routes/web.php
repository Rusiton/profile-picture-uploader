<?php

use App\Http\Controllers\auth\GoogleAuthenticationController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/google/redirect', [GoogleAuthenticationController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthenticationController::class, 'callback']);