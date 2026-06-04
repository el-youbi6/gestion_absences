<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\Admin\AbsenceStatsController;
use App\Http\Controllers\Admin\FormateurAssignmentController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\loginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Admin\ImportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



// Routes pour la gestion des années scolaires
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    Route::post('/academic-year/set-active', [AcademicYearController::class, 'setActive'])
        ->name('academic-year.set-active');

    Route::get('/academic-year/all', [AcademicYearController::class, 'getAll'])
        ->name('academic-year.all');

    Route::middleware('adminOrSurveillant')->group(function () {
        Route::get('/import', [ImportController::class, 'index']);
        Route::post('/import', [ImportController::class, 'import'])->name('import');

        Route::get('/admin/statistiques-absences', [AbsenceStatsController::class, 'index'])
            ->name('admin.absence-stats.index');

        Route::get('/admin/affectations-formateurs', [FormateurAssignmentController::class, 'index'])
            ->name('admin.formateur-assignments.index');
        Route::post('/admin/affectations-formateurs', [FormateurAssignmentController::class, 'store'])
            ->name('admin.formateur-assignments.store');
        Route::post('/admin/affectations-formateurs/import', [FormateurAssignmentController::class, 'import'])
            ->name('admin.formateur-assignments.import');
    });

    Route::middleware('isFormateur')->group(function () {
        Route::get('/absences/saisie', [AbsenceController::class, 'create'])
            ->name('absences.saisie');

        Route::post('/absences/saisie', [AbsenceController::class, 'store'])
            ->name('absences.saisie.store');

        Route::get('/absences/rapport-modules', [AbsenceController::class, 'moduleReport'])
            ->name('absences.rapport-modules');
    });

    Route::middleware('isAdmin')->group(function () {
        Route::get('/admin/users', [UserManagementController::class, 'index'])
            ->name('admin.users.index');
        Route::post('/admin/users', [UserManagementController::class, 'store'])
            ->name('admin.users.store');
        Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])
            ->name('admin.users.update');
        Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])
            ->name('admin.users.destroy');
    });
});

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return Inertia::render('auth/login');
    })->name('login');

    Route::post('/login', [loginController::class, 'Login'])->name('connexion');
});
