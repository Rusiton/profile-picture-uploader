<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\AuthenticationController;

$availableApiVersions = [
    'v1',
];

$apiVersion = end($availableApiVersions);

foreach (explode('/', request()->fullUrl()) as $requestParam) {
    if (in_array($requestParam, $availableApiVersions)) {
        $apiVersion = $requestParam;
    }
};

$apiNamespace = "App\\Http\\Controllers\\api\\{$apiVersion}";

Route::group(['prefix' => $apiVersion, 'namespace' => $apiNamespace], function () use ($apiNamespace) {
    Route::get('/profile-picture/index', ["{$apiNamespace}\\ProfilePictureController", 'index']);
    Route::get('/profile-picture/user', ["{$apiNamespace}\\ProfilePictureController", 'getUserProfilePicture']);

    Route::post('/profile-picture/upload', ["{$apiNamespace}\\ProfilePictureController", 'upload']);
    Route::delete('/profile-picture/delete', ["{$apiNamespace}\\ProfilePictureController", 'delete']);

    Route::apiResource('user', "{$apiNamespace}\\UserController");

    Route::group(['prefix' => 'auth'], function () {
        Route::post('/register', [AuthenticationController::class, 'register']);
        Route::post('/login', [AuthenticationController::class, 'login']);
        Route::delete('/logout', [AuthenticationController::class, 'logout']);

        Route::get('/email/verify/{id}/{hash}', [AuthenticationController::class, 'verifyEmail'])->middleware(['signed'])->name('verification.verify');

        Route::post('/email/verification-notification', [AuthenticationController::class, 'sendVerificationNotification'])->middleware('throttle:6,1');

        Route::get('/email/verify-status', [AuthenticationController::class, 'getVerificationStatus']);

        Route::post('/send-reset-password', [AuthenticationController::class, 'sendPasswordResetNotification']);
        Route::post('/reset-password', [AuthenticationController::class, 'resetPassword']);
    });  
});


