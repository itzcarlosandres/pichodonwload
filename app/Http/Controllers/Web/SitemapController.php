<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Console;
use App\Models\Category;
use App\Models\Franchise;
use App\Models\Setting;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Master Sitemap Index: /sitemap.xml
     */
    public function index(): Response
    {
        $latestGame = Game::where('status', 'PUBLISHED')->latest('updated_at')->first();
        $latestConsole = Console::latest('updated_at')->first();
        $latestFranchise = Franchise::where('is_active', true)->latest('updated_at')->first();

        $subSitemaps = [
            [
                'loc' => url('/sitemap-games.xml'),
                'lastmod' => $latestGame ? $latestGame->updated_at->toAtomString() : now()->toAtomString(),
            ],
            [
                'loc' => url('/sitemap-consoles.xml'),
                'lastmod' => $latestConsole ? $latestConsole->updated_at->toAtomString() : now()->toAtomString(),
            ],
            [
                'loc' => url('/sitemap-genres.xml'),
                'lastmod' => $latestGame ? $latestGame->updated_at->toAtomString() : now()->toAtomString(),
            ],
            [
                'loc' => url('/sitemap-collections.xml'),
                'lastmod' => $latestFranchise ? $latestFranchise->updated_at->toAtomString() : now()->toAtomString(),
            ],
            [
                'loc' => url('/sitemap-pages.xml'),
                'lastmod' => now()->toAtomString(),
            ],
        ];

        $content = view('sitemaps.index', compact('subSitemaps'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'X-Robots-Tag' => 'noindex, follow',
        ]);
    }

    /**
     * Games / Posts XML Sitemap: /sitemap-games.xml
     */
    public function games(): Response
    {
        $games = Game::select(['id', 'title', 'slug', 'updated_at', 'cover_url', 'banner_url'])
            ->where('status', 'PUBLISHED')
            ->orderByDesc('updated_at')
            ->get();

        $content = view('sitemaps.games', compact('games'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    /**
     * Consoles XML Sitemap: /sitemap-consoles.xml
     */
    public function consoles(): Response
    {
        $consoles = Console::withCount(['games' => fn($q) => $q->where('status', 'PUBLISHED')])
            ->orderBy('name')
            ->get();

        $content = view('sitemaps.consoles', compact('consoles'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    /**
     * Genres / Categories XML Sitemap: /sitemap-genres.xml
     */
    public function genres(): Response
    {
        $categories = Category::whereHas('games', fn($q) => $q->where('status', 'PUBLISHED'))
            ->orderBy('name')
            ->get();

        $content = view('sitemaps.genres', compact('categories'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    /**
     * Collections / Sagas XML Sitemap: /sitemap-collections.xml
     */
    public function collections(): Response
    {
        $franchises = Franchise::withCount(['games' => fn($q) => $q->where('status', 'PUBLISHED')])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $content = view('sitemaps.collections', compact('franchises'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    /**
     * Static & Hub Pages XML Sitemap: /sitemap-pages.xml
     */
    public function pages(): Response
    {
        $pages = [
            [
                'loc' => url('/'),
                'changefreq' => 'daily',
                'priority' => '1.0',
                'lastmod' => now()->toAtomString(),
            ],
            [
                'loc' => route('consoles.index'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
                'lastmod' => now()->subDay()->toAtomString(),
            ],
            [
                'loc' => route('collections.index'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
                'lastmod' => now()->subDay()->toAtomString(),
            ],
            [
                'loc' => route('emulators'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
                'lastmod' => now()->subDays(2)->toAtomString(),
            ],
            [
                'loc' => route('bios'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'lastmod' => now()->subDays(3)->toAtomString(),
            ],
            [
                'loc' => route('rankings'),
                'changefreq' => 'daily',
                'priority' => '0.8',
                'lastmod' => now()->toAtomString(),
            ],
            [
                'loc' => route('search'),
                'changefreq' => 'daily',
                'priority' => '0.7',
                'lastmod' => now()->toAtomString(),
            ],
            [
                'loc' => route('dmca'),
                'changefreq' => 'yearly',
                'priority' => '0.4',
                'lastmod' => now()->subMonth()->toAtomString(),
            ],
            [
                'loc' => route('contact'),
                'changefreq' => 'monthly',
                'priority' => '0.5',
                'lastmod' => now()->subWeeks(2)->toAtomString(),
            ],
        ];

        $content = view('sitemaps.pages', compact('pages'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    /**
     * Dynamic robots.txt
     */
    public function robots(): Response
    {
        $customRobots = Setting::get('robots_txt');
        if ($customRobots && trim($customRobots) !== '') {
            $content = $customRobots;
        } else {
            $sitemapUrl = url('/sitemap.xml');
            $content = "User-agent: *\n"
                . "Allow: /\n"
                . "Disallow: /admin/\n"
                . "Disallow: /login\n"
                . "Disallow: /register\n"
                . "Disallow: /download/*/track\n"
                . "Disallow: /search/live\n"
                . "Disallow: /favorites/\n\n"
                . "Sitemap: {$sitemapUrl}\n";
        }

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
