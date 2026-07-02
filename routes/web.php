<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/dashboard'));

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');
    Route::patch('/projects/{slug}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{slug}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::post('/projects/{slug}/files', [ProjectFileController::class, 'store'])->name('project-files.store');
    Route::delete('/projects/{slug}/files/{file}', [ProjectFileController::class, 'destroy'])->name('project-files.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/projects', [\App\Http\Controllers\Admin\ProjectController::class, 'index'])->name('projects.index');
    Route::delete('/projects/{project}', [\App\Http\Controllers\Admin\ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
