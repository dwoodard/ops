<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Objectives\ObjectiveController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Controllers\Teams\TeamOnboardingController;
use App\Http\Controllers\Teams\TeamProfileController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard.index');
        Route::prefix('team-profile')->name('team-profile.')->group(function () {
            Route::get('/', [TeamProfileController::class, 'show'])->name('show');
            Route::post('enrich', [TeamProfileController::class, 'enrich'])->name('enrich');
            Route::post('approve', [TeamProfileController::class, 'approve'])->name('approve');
            Route::post('reject', [TeamProfileController::class, 'reject'])->name('reject');
        });
        Route::get('onboarding', [TeamOnboardingController::class, 'edit'])->name('onboarding.edit');
        Route::post('onboarding', [TeamOnboardingController::class, 'update'])->name('onboarding.update');
        Route::resource('objectives', ObjectiveController::class)->only(['index', 'create', 'store', 'show']);
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
