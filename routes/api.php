<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ReSendController;
use App\Http\Controllers\Auth\PasswordReset\SendOtpController;
use App\Http\Controllers\Auth\PasswordReset\VerifyOtpController;
use App\Http\Controllers\Auth\PasswordReset\ResetPasswordController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\UserController;

// Auth Routes
Route::prefix('auth')->group(function () {

    // Register
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/verify-code', [VerificationController::class, 'verify'])
        ->middleware('verify.temp.token:email_verification');

    // Login & Logout
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', LogoutController::class)
        ->middleware('auth:sanctum');

    // Resend
    Route::post('/resend', [ReSendController::class, 'resendOtp'])
        ->middleware('verify.temp.token:email_verification,reset_password_request');

    // Password Reset
    Route::prefix('password')->group(function () {
        Route::post('/forgot', SendOtpController::class);
        Route::post('/verify', VerifyOtpController::class)
            ->middleware('verify.temp.token:reset_password_request');
        Route::post('/reset', ResetPasswordController::class)
            ->middleware('verify.temp.token:reset_password_confirm');
    });

});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    // Users
    Route::get('users', [UserController::class, 'index'])
        ->middleware('can:users.view');
    Route::post('users', [UserController::class, 'store'])
        ->middleware('can:users.create');
    Route::put('users/{user}', [UserController::class, 'update'])
        ->middleware('can:users.edit');
    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('can:users.delete');

    // Roles - admin only
    Route::apiResource('roles', RoleController::class)
        ->middleware('role:admin');
    Route::get('roles/{role}/matrix', [RoleController::class, 'matrix'])
        ->middleware('role:admin');
    Route::post('roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions'])
        ->middleware('role:admin');

    // Permissions - admin only
    Route::get('permissions', [PermissionController::class, 'index'])
        ->middleware('role:admin');

});