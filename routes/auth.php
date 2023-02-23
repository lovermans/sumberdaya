<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;

$route->get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

$route->post('register', [RegisteredUserController::class, 'store']);

$route->middleware('guest')->group(function () use ($route) {
    $route->get('/login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    $route->post('/login', [AuthenticatedSessionController::class, 'store']);

    $route->get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');

    $route->post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    $route->get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');

    $route->post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.update');
});

$route->middleware('auth')->group(function () use ($route) {
    $route->get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
                ->name('verification.notice');

    $route->get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    $route->post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    $route->get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

    $route->post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    $route->post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});
