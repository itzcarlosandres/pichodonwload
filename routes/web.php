<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\GameController;
use App\Http\Controllers\Web\ConsoleController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\DownloadController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\SitemapController;

// 0. SEO: Dynamic Sitemaps & Robots.txt
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-games.xml', [SitemapController::class, 'games'])->name('sitemap.games');
Route::get('/sitemap-consoles.xml', [SitemapController::class, 'consoles'])->name('sitemap.consoles');
Route::get('/sitemap-genres.xml', [SitemapController::class, 'genres'])->name('sitemap.genres');
Route::get('/sitemap-collections.xml', [SitemapController::class, 'collections'])->name('sitemap.collections');
Route::get('/sitemap-pages.xml', [SitemapController::class, 'pages'])->name('sitemap.pages');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots.txt');

// 1. Storefront & Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/dmca', [PageController::class, 'dmca'])->name('dmca');
Route::get('/contacto', [PageController::class, 'contact'])->name('contact');
Route::post('/contacto', [PageController::class, 'submitContact'])->name('contact.submit');
Route::get('/game/{slug}', [GameController::class, 'show'])->name('game.show');
Route::post('/game/{game}/review', [GameController::class, 'submitReview'])->name('game.review');
Route::get('/game/{slug}/download', [DownloadController::class, 'show'])->name('game.download');
Route::get('/download/{slug}', [DownloadController::class, 'show'])->name('download.file');
Route::post('/download/{slug}/track', [DownloadController::class, 'track'])->name('download.track');

Route::get('/consoles', [ConsoleController::class, 'index'])->name('consoles.index');
Route::get('/consoles/{slug}', [ConsoleController::class, 'show'])->name('consoles.show');

// Bios, Emuladores & Sagas / Colecciones
Route::get('/bios', [PageController::class, 'bios'])->name('bios');
Route::get('/bios/hub', [PageController::class, 'bios'])->name('bios.index');
Route::get('/emuladores', [PageController::class, 'emulators'])->name('emulators');
Route::get('/emuladores/hub', [PageController::class, 'emulators'])->name('emulators.index');
Route::get('/colecciones', [PageController::class, 'collections'])->name('collections.index');
Route::get('/colecciones/{slug}', [PageController::class, 'collectionDetail'])->name('collections.show');
Route::get('/rankings', [PageController::class, 'rankings'])->name('rankings');
Route::get('/top-25', [PageController::class, 'rankings'])->name('rankings.index');

Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/live', [SearchController::class, 'live'])->name('search.live');
Route::get('/categoria/{slug}', [SearchController::class, 'category'])->name('category.show');
Route::get('/genero/{slug}', [SearchController::class, 'category'])->name('genre.show');

// 2. User Profile & Favorites
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::post('/favorites/{game}/toggle', [ProfileController::class, 'toggleFavorite'])->name('favorites.toggle');

// 3. Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/login/post', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/register/post', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

