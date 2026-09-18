<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Game;
use App\Models\Console;
use App\Models\Category;
use App\Models\Badge;
use App\Models\User;
use App\Models\Setting;
use App\Models\Review;
use App\Models\Banner;

echo "--- TESTING ALL VIEWS ---\n";

// 1. Home
$home = view('web.home', [
    'spotlightGame' => Game::first(),
    'railGames' => Game::take(3)->get(),
    'consoles' => Console::withCount('games')->get(),
    'trendingGames' => Game::take(6)->get(),
    'topRatedGames' => Game::take(3)->get(),
    'recentGames' => Game::take(6)->get(),
    'banners' => collect(),
    'categories' => Category::all()
])->render();
echo "[OK] web.home\n";

// 2. Game Detail
$game = Game::with(['console', 'badges', 'categories', 'screenshots', 'reviews.user'])->first();
$detail = view('web.game-detail', [
    'game' => $game,
    'relatedGames' => Game::take(4)->get()
])->render();
echo "[OK] web.game-detail\n";

// 3. Consoles Hub
$hub = view('web.consoles-hub', ['consoles' => Console::withCount('games')->get()])->render();
echo "[OK] web.consoles-hub\n";

// 4. Console Detail
$conDetail = view('web.console-detail', [
    'console' => Console::first(),
    'games' => Game::paginate(12),
    'categories' => Category::all()
])->render();
echo "[OK] web.console-detail\n";

// 5. Search
$search = view('web.search', [
    'games' => Game::paginate(12),
    'consoles' => Console::all(),
    'categories' => Category::all()
])->render();
echo "[OK] web.search\n";

// 6. Profile
$profile = view('web.profile', [
    'user' => User::first(),
    'favorites' => Game::paginate(6)
])->render();
echo "[OK] web.profile\n";

// 7. Auth Login & Register
$login = view('web.auth.login')->render();
$reg = view('web.auth.register')->render();
echo "[OK] web.auth.login & web.auth.register\n";

// 8. Admin Dashboard
$adminDash = view('admin.dashboard', [
    'totalGames' => 6,
    'totalDownloads' => 1250,
    'totalUsers' => 2,
    'pendingReviews' => 0,
    'recentGames' => Game::take(5)->get(),
    'recentReviews' => Review::take(5)->get(),
    'storageStats' => [
        'total_gb' => 18.5,
        'sony_pct' => 50,
        'nintendo_pct' => 30,
        'xbox_pct' => 15,
        'sega_pct' => 5
    ]
])->render();
echo "[OK] admin.dashboard\n";

// 9. Admin Games
$adminGames = view('admin.games.index', [
    'games' => Game::paginate(10),
    'consoles' => Console::all()
])->render();
echo "[OK] admin.games.index\n";

// 10. Admin Game Create & Edit
$adminCreate = view('admin.games.create', [
    'consoles' => Console::all(),
    'categories' => Category::all(),
    'badges' => Badge::all()
])->render();
$adminEdit = view('admin.games.edit', [
    'game' => $game,
    'consoles' => Console::all(),
    'categories' => Category::all(),
    'badges' => Badge::all()
])->render();
echo "[OK] admin.games.create & edit\n";

// 11. Admin Consoles
$adminCons = view('admin.consoles.index', ['consoles' => Console::withCount('games')->get()])->render();
echo "[OK] admin.consoles.index\n";

// 12. Admin Categories
$adminCats = view('admin.categories.index', ['categories' => Category::withCount('games')->get()])->render();
echo "[OK] admin.categories.index\n";

// 13. Admin Badges
$adminBadges = view('admin.badges.index', ['badges' => Badge::withCount('games')->get()])->render();
echo "[OK] admin.badges.index\n";

// 14. Admin Banners
$adminBanners = view('admin.banners.index', ['banners' => Banner::all()])->render();
echo "[OK] admin.banners.index\n";

// 15. Admin Users
$adminUsers = view('admin.users.index', ['users' => User::paginate(15)])->render();
echo "[OK] admin.users.index\n";

// 16. Admin Reviews
$adminReviews = view('admin.reviews.index', ['reviews' => Review::paginate(15)])->render();
echo "[OK] admin.reviews.index\n";

// 17. Admin Settings
$adminSettings = view('admin.settings.index', ['settings' => Setting::all()->pluck('value', 'key')])->render();
echo "[OK] admin.settings.index\n";

echo "SUCCESS: ALL 18 SYSTEM VIEWS PASSED COMPILATION & RENDERING WITH 0 ERRORS!\n";
