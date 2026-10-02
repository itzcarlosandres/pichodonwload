<?php

use App\Http\Controllers\Admin\AdminAiController;
use App\Http\Controllers\Admin\AdminBadgeController;
use App\Http\Controllers\Admin\AdminBannerController;
use App\Http\Controllers\Admin\AdminBiosController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminConsoleController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEmulatorController;
use App\Http\Controllers\Admin\AdminFranchiseController;
use App\Http\Controllers\Admin\AdminGameController;
use App\Http\Controllers\Admin\AdminGameRequestController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminScraperDemoController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

// Scraper Extractor Demo & Catalog Browser
Route::get('scraper-demo', [AdminScraperDemoController::class, 'index'])->name('scraper.demo');
Route::post('scraper-demo/extract', [AdminScraperDemoController::class, 'extract'])->name('scraper.extract');
Route::post('scraper-demo/save', [AdminScraperDemoController::class, 'saveToCatalog'])->name('scraper.save');
Route::get('scraper-catalog', [AdminScraperDemoController::class, 'catalog'])->name('scraper.catalog');
Route::get('scraper-catalog/fetch', [AdminScraperDemoController::class, 'fetchCatalog'])->name('scraper.catalog.fetch');
Route::post('scraper-catalog/quick-import', [AdminScraperDemoController::class, 'quickImport'])->name('scraper.quick_import');
Route::get('scraper/safety-status', [AdminScraperDemoController::class, 'safetyStatus'])->name('scraper.safety_status');
Route::post('scraper/reset-cooldown', [AdminScraperDemoController::class, 'resetSafetyCooldown'])->name('scraper.reset_cooldown');
Route::get('scraper/autopilot-status', [AdminScraperDemoController::class, 'autopilotStatus'])->name('scraper.autopilot_status');
Route::post('scraper/drip-publish-now', [AdminScraperDemoController::class, 'dripPublishNow'])->name('scraper.drip_now');
Route::post('scraper/auto-harvest-draft', [AdminScraperDemoController::class, 'autoHarvestToDraft'])->name('scraper.auto_harvest_draft');
Route::get('scraper/autopilot-settings', [AdminScraperDemoController::class, 'getAutopilotSettings'])->name('scraper.autopilot_settings.get');
Route::post('scraper/autopilot-settings', [AdminScraperDemoController::class, 'updateAutopilotSettings'])->name('scraper.autopilot_settings.update');

// AI Endpoints
Route::post('/ai/seo', [AdminAiController::class, 'generateSeo'])->name('ai.seo');
Route::post('/ai/description', [AdminAiController::class, 'generateDescription'])->name('ai.description');
Route::post('/ai/specs', [AdminAiController::class, 'autocompleteSpecs'])->name('ai.specs');
Route::post('/ai/franchise', [AdminAiController::class, 'generateFranchise'])->name('ai.franchise');

// Games CRUD
Route::post('games/prepare-rom-upload', [AdminGameController::class, 'prepareRomUpload'])->name('games.prepareRomUpload');
Route::post('games/upload-rom', [AdminGameController::class, 'uploadRom'])->name('games.uploadRom');
Route::resource('games', AdminGameController::class);
Route::post('games/{game}/duplicate', [AdminGameController::class, 'duplicate'])->name('games.duplicate');

// Sagas & Franquicias CRUD
Route::match(['get', 'post'], 'franchises/sync-all', [AdminFranchiseController::class, 'syncAll'])->name('franchises.syncAll');
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

// Peticiones de la Comunidad ("Solicitar ROM")
Route::resource('requests', AdminGameRequestController::class)->only(['index', 'destroy']);
Route::patch('requests/{gameRequest}/status', [AdminGameRequestController::class, 'updateStatus'])->name('requests.updateStatus');

// System Settings & Storage S3/R2 & Gemini & Telegram Test
Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
Route::post('settings', [AdminSettingController::class, 'update'])->name('settings.update');
Route::post('settings/test-storage', [AdminSettingController::class, 'testStorage'])->name('settings.testStorage');
Route::post('settings/test-gemini', [AdminSettingController::class, 'testGemini'])->name('settings.testGemini');
Route::post('settings/test-telegram', [AdminSettingController::class, 'testTelegram'])->name('settings.testTelegram');
Route::post('settings/clear-cache', [AdminSettingController::class, 'clearCache'])->name('settings.clearCache');

// Telegram Game Publishing
Route::post('games/{game}/publish-telegram', [AdminGameController::class, 'publishTelegram'])->name('games.publishTelegram');

// Admin Profile / Credenciales (Correo y Contraseña)
Route::get('profile', [AdminUserController::class, 'profile'])->name('profile');
Route::put('profile', [AdminUserController::class, 'updateProfile'])->name('profile.update');
