<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use Illuminate\Support\Facades\Route;

/*
 * Se entra con un usuario asignado, no con correo (HU-01).
 *
 * No hay registro publico ni recuperacion por correo: las cuentas las crea el
 * pastor y solo el restablece claves (RF-003, RF-005). El sistema no envia
 * correo, asi que un flujo de recuperacion seria una puerta que no funciona.
 */

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('password/change', [ChangePasswordController::class, 'create'])->name('password.change');
    Route::post('password/change', [ChangePasswordController::class, 'store'])->name('password.change.store');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
