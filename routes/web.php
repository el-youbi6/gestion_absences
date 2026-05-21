<?php

use App\Http\Controllers\ImportController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\Auth\loginController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::get('/Import', function () {
    return Inertia::render('Importation');
});

// Routes pour la gestion des années scolaires
Route::middleware('auth')->group(function () {
    Route::post('/academic-year/set-active', [AcademicYearController::class, 'setActive'])
        ->name('academic-year.set-active');

    Route::get('/academic-year/all', [AcademicYearController::class, 'getAll'])
        ->name('academic-year.all');
});
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return Inertia::render('auth/login');
    })->name('login');
    Route::post('/login', [loginController::class, 'Login'])->name('connexion');
});


Route::post('/import', [ImportController::class, 'import'])->name('import');
