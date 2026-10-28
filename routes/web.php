<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityTaskController;
use App\Http\Controllers\ActivityTypeController;
use App\Http\Controllers\AnnualPlanController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\CommitteeMemberController;
use App\Http\Controllers\CommitteeUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberPickerController;
use App\Http\Controllers\MonthNoteController;
use App\Http\Controllers\RoleSwitchController;
use App\Http\Controllers\SiteMottoController;
use App\Http\Controllers\SympathizerAssignmentController;
use App\Http\Controllers\SympathizerController;
use App\Http\Controllers\SympathizerStageController;
use App\Http\Controllers\SympathizerSuggestController;
use App\Http\Controllers\SympathizerVisitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'))->name('home');

// La version que esta instalada: cada pantalla la compara con la suya para avisar que hay una
// nueva (T047). No revela nada: es la huella del archivo de compilacion.
Route::get('version', fn () => response()->json(['version' => Inertia::getVersion()]))->name('version');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

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

    Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('activities/create', [ActivityController::class, 'create'])->name('activities.create');
    Route::get('activities/print', [ActivityController::class, 'print'])->name('activities.print');
    Route::post('activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::get('activities/{activity}', [ActivityController::class, 'show'])->whereNumber('activity')->name('activities.show');
    Route::post('activities/{activity}/complete', [ActivityController::class, 'complete'])->whereNumber('activity')->name('activities.complete');
    Route::post('activities/{activity}/tasks', [ActivityTaskController::class, 'store'])->whereNumber('activity')->name('activities.tasks.store');
    Route::post('activities/{activity}/tasks/{task}/toggle', [ActivityTaskController::class, 'toggle'])->whereNumber(['activity', 'task'])->name('activities.tasks.toggle');

    Route::post('annual-plan/{year}/activities', [AnnualPlanController::class, 'storeActivity'])->whereNumber('year')->name('annual-plan.activities.store');
    Route::post('annual-plan/{year}/approve', [AnnualPlanController::class, 'approve'])->whereNumber('year')->name('annual-plan.approve');

    Route::post('month-notes', [MonthNoteController::class, 'store'])->name('month-notes.store');
    Route::delete('month-notes/{note}', [MonthNoteController::class, 'destroy'])->whereNumber('note')->name('month-notes.destroy');

    Route::get('site-motto', [SiteMottoController::class, 'index'])->name('site-motto.index');
    Route::post('site-motto', [SiteMottoController::class, 'store'])->name('site-motto.store');

    Route::get('activity-types', [ActivityTypeController::class, 'index'])->name('activity-types.index');
    Route::post('activity-types', [ActivityTypeController::class, 'store'])->name('activity-types.store');
    Route::patch('activity-types/{activityType}', [ActivityTypeController::class, 'update'])->whereNumber('activityType')->name('activity-types.update');

    Route::get('members', [MemberController::class, 'index'])->name('members.index');
    Route::get('members/picker', MemberPickerController::class)->name('members.picker');
    Route::get('members/create', [MemberController::class, 'create'])->name('members.create');
    Route::post('members', [MemberController::class, 'store'])->name('members.store');
    Route::get('members/{member}/edit', [MemberController::class, 'edit'])->whereNumber('member')->name('members.edit');
    Route::put('members/{member}', [MemberController::class, 'update'])->whereNumber('member')->name('members.update');
    Route::post('committees/{committee}/members', [CommitteeMemberController::class, 'store'])->whereNumber('committee')->name('committee-members.store');
    Route::delete('committees/{committee}/members/{member}', [CommitteeMemberController::class, 'destroy'])->whereNumber(['committee', 'member'])->name('committee-members.destroy');

    Route::get('sympathizers', [SympathizerController::class, 'index'])->name('sympathizers.index');
    Route::get('sympathizers/create', [SympathizerController::class, 'create'])->name('sympathizers.create');
    Route::get('sympathizers/suggest', SympathizerSuggestController::class)->name('sympathizers.suggest');
    Route::post('sympathizers', [SympathizerController::class, 'store'])->name('sympathizers.store');
    Route::get('sympathizers/{sympathizer}', [SympathizerController::class, 'show'])->whereNumber('sympathizer')->name('sympathizers.show');
    Route::post('sympathizers/{sympathizer}/stages', [SympathizerStageController::class, 'store'])->whereNumber('sympathizer')->name('sympathizers.stages.store');
    Route::post('sympathizers/{sympathizer}/visits', [SympathizerVisitController::class, 'store'])->whereNumber('sympathizer')->name('sympathizers.visits.store');
    Route::post('sympathizers/{sympathizer}/promote', [SympathizerController::class, 'promote'])->whereNumber('sympathizer')->name('sympathizers.promote');
    Route::post('sympathizers/{sympathizer}/assign', [SympathizerAssignmentController::class, 'store'])->whereNumber('sympathizer')->name('sympathizers.assign');

    Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('approvals/bulk-approve', [ApprovalController::class, 'bulkApprove'])->name('approvals.bulk-approve');
    Route::post('approvals/{approval}/approve', [ApprovalController::class, 'approve'])->whereNumber('approval')->name('approvals.approve');
    Route::post('approvals/{approval}/reject', [ApprovalController::class, 'reject'])->whereNumber('approval')->name('approvals.reject');
});

require __DIR__.'/auth.php';
