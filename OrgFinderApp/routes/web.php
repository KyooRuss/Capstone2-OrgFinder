<?php

use App\Http\Controllers\SuperAdmin\AuthController;
use App\Http\Controllers\SuperAdmin\OrganizationController;
use App\Http\Controllers\SuperAdmin\EventController;
use App\Http\Controllers\SuperAdmin\AdminOfficerController;
use App\Http\Controllers\SuperAdmin\StudentController;
use App\Http\Controllers\SuperAdmin\TrashController;
use App\Http\Controllers\AdminOfficer\AuthController as AdminOfficerAuthController;
use App\Http\Controllers\AdminOfficer\OrganizationController as AdminOfficerOrgController;
use App\Http\Controllers\AdminOfficer\EventController as AdminOfficerEventController;
use App\Http\Controllers\AdminOfficer\MemberController;
use App\Http\Controllers\AdminOfficer\OfficerController;
use App\Http\Controllers\AdminOfficer\TrashController as AdminOfficerTrashController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('super-admin.login'));

Route::prefix('super-admin')->name('super-admin.')->group(function () {

    // Auth
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected routes
    Route::middleware(\App\Http\Middleware\SuperAdminMiddleware::class)->group(function () {

        // Organizations
        Route::prefix('organizations')->name('organizations.')->group(function () {
            Route::get('/', [OrganizationController::class, 'index'])->name('index');
            Route::get('/create', [OrganizationController::class, 'create'])->name('create');
            Route::post('/', [OrganizationController::class, 'store'])->name('store');
            Route::get('/{organization}/edit', [OrganizationController::class, 'edit'])->name('edit');
            Route::put('/{organization}', [OrganizationController::class, 'update'])->name('update');
            Route::delete('/{organization}', [OrganizationController::class, 'destroy'])->name('destroy');
            Route::get('/{organization}/access', [OrganizationController::class, 'getAccess'])->name('access.get');
            Route::post('/{organization}/access', [OrganizationController::class, 'addAccess'])->name('access.add');
            Route::delete('/{organization}/access/{access}', [OrganizationController::class, 'removeAccess'])->name('access.remove');
        });

        // Events
        Route::prefix('events')->name('events.')->group(function () {
            Route::get('/upcoming', [EventController::class, 'upcoming'])->name('upcoming');
            Route::get('/past', [EventController::class, 'past'])->name('past');
            Route::get('/{event}', [EventController::class, 'show'])->name('show');
            Route::post('/{event}/approve', [EventController::class, 'approve'])->name('approve');
            Route::post('/{event}/reject', [EventController::class, 'reject'])->name('reject');
            Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
        });

        // Admin Officers
        Route::prefix('admin-officers')->name('admin-officers.')->group(function () {
            Route::get('/', [AdminOfficerController::class, 'index'])->name('index');
            Route::post('/{user}/block', [AdminOfficerController::class, 'block'])->name('block');
            Route::post('/{user}/unblock', [AdminOfficerController::class, 'unblock'])->name('unblock');
            Route::delete('/{user}', [AdminOfficerController::class, 'destroy'])->name('destroy');
        });

        // Students
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [StudentController::class, 'index'])->name('index');
            Route::post('/{user}/make-admin', [StudentController::class, 'makeAdmin'])->name('make-admin');
            Route::post('/{user}/block', [StudentController::class, 'block'])->name('block');
            Route::post('/{user}/unblock', [StudentController::class, 'unblock'])->name('unblock');
            Route::delete('/{user}', [StudentController::class, 'destroy'])->name('destroy');
        });

        // Trash
        Route::prefix('trash')->name('trash.')->group(function () {
            Route::get('/organizations', [TrashController::class, 'organizations'])->name('organizations');
            Route::get('/events', [TrashController::class, 'events'])->name('events');
            Route::get('/admin-officers', [TrashController::class, 'adminOfficers'])->name('admin-officers');
            Route::get('/students', [TrashController::class, 'students'])->name('students');

            Route::post('/organizations/{organization}/restore', [TrashController::class, 'restoreOrganization'])->name('organizations.restore');
            Route::delete('/organizations/{organization}', [TrashController::class, 'forceDeleteOrganization'])->name('organizations.force-delete');

            Route::post('/events/{event}/restore', [TrashController::class, 'restoreEvent'])->name('events.restore');
            Route::delete('/events/{event}', [TrashController::class, 'forceDeleteEvent'])->name('events.force-delete');

            Route::post('/users/{user}/restore', [TrashController::class, 'restoreUser'])->name('users.restore');
            Route::delete('/users/{user}', [TrashController::class, 'forceDeleteUser'])->name('users.force-delete');
        });
    });
});

// ─── Admin Officer ────────────────────────────────────────────────────────────
Route::prefix('admin-officer')->name('admin-officer.')->group(function () {

    // Auth
    Route::get('/login', [AdminOfficerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminOfficerAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AdminOfficerAuthController::class, 'logout'])->name('logout');

    // Protected routes
    Route::middleware(\App\Http\Middleware\AdminOfficerMiddleware::class)->group(function () {

        // Organization profile
        Route::get('/organization', [AdminOfficerOrgController::class, 'index'])->name('organization.index');

        // Events
        Route::prefix('events')->name('events.')->group(function () {
            Route::get('/', [AdminOfficerEventController::class, 'index'])->name('index');
            Route::get('/{event}', [AdminOfficerEventController::class, 'show'])->name('show');
            Route::post('/', [AdminOfficerEventController::class, 'store'])->name('store');
            Route::delete('/{event}', [AdminOfficerEventController::class, 'destroy'])->name('destroy');
        });

        // Members
        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/', [MemberController::class, 'index'])->name('index');
            Route::post('/', [MemberController::class, 'store'])->name('store');
            Route::post('/{user}/make-officer', [MemberController::class, 'makeOfficer'])->name('make-officer');
            Route::post('/{user}/block', [MemberController::class, 'block'])->name('block');
            Route::delete('/{user}', [MemberController::class, 'destroy'])->name('destroy');
        });

        // Officers
        Route::prefix('officers')->name('officers.')->group(function () {
            Route::get('/', [OfficerController::class, 'index'])->name('index');
            Route::post('/{user}/block', [OfficerController::class, 'block'])->name('block');
            Route::delete('/{user}', [OfficerController::class, 'destroy'])->name('destroy');
        });

        // Trash
        Route::prefix('trash')->name('trash.')->group(function () {
            Route::get('/members', [AdminOfficerTrashController::class, 'members'])->name('members');
            Route::get('/officers', [AdminOfficerTrashController::class, 'officers'])->name('officers');
            Route::post('/users/{user}/restore', [AdminOfficerTrashController::class, 'restoreUser'])->name('users.restore');
            Route::delete('/users/{user}', [AdminOfficerTrashController::class, 'forceDeleteUser'])->name('users.force-delete');
        });
    });
});
