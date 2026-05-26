<?php

use Illuminate\Support\Facades\Route;
use Modules\Access\Controllers\AccessController;
use Modules\Admin\Controllers\AdminDashboardController;
use Modules\Identity\Controllers\AuthController;
use Modules\Tenancy\Controllers\OrganizationController;
use Modules\Tenancy\Controllers\OrganizationInvitationController;
use Modules\User\Controllers\UserController;

Route::prefix('auth')->group(function (): void {
    Route::middleware('throttle:auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:api')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me'])->middleware('organization.context');
        Route::patch('organizations/current', [OrganizationController::class, 'switch']);
    });
});

Route::post('invitations/accept', [OrganizationInvitationController::class, 'accept'])
    ->middleware('auth:api');

Route::middleware(['auth:api', 'organization.context'])->group(function (): void {
    Route::get('me/organizations', [OrganizationController::class, 'index']);
    Route::get('organizations/current', [OrganizationController::class, 'current']);
    Route::get('organizations/current/invitations', [OrganizationInvitationController::class, 'index']);
    Route::post('organizations/current/invitations', [OrganizationInvitationController::class, 'store']);
    Route::delete('organizations/current/invitations/{id}', [OrganizationInvitationController::class, 'destroy']);
    Route::get('access/roles', [AccessController::class, 'roles']);
    Route::get('access/permissions', [AccessController::class, 'permissions']);
});

Route::prefix('admin')
    ->middleware(['auth:api', 'organization.context', 'admin'])
    ->group(function (): void {
        Route::get('overview', [AdminDashboardController::class, 'show']);
        Route::get('users', [UserController::class, 'index']);
    });
