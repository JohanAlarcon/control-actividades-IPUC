<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityTypeController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\CommitteeUserController;
use App\Http\Controllers\RoleSwitchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'))->name('home');

// La version que esta instalada: cada pantalla la compara con la suya para avisar que hay una
// nueva (T047). No revela nada: es la huella del archivo de compilacion.
Route::get('version', fn () => response()->json(['version' => Inertia::getVersion()]))->name('version');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::post('role/switch', [RoleSwitchController::class, 'store'])->name('role.switch');

    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('users/{user}/revoke', [UserController::class, 'revoke'])->name('users.revoke');
    Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');

    Route::get('committees', [CommitteeController::class, 'index'])->name('committees.index');
    Route::get('committees/{committee}', [CommitteeController::class, 'show'])->whereNumber('committee')->name('committees.show');

    Route::post('committees/{committee}/roles', [CommitteeUserController::class, 'store'])->name('roles.store');
    Route::delete('roles/{role}', [CommitteeUserController::class, 'destroy'])->name('roles.destroy');

    Route::get('activities/create', [ActivityController::class, 'create'])->name('activities.create');
    Route::post('activities', [ActivityController::class, 'store'])->name('activities.store');

    Route::get('activity-types', [ActivityTypeController::class, 'index'])->name('activity-types.index');
    Route::post('activity-types', [ActivityTypeController::class, 'store'])->name('activity-types.store');
    Route::patch('activity-types/{activityType}', [ActivityTypeController::class, 'update'])->whereNumber('activityType')->name('activity-types.update');

    Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('approvals/{approval}/approve', [ApprovalController::class, 'approve'])->whereNumber('approval')->name('approvals.approve');
    Route::post('approvals/{approval}/reject', [ApprovalController::class, 'reject'])->whereNumber('approval')->name('approvals.reject');
});

require __DIR__.'/auth.php';
