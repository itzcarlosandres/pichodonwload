<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminGameController;
use App\Http\Controllers\Admin\AdminAiController;
use App\Http\Controllers\Admin\AdminConsoleController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminBadgeController;
use App\Http\Controllers\Admin\AdminBannerController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminBiosController;
use App\Http\Controllers\Admin\AdminEmulatorController;
use App\Http\Controllers\Admin\AdminFranchiseController;

// Dashboard
Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

// AI Endpoints
Route::post('/ai/seo', [AdminAiController::class, 'generateSeo'])->name('ai.seo');
Route::post('/ai/description', [AdminAiController::class, 'generateDescription'])->name('ai.description');
Route::post('/ai/specs', [AdminAiController::class, 'autocompleteSpecs'])->name('ai.specs');
Route::post('/ai/franchise', [AdminAiController::class, 'generateFranchise'])->name('ai.franchise');

// Games CRUD
Route::post('games/upload-rom', [AdminGameController::class, 'uploadRom'])->name('games.uploadRom');
Route::resource('games', AdminGameController::class);
Route::post('games/{game}/duplicate', [AdminGameController::class, 'duplicate'])->name('games.duplicate');

// Sagas & Franquicias CRUD
Route::post('franchises/{franchise}/toggle-status', [AdminFranchiseController::class, 'toggleStatus'])->name('franchises.toggleStatus');
Route::resource('franchises', AdminFranchiseController::class)->except(['create', 'show', 'edit']);

// BIOS & Firmwares CRUD
Route::resource('bios', AdminBiosController::class)->except(['create', 'show', 'edit']);

// Emuladores Recomendados CRUD
Route::resource('emulators', AdminEmulatorController::class)->except(['create', 'show', 'edit']);

// Consoles CRUD
Route::resource('consoles', AdminConsoleController::class)->except(['create', 'show', 'edit']);

// Categories CRUD
Route::resource('categories', AdminCategoryController::class)->except(['create', 'show', 'edit']);

// Badges CRUD
Route::resource('badges', AdminBadgeController::class)->except(['create', 'show', 'edit']);

// Banners CRUD
Route::resource('banners', AdminBannerController::class)->except(['create', 'show', 'edit']);

// Users & RBAC
Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
Route::patch('users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.updateRole');
Route::post('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');

// Reviews Moderation
Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
Route::post('reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
Route::delete('reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');

// System Settings & Storage S3/R2 & Gemini Test
Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
Route::post('settings', [AdminSettingController::class, 'update'])->name('settings.update');
Route::post('settings/test-storage', [AdminSettingController::class, 'testStorage'])->name('settings.testStorage');
Route::post('settings/test-gemini', [AdminSettingController::class, 'testGemini'])->name('settings.testGemini');

// Admin Profile / Credenciales (Correo y Contraseña)
Route::get('profile', [AdminUserController::class, 'profile'])->name('profile');
Route::put('profile', [AdminUserController::class, 'updateProfile'])->name('profile.update');


