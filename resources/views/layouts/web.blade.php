<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', \App\Models\Setting::get('seo_meta_title', 'ROMHUB — Retro ROMs & Emulators for Classic Consoles'))</title>
    <meta name="description" content="@yield('meta_description', \App\Models\Setting::get('seo_meta_description', 'Explora, descarga y preserva videojuegos clásicos y modernos organizados por 20 consolas con hashes verificados.'))">
    <meta name="keywords" content="@yield('meta_keywords', \App\Models\Setting::get('seo_keywords', 'roms, emuladores, videojuegos, ps2, switch, gamecube, xbox 360, gba'))">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Google Search Console & Bing Webmaster Verification -->
    @if($gVerification = \App\Models\Setting::get('google_site_verification'))
    <meta name="google-site-verification" content="{{ $gVerification }}">
    @endif
    @if($bVerification = \App\Models\Setting::get('bing_site_verification'))
    <meta name="msvalidate.01" content="{{ $bVerification }}">
    @endif
    
    <!-- PWA & Mobile Web App Capabilities -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#F5EFE6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ \App\Models\Setting::get('site_name', 'ROMHUB') }}">

    <!-- OpenGraph / Twitter Cards / Social Sharing -->
    <meta property="og:site_name" content="{{ \App\Models\Setting::get('site_name', 'ROMHUB') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:title" content="@yield('og_title', \App\Models\Setting::get('seo_meta_title', 'ROMHUB — Retro ROMs & Emulators'))">
    <meta property="og:description" content="@yield('og_description', \App\Models\Setting::get('seo_meta_description', 'Preservación de Videojuegos Clásicos y Emuladores.'))">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', \App\Models\Setting::get('seo_meta_title', 'ROMHUB'))">
    <meta name="twitter:description" content="@yield('og_description', \App\Models\Setting::get('seo_meta_description', 'Preservación de Videojuegos Clásicos'))">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-default.jpg'))">

    <!-- Schema.org JSON-LD Structured Data for Google Search -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "name": "{{ \App\Models\Setting::get('site_name', 'ROMHUB') }}",
        "url": "{{ url('/') }}",
        "description": "{{ \App\Models\Setting::get('seo_meta_description', 'Preservación de Videojuegos') }}",
        "potentialAction": {
            "@@type": "SearchAction",
            "target": "{{ route('search') }}?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    @stack('schema')

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ \App\Models\Setting::get('site_favicon_url') ?: asset('favicon.ico') }}">

    <!-- Google Fonts: Bricolage Grotesque, Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide CDN for immediate icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Tailwind & Alpine compiled via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        retro: {
                            bg: '#F5EFE6',
                            card: '#FFFFFF',
                            surface: '#FAF7F2',
                            red: '#CE2D2D',
                            redHover: '#B71C1C',
                            dark: '#18181B',
                            border: '#E5E0D8',
                            borderDark: '#1E1E1E',
                            muted: '#71717A',
                            pill: '#EDE7DE',
                        }
                    },
                    fontFamily: {
                        heading: ['"Bricolage Grotesque"', 'sans-serif'],
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }
        h1, h2, .font-heading {
            font-family: 'Bricolage Grotesque', sans-serif !important;
            letter-spacing: -0.02em;
        }
        .card-game-title, .font-sans {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
            letter-spacing: -0.01em;
        }
        /* Markdown / Rich Game Content Typography */
        .prose-game-content h1, .prose-game-content h2, .prose-game-content h3, .prose-game-content h4 {
            color: #18181B;
            font-family: 'Bricolage Grotesque', sans-serif !important;
            font-weight: 800;
            letter-spacing: -0.01em;
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
        }
        .prose-game-content h1 { font-size: 1.3rem; color: #CE2D2D; border-bottom: none !important; }
        .prose-game-content h2 { 
            font-size: 1.125rem; 
            color: #18181B; 
            border-bottom: none !important; 
            padding-bottom: 0; 
            margin-top: 1.5rem;
        }
        .prose-game-content h3 { font-size: 0.975rem; color: #3F3F46; margin-top: 1rem; border-bottom: none !important; }
        .prose-game-content p {
            color: #52525B;
            font-size: 0.875rem;
            line-height: 1.7;
            margin-bottom: 0.85rem;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .prose-game-content strong {
            color: #18181B;
            font-weight: 700;
        }
        .prose-game-content ul, .prose-game-content ol {
            margin-top: 0.5rem;
            margin-bottom: 0.85rem;
            padding-left: 1.25rem;
            list-style-type: disc;
        }
        .prose-game-content li {
            color: #52525B;
            font-size: 0.875rem;
            line-height: 1.65;
            margin-bottom: 0.35rem;
        }
        .prose-game-content hr {
            display: none !important;
        }
        .prose-game-content code {
            font-family: 'JetBrains Mono', monospace;
            background-color: #EDE7DE;
            border: 1px solid #DDD6CB;
            color: #CE2D2D;
            padding: 0.15rem 0.35rem;
            border-radius: 0.375rem;
            font-size: 0.8rem;
        }

        /* ================= DARK VAULT THEME ENHANCEMENTS ================= */
        html.dark {
            color-scheme: dark;
        }
        html.dark body {
            background-color: #0F0F12 !important;
            color: #F4F4F5 !important;
        }
        html.dark header,
        html.dark .bg-white,
        html.dark .bg-\[\#FFFFFF\] {
            background-color: #18181B !important;
            color: #F4F4F5 !important;
            border-color: #27272A !important;
        }
        html.dark .console-card-badge,
        html.dark .bg-white\/95,
        html.dark .bg-white\/90 {
            background-color: rgba(24, 24, 27, 0.94) !important;
            color: #FFFFFF !important;
            border-color: #3F3F46 !important;
        }
        html.dark .bg-\[\#FAF7F2\],
        html.dark .bg-\[\#EDE7DE\],
        html.dark .bg-\[\#F5EFE6\],
        html.dark .bg-\[\#F8FAFC\],
        html.dark .bg-\[\#FFFDF5\] {
            background-color: #202025 !important;
            color: #E4E4E7 !important;
            border-color: #2E2E35 !important;
        }
        html.dark .border-\[\#E5E0D8\],
        html.dark .border-\[\#DDD6CB\],
        html.dark .border-\[\#1E1E1E\] {
            border-color: #27272A !important;
        }
        html.dark main section {
            border-color: #27272A !important;
        }
        html.dark .text-\[\#18181B\],
        html.dark .text-black {
            color: #F4F4F5 !important;
        }
        html.dark .text-gray-700,
        html.dark .text-gray-600 {
            color: #CBD5E1 !important;
        }
        html.dark .text-gray-500,
        html.dark .text-gray-400 {
            color: #94A3B8 !important;
        }
        html.dark input,
        html.dark textarea,
        html.dark select {
            background-color: #18181B !important;
            color: #FFFFFF !important;
            border-color: #33333C !important;
        }
        html.dark .bg-\[\#FDF2F2\] {
            background-color: #2B1616 !important;
            border-color: #632323 !important;
            color: #FCA5A5 !important;
        }
        html.dark .border-\[\#FCA5A5\] {
            border-color: #632323 !important;
        }
        html.dark .prose-game-content h1,
        html.dark .prose-game-content h2,
        html.dark .prose-game-content h3,
        html.dark .prose-game-content h4 {
            color: #F4F4F5 !important;
            border-bottom: none !important;
            padding-bottom: 0 !important;
        }
        html.dark .prose-game-content hr {
            display: none !important;
        }
        html.dark .prose-game-content p,
        html.dark .prose-game-content li {
            color: #CBD5E1 !important;
        }
        html.dark .prose-game-content strong {
            color: #FFFFFF !important;
        }
        html.dark .prose-game-content code {
            background-color: #202025 !important;
            border-color: #33333C !important;
            color: #F87171 !important;
        }

        /* ================= NAV CAPSULE STYLES (RETRO & DARK) ================= */
        nav.nav-capsule {
            background-color: #EDE7DE !important;
            border-color: #DDD6CB !important;
        }
        nav.nav-capsule a,
        nav.nav-capsule button {
            color: #4B5563 !important;
            transition: all 0.15s ease-in-out;
        }
        nav.nav-capsule a i,
        nav.nav-capsule a svg,
        nav.nav-capsule button i,
        nav.nav-capsule button svg {
            color: #6B7280;
            transition: color 0.15s ease-in-out;
        }
        nav.nav-capsule a:hover,
        nav.nav-capsule button:hover {
            color: #18181B !important;
            background-color: #FFFFFF !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06) !important;
        }
        nav.nav-capsule a:hover i,
        nav.nav-capsule a:hover svg,
        nav.nav-capsule button:hover i,
        nav.nav-capsule button:hover svg {
            color: #CE2D2D !important;
        }
        nav.nav-capsule a.nav-item-active,
        nav.nav-capsule button.nav-item-active {
            background-color: #FFFFFF !important;
            color: #18181B !important;
            font-weight: 800 !important;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1) !important;
        }
        nav.nav-capsule a.nav-item-active i,
        nav.nav-capsule a.nav-item-active svg,
        nav.nav-capsule button.nav-item-active i,
        nav.nav-capsule button.nav-item-active svg {
            color: #CE2D2D !important;
        }

        /* Dark Vault Theme for Nav Capsule */
        html.dark nav.nav-capsule {
            background-color: #131317 !important;
            border-color: #27272A !important;
        }
        html.dark nav.nav-capsule a,
        html.dark nav.nav-capsule button {
            color: #94A3B8 !important;
        }
        html.dark nav.nav-capsule a i,
        html.dark nav.nav-capsule a svg,
        html.dark nav.nav-capsule button i,
        html.dark nav.nav-capsule button svg {
            color: #94A3B8 !important;
        }
        html.dark nav.nav-capsule a:hover,
        html.dark nav.nav-capsule button:hover {
            color: #FFFFFF !important;
            background-color: #272730 !important;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4) !important;
        }
        html.dark nav.nav-capsule a:hover span,
        html.dark nav.nav-capsule button:hover span {
            color: #FFFFFF !important;
        }
        html.dark nav.nav-capsule a:hover i,
        html.dark nav.nav-capsule a:hover svg,
        html.dark nav.nav-capsule button:hover i,
        html.dark nav.nav-capsule button:hover svg {
            color: #EF4444 !important;
        }
        html.dark nav.nav-capsule a.nav-item-active,
        html.dark nav.nav-capsule button.nav-item-active {
            background-color: #272730 !important;
            color: #FFFFFF !important;
            font-weight: 800 !important;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.5) !important;
            border: 1px solid #3F3F46 !important;
        }
        html.dark nav.nav-capsule a.nav-item-active span,
        html.dark nav.nav-capsule button.nav-item-active span {
            color: #FFFFFF !important;
        }
        html.dark nav.nav-capsule a.nav-item-active i,
        html.dark nav.nav-capsule a.nav-item-active svg,
        html.dark nav.nav-capsule button.nav-item-active i,
        html.dark nav.nav-capsule button.nav-item-active svg {
            color: #EF4444 !important;
        }
    </style>
</head>
@php
    $containerWidth = \App\Models\Setting::get('container_max_width', 'max-w-[1200px]');
    $menuCategories = \App\Models\Category::withCount(['games' => fn($q) => $q->where('status', 'PUBLISHED')])
        ->orderBy('name')
        ->get();
    $catIconMap = [
        'accion-aventura' => 'swords',
        'rpg-jrpg' => 'sparkles',
        'plataformas' => 'layers',
        'lucha' => 'flame',
        'shooter-fps' => 'crosshair',
        'carreras' => 'gauge',
        'terror-survival' => 'skull',
        'estrategia' => 'shield',
        'deportes' => 'trophy',
    ];
@endphp
<body class="min-h-screen flex flex-col bg-[#F5EFE6] text-[#18181B] selection:bg-[#CE2D2D] selection:text-white" 
      x-data="{ 
          mobileMenuOpen: false,
          downloadModalOpen: false, 
          selectedGame: null,
          isDark: document.documentElement.classList.contains('dark'),
          toggleTheme() {
              this.isDark = !this.isDark;
              if (this.isDark) {
                  document.documentElement.classList.add('dark');
                  localStorage.setItem('theme', 'dark');
              } else {
                  document.documentElement.classList.remove('dark');
                  localStorage.setItem('theme', 'light');
              }
          }
      }">

    <!-- Top Retro Multi-Color Spectrum Line -->
    <div class="h-[3px] w-full flex overflow-hidden shadow-sm">
        <div class="h-full flex-1 bg-[#FF5347]"></div>
        <div class="h-full flex-1 bg-[#FF9A45]"></div>
        <div class="h-full flex-1 bg-[#FFC93F]"></div>
        <div class="h-full flex-1 bg-[#4FBE78]"></div>
        <div class="h-full flex-1 bg-[#4B92E8]"></div>
        <div class="h-full flex-1 bg-[#A276DC]"></div>
    </div>

    <!-- Navigation Bar (Estático - No se mueve con el scroll) -->
    <div class="pt-3 sm:pt-4 px-3 sm:px-6 w-full mb-4">
        <header class="{{ $containerWidth }} mx-auto bg-white dark:bg-[#18181B] border border-[#E5E0D8] dark:border-[#27272A] rounded-2xl px-4 lg:px-5 py-2.5 shadow-sm flex items-center justify-between gap-4 transition-all">
            
            <!-- Left: Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group shrink-0">
                @if(\App\Models\Setting::get('site_logo_type') === 'image' && \App\Models\Setting::get('site_logo_url'))
                    <img src="{{ \App\Models\Setting::get('site_logo_url') }}" alt="{{ \App\Models\Setting::get('site_name', 'ROMHUB') }}" class="h-8 w-auto object-contain">
                @else
                    <div class="w-8 h-8 rounded-xl bg-[#CE2D2D] flex items-center justify-center text-white font-black text-sm shadow-md shadow-red-500/20 group-hover:scale-105 transition-transform shrink-0">
                        <i data-lucide="{{ \App\Models\Setting::get('site_logo_icon', 'gamepad-2') }}" class="w-4.5 h-4.5 text-white"></i>
                    </div>
                    <div class="flex items-baseline font-sans">
                        <span class="font-black italic tracking-wider text-[#18181B] text-base sm:text-lg uppercase">{{ \App\Models\Setting::get('site_logo_prefix', 'PICHO') }}</span>
                        <span class="text-[#CE2D2D] font-black not-italic text-base sm:text-lg uppercase ml-0.5">{{ \App\Models\Setting::get('site_logo_suffix', 'Roms') }}</span>
                    </div>
                @endif
            </a>

            <!-- Center: Navigation Pill Capsule -->
            <nav class="nav-capsule hidden md:flex items-center gap-1 bg-[#EDE7DE] border border-[#DDD6CB] rounded-xl p-1 shadow-inner shrink-0"
                 x-data="{ genreOpen: false }">
                <!-- 1. HOME -->
                @php $isHome = request()->routeIs('home') && !request('category'); @endphp
                <a href="{{ route('home') }}" 
                   class="px-3.5 py-1.5 rounded-lg flex items-center gap-2 text-xs font-semibold transition-all {{ $isHome ? 'nav-item-active' : 'nav-item-inactive' }}">
                    <i data-lucide="home" class="w-4 h-4 {{ $isHome ? 'text-[#CE2D2D]' : 'text-gray-500' }}"></i>
                    <span>HOME</span>
                </a>

                <!-- 2. Consolas con contador -->
                @php $isConsoles = request()->routeIs('consoles.*'); @endphp
                <a href="{{ route('consoles.index') }}" 
                   class="px-3.5 py-1.5 rounded-lg flex items-center gap-2 text-xs font-semibold transition-all {{ $isConsoles ? 'nav-item-active' : 'nav-item-inactive' }}">
                    <i data-lucide="gamepad-2" class="w-4 h-4 {{ $isConsoles ? 'text-[#CE2D2D]' : 'text-gray-500' }}"></i>
                    <span>Consolas</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded border font-mono font-bold {{ $isConsoles ? 'bg-[#CE2D2D] text-white border-[#CE2D2D]' : 'bg-[#FAF7F2] text-[#CE2D2D] border-[#DDD6CB]' }}">20</span>
                </a>

                <!-- 3. Género Dropdown (DIRECTO DESPUÉS DE CONSOLAS) -->
                @php $isCategory = request()->has('category'); @endphp
                <div class="relative" 
                     @click.outside="genreOpen = false" 
                     @keydown.escape.window="genreOpen = false">
                    <button type="button" 
                            @click="genreOpen = !genreOpen; $nextTick(() => { if (window.lucide) { lucide.createIcons(); } })"
                            class="px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 text-xs font-semibold transition-all {{ ($isCategory || request()->routeIs('categories.*')) ? 'nav-item-active' : 'nav-item-inactive' }}"
                            :class="genreOpen ? 'nav-item-active' : ''">
                        <i data-lucide="tag" class="w-4 h-4 {{ ($isCategory || request()->routeIs('categories.*')) ? 'text-[#CE2D2D]' : 'text-gray-500' }}" :class="genreOpen ? 'text-[#CE2D2D]' : ''"></i>
                        <span>Género</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-500 transition-transform duration-200" :class="genreOpen ? 'rotate-180 text-[#CE2D2D]' : ''"></i>
                    </button>

                    <!-- Dropdown Mega Menu -->
                    <div x-show="genreOpen" 
                         x-cloak 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         class="absolute left-0 mt-3 w-[460px] sm:w-[520px] max-w-[calc(100vw-2rem)] bg-white dark:bg-[#18181B] border border-[#E5E0D8] dark:border-[#27272A] rounded-2xl shadow-2xl p-4 z-[100]">
                        
                        <div class="flex items-center justify-between px-2 pb-3 mb-3 border-b border-[#E5E0D8] dark:border-[#27272A]">
                            <span class="text-xs font-mono font-bold text-[#18181B] dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i data-lucide="shapes" class="w-4 h-4 text-[#CE2D2D]"></i>
                                Filtrar por Género
                            </span>
                            <span class="text-[11px] font-mono text-gray-500 dark:text-gray-400">{{ $menuCategories->count() }} categorías</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[380px] overflow-y-auto pr-1">
                            @foreach($menuCategories as $cat)
                                @php
                                    $cIcon = $catIconMap[$cat->slug] ?? ($cat->icon ?: 'tag');
                                    if ($cIcon === 'chess-knight') { $cIcon = 'shield'; }
                                    $isCatActive = request('category') === $cat->slug;
                                @endphp
                                <a href="{{ route('search', ['category' => $cat->slug]) }}" 
                                   class="flex items-center gap-3 p-2.5 rounded-xl border transition-all group {{ $isCatActive ? 'bg-[#FDF2F2] dark:bg-[#2B1616] border-[#FCA5A5] dark:border-[#632323] text-[#CE2D2D] shadow-sm' : 'bg-[#FAF7F2] dark:bg-[#202025] hover:bg-white dark:hover:bg-[#272730] border-[#E5E0D8] dark:border-[#2E2E35] hover:border-[#CE2D2D]/40 text-[#18181B] dark:text-[#E4E4E7]' }}">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 shadow-sm"
                                         style="background-color: {{ $cat->color ? $cat->color.'20' : '#CE2D2D20' }}; color: {{ $cat->color ?: '#CE2D2D' }};">
                                        <i data-lucide="{{ $cIcon }}" class="w-4 h-4 group-hover:scale-110 transition-transform"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold truncate leading-tight {{ $isCatActive ? 'text-[#CE2D2D]' : 'group-hover:text-[#CE2D2D]' }}">{{ $cat->name }}</p>
                                        <p class="text-[10px] font-mono text-gray-500 dark:text-gray-400 mt-0.5">{{ $cat->games_count }} {{ $cat->games_count == 1 ? 'juego disponible' : 'juegos disponibles' }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <div class="pt-3 mt-3 border-t border-[#E5E0D8] dark:border-[#27272A] flex items-center justify-between px-2 text-xs font-mono">
                            <a href="{{ route('search') }}" class="text-[#CE2D2D] hover:underline flex items-center gap-1.5 transition-colors font-medium">
                                <span>Ver catálogo completo con filtros</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 4. Top 25 Rankings (AL LADO DE GÉNERO) -->
                @php $isRankings = request()->routeIs('rankings*'); @endphp
                <a href="{{ route('rankings') }}" 
                   class="px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 text-xs font-semibold transition-all {{ $isRankings ? 'nav-item-active' : 'nav-item-inactive' }}">
                    <i data-lucide="trophy" class="w-4 h-4 {{ $isRankings ? 'text-[#CE2D2D]' : 'text-gray-500' }}"></i>
                    <span>Top 25</span>
                </a>

                <!-- 5. Sagas / Colecciones -->
                @php $isCollections = request()->routeIs('collections.*'); @endphp
                <a href="{{ route('collections.index') }}" 
                   class="px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 text-xs font-semibold transition-all {{ $isCollections ? 'nav-item-active' : 'nav-item-inactive' }}">
                    <i data-lucide="sparkles" class="w-4 h-4 {{ $isCollections ? 'text-[#CE2D2D]' : 'text-gray-500' }}"></i>
                    <span>Sagas</span>
                </a>

                <!-- 6. Emuladores -->
                @php $isEmulators = request()->routeIs('emulators*'); @endphp
                <a href="{{ route('emulators') }}" 
                   class="px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 text-xs font-semibold transition-all {{ $isEmulators ? 'nav-item-active' : 'nav-item-inactive' }}">
                    <i data-lucide="cpu" class="w-4 h-4 {{ $isEmulators ? 'text-[#CE2D2D]' : 'text-gray-500' }}"></i>
                    <span>Emuladores</span>
                </a>

                <!-- 7. BIOS -->
                @php $isBios = request()->routeIs('bios*'); @endphp
                <a href="{{ route('bios') }}" 
                   class="px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 text-xs font-semibold transition-all {{ $isBios ? 'nav-item-active' : 'nav-item-inactive' }}">
                    <i data-lucide="binary" class="w-4 h-4 {{ $isBios ? 'text-[#CE2D2D]' : 'text-gray-500' }}"></i>
                    <span>BIOS</span>
                </a>
            </nav>

            <!-- Right Section: Novedades CTA, Theme Switcher & Mobile Controls -->
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                
                <!-- Quick Mobile Search Button -->
                <a href="{{ route('search') }}" 
                   class="md:hidden p-2 rounded-xl text-gray-700 hover:text-[#CE2D2D] hover:bg-[#FAF7F2] border border-transparent hover:border-[#E5E0D8] transition-colors" 
                   title="Buscador">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </a>

                <!-- Dark Vault / Retro Cream Theme Switcher Button (Icon Only) -->
                <button type="button" 
                        @click="toggleTheme()" 
                        class="w-9 h-9 rounded-xl bg-[#EDE7DE] dark:bg-[#202025] hover:bg-[#E2DACF] dark:hover:bg-[#272730] text-[#18181B] dark:text-white border border-[#DDD6CB] dark:border-[#2E2E35] hover:border-[#CE2D2D]/50 transition-all shadow-sm flex items-center justify-center cursor-pointer group shrink-0 relative"
                        :title="isDark ? 'Cambiar a Modo Claro (Retro Cream)' : 'Cambiar a Modo Oscuro (Dark Vault)'"
                        aria-label="Cambiar tema">
                    <!-- Moon Icon (Retro Cream / Light Mode) -->
                    <svg x-show="!isDark" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-gray-700 group-hover:text-[#CE2D2D] transition-transform group-hover:-rotate-12 pointer-events-none">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                    </svg>
                    <!-- Sun Icon (Dark Vault / Dark Mode) -->
                    <svg x-show="isDark" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-amber-400 group-hover:text-amber-300 transition-transform group-hover:rotate-45 pointer-events-none">
                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2"></path>
                        <path d="M12 20v2"></path>
                        <path d="m4.93 4.93 1.41 1.41"></path>
                        <path d="m17.66 17.66 1.41 1.41"></path>
                        <path d="M2 12h2"></path>
                        <path d="M20 12h2"></path>
                        <path d="m6.34 17.66-1.41 1.41"></path>
                        <path d="m19.07 4.93-1.41 1.41"></path>
                    </svg>
                </button>

                <!-- Novedades CTA Pill Button -->
                <a href="{{ route('home') }}#novedades" 
                   class="hidden xs:flex px-3 sm:px-3.5 py-1.5 rounded-xl bg-[#EDE7DE] hover:bg-[#E2DACF] text-[#18181B] border border-[#DDD6CB] hover:border-[#CE2D2D]/50 text-xs font-bold items-center gap-1.5 transition-all shadow-sm group">
                    <span>Novedades</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-gray-500 group-hover:text-[#CE2D2D] group-hover:translate-x-0.5 transition-all"></i>
                </a>

                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="p-1.5 rounded-lg bg-amber-500/10 text-amber-700 border border-amber-500/30 hover:bg-amber-500/20 text-xs font-semibold flex items-center gap-1 transition-colors" title="Panel Administrador">
                            <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                @endauth

                <!-- Mobile Hamburger Toggle -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="md:hidden p-2 rounded-xl text-gray-700 hover:text-black hover:bg-black/5 transition-colors focus:outline-none"
                        aria-label="Abrir menú">
                    <i data-lucide="menu" class="w-5 h-5" x-show="!mobileMenuOpen"></i>
                    <i data-lucide="x" class="w-5 h-5" x-show="mobileMenuOpen" x-cloak></i>
                </button>
            </div>

        </header>

        <!-- Mobile Menu Dropdown -->
        <div x-show="mobileMenuOpen" 
             x-cloak 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
             @click.outside="mobileMenuOpen = false"
             class="md:hidden {{ $containerWidth }} mx-auto mt-2 p-3.5 bg-white dark:bg-[#18181B] border border-[#E5E0D8] dark:border-[#27272A] rounded-2xl space-y-3 shadow-2xl">
            
            <div class="space-y-1.5">
                <!-- Mobile Theme Switcher -->
                <button type="button" 
                        @click="toggleTheme()" 
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-[#FAF7F2] dark:hover:bg-[#202025] transition-colors border border-[#DDD6CB] dark:border-[#27272A]">
                    <span class="flex items-center gap-3">
                        <span class="w-4 h-4 flex items-center justify-center shrink-0">
                            <svg x-show="!isDark" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-[#CE2D2D]"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path></svg>
                            <svg x-show="isDark" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-amber-400"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>
                        </span>
                        <span x-text="isDark ? 'Cambiar a Modo Claro' : 'Cambiar a Modo Oscuro'"></span>
                    </span>
                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-[#EDE7DE] dark:bg-[#272730] border border-[#DDD6CB] dark:border-[#3F3F46]" x-text="isDark ? 'Dark Vault 🌙' : 'Retro Cream ☀️'"></span>
                </button>
                <!-- 1. HOME -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('home') && !request('category') ? 'bg-[#FDF2F2] dark:bg-[#2B1616] text-[#CE2D2D] font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-[#FAF7F2] dark:hover:bg-[#202025]' }}">
                    <i data-lucide="home" class="w-4 h-4 text-[#CE2D2D]"></i> HOME
                </a>

                <!-- 2. Consolas -->
                <a href="{{ route('consoles.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('consoles.*') ? 'bg-[#FDF2F2] dark:bg-[#2B1616] text-[#CE2D2D] font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-[#FAF7F2] dark:hover:bg-[#202025]' }}">
                    <span class="flex items-center gap-3">
                        <i data-lucide="gamepad-2" class="w-4 h-4 text-[#CE2D2D]"></i> Consolas
                    </span>
                    <span class="bg-[#F5EFE6] dark:bg-[#272730] text-[#CE2D2D] text-[10px] px-2 py-0.5 rounded-md border border-[#DDD6CB] dark:border-[#3F3F46] font-mono font-bold">20</span>
                </a>

                <!-- 3. Género (Móvil) Accordion (DESPUÉS DE CONSOLAS) -->
                <div x-data="{ mobileGenreOpen: false }" class="space-y-1.5 pt-1">
                    <button type="button" 
                            @click="mobileGenreOpen = !mobileGenreOpen; $nextTick(() => { if (window.lucide) { lucide.createIcons(); } })" 
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-[#FAF7F2] dark:bg-[#202025] text-[#18181B] dark:text-[#E4E4E7] border border-[#E5E0D8] dark:border-[#27272A] transition-colors">
                        <span class="flex items-center gap-3">
                            <i data-lucide="tag" class="w-4 h-4 text-[#CE2D2D]"></i>
                            <span class="font-bold">Géneros ({{ $menuCategories->count() }})</span>
                        </span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-gray-500" :class="mobileGenreOpen ? 'rotate-180 text-[#CE2D2D]' : ''"></i>
                    </button>

                    <!-- Mobile 2-column touch cards -->
                    <div x-show="mobileGenreOpen" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 pt-1 max-h-72 overflow-y-auto pr-1">
                        @foreach($menuCategories as $cat)
                            @php
                                $cIcon = $catIconMap[$cat->slug] ?? ($cat->icon ?: 'tag');
                                if ($cIcon === 'chess-knight') { $cIcon = 'shield'; }
                                $isCatActive = request('category') === $cat->slug;
                            @endphp
                            <a href="{{ route('search', ['category' => $cat->slug]) }}" 
                               class="flex items-center gap-2.5 p-2 rounded-xl border transition-all {{ $isCatActive ? 'bg-[#FDF2F2] dark:bg-[#2B1616] border-[#FCA5A5] dark:border-[#632323] text-[#CE2D2D]' : 'bg-[#FAF7F2] dark:bg-[#202025] hover:bg-white dark:hover:bg-[#272730] border-[#E5E0D8] dark:border-[#27272A] text-gray-700 dark:text-gray-300' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" 
                                     style="background-color: {{ $cat->color ? $cat->color.'20' : '#CE2D2D20' }}; color: {{ $cat->color ?: '#CE2D2D' }};">
                                    <i data-lucide="{{ $cIcon }}" class="w-3.5 h-3.5"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="text-xs font-bold truncate block leading-tight">{{ $cat->name }}</span>
                                    <span class="text-[10px] font-mono text-gray-500 dark:text-gray-400 block">{{ $cat->games_count }} juegos</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- 4. Top 25 Rankings (AL LADO DE GÉNERO) -->
                <a href="{{ route('rankings') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('rankings*') ? 'bg-[#FDF2F2] dark:bg-[#2B1616] text-[#CE2D2D] font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-[#FAF7F2] dark:hover:bg-[#202025]' }}">
                    <i data-lucide="trophy" class="w-4 h-4 text-[#CE2D2D]"></i> Top 25 Más Jugados
                </a>

                <!-- 5. Sagas / Colecciones -->
                <a href="{{ route('collections.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('collections.*') ? 'bg-[#FDF2F2] dark:bg-[#2B1616] text-[#CE2D2D] font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-[#FAF7F2] dark:hover:bg-[#202025]' }}">
                    <i data-lucide="sparkles" class="w-4 h-4 text-[#CE2D2D]"></i> Sagas & Colecciones
                </a>

                <!-- 6. Emuladores Oficiales -->
                <a href="{{ route('emulators') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('emulators') ? 'bg-[#FDF2F2] dark:bg-[#2B1616] text-[#CE2D2D] font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-[#FAF7F2] dark:hover:bg-[#202025]' }}">
                    <i data-lucide="cpu" class="w-4 h-4 text-[#CE2D2D]"></i> Emuladores Recomendados
                </a>

                <!-- 7. BIOS & Firmwares -->
                <a href="{{ route('bios') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('bios') ? 'bg-[#FDF2F2] dark:bg-[#2B1616] text-[#CE2D2D] font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-[#FAF7F2] dark:hover:bg-[#202025]' }}">
                    <i data-lucide="binary" class="w-4 h-4 text-[#CE2D2D]"></i> BIOS & Firmware
                </a>
            </div>

                <!-- Novedades -->
                <a href="{{ route('home') }}#novedades" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold bg-[#EDE7DE] hover:bg-[#E2DACF] text-[#18181B] border border-[#DDD6CB]">
                    <span>Novedades Recientes</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-gray-500"></i>
                </a>

                <!-- Mobile Quick Links: Contacto & Admin -->
                <div class="pt-2 border-t border-[#E5E0D8] space-y-2 text-xs">
                    <a href="{{ route('contact') }}" class="flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-[#FAF7F2] border border-[#E5E0D8] text-gray-700 hover:text-black">
                        <i data-lucide="mail" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                        <span>Contacto</span>
                    </a>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 font-semibold">
                                <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                                <span>Panel Administrador</span>
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>

    <!-- Main Content Injection -->
    <div class="flex-1">
        @yield('content')
    </div>

    <!-- Toast Notification Container -->
    <div x-data="{ show: false, message: '' }" 
         x-on:toast-notify.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 3500)"
         x-show="show" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         x-cloak
         class="fixed bottom-4 right-4 z-50 bg-[#18181B] px-4 py-2.5 rounded-xl border border-black shadow-2xl flex items-center gap-2 text-xs text-white">
        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
        <span x-text="message"></span>
    </div>

    <!-- Application Footer (Retro Cream Aesthetic) -->
    <footer class="border-t border-[#E5E0D8] bg-[#FAF7F2] mt-16 py-10 px-4 lg:px-6 text-xs text-gray-600 font-mono">
        <div class="{{ $containerWidth }} mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-3">
                @if(\App\Models\Setting::get('site_logo_type') === 'image' && \App\Models\Setting::get('site_logo_url'))
                    <img src="{{ \App\Models\Setting::get('site_logo_url') }}" alt="{{ \App\Models\Setting::get('site_name', 'ROMHUB') }}" class="h-8 w-auto object-contain">
                @else
                    <div class="w-7 h-7 rounded-lg bg-[#CE2D2D] flex items-center justify-center text-white font-black text-xs shrink-0">
                        <i data-lucide="{{ \App\Models\Setting::get('site_logo_icon', 'disc') }}" class="w-4 h-4 text-white"></i>
                    </div>
                @endif
                <div>
                    @php
                        $footerTitle = \App\Models\Setting::get('footer_title');
                        if (empty($footerTitle)) {
                            $footerTitle = \App\Models\Setting::get('site_logo_prefix', 'ROM') . \App\Models\Setting::get('site_logo_suffix', 'HUB') . ' RETRO ARCHIVE';
                        }
                        $footerDescription = \App\Models\Setting::get('footer_description', 'Preservación digital conforme a estándares No-Intro & Redump.');
                        $defaultCopyright = '© ' . date('Y') . ' ' . \App\Models\Setting::get('site_name', 'ROMHUB') . ' Inc. Todos los derechos reservados.';
                        $footerCopyright = \App\Models\Setting::get('footer_copyright', $defaultCopyright);
                        if (empty($footerCopyright)) {
                            $footerCopyright = $defaultCopyright;
                        }
                    @endphp
                    <strong class="text-[#18181B] font-sans text-sm block font-bold">{{ $footerTitle }}</strong>
                    @if(!empty($footerDescription))
                        <p class="text-[11px] text-gray-500 font-sans mt-0.5">{{ $footerDescription }}</p>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-gray-700 font-sans text-xs font-semibold">
                <a href="{{ route('home') }}" class="hover:text-[#CE2D2D] transition-colors">Inicio</a>
                <a href="{{ route('consoles.index') }}" class="hover:text-[#CE2D2D] transition-colors">Consolas</a>
                <a href="{{ route('collections.index') }}" class="hover:text-[#CE2D2D] transition-colors">Sagas</a>
                <a href="{{ route('emulators') }}" class="hover:text-[#CE2D2D] transition-colors">Emuladores</a>
                <a href="{{ route('bios') }}" class="hover:text-[#CE2D2D] transition-colors">BIOS</a>
                <a href="{{ route('search') }}" class="hover:text-[#CE2D2D] transition-colors">Buscador</a>
                <a href="{{ route('dmca') }}" class="hover:text-[#CE2D2D] transition-colors">DMCA</a>
                <a href="{{ route('contact') }}" class="hover:text-[#CE2D2D] transition-colors">Contacto</a>
            </div>

            <p class="text-[11px] text-gray-500">{{ $footerCopyright }}</p>
        </div>
    </footer>

    <!-- Bottom Retro Multi-Color Spectrum Line -->
    <div class="h-[3px] w-full flex overflow-hidden">
        <div class="h-full flex-1 bg-[#FF5347]"></div>
        <div class="h-full flex-1 bg-[#FF9A45]"></div>
        <div class="h-full flex-1 bg-[#FFC93F]"></div>
        <div class="h-full flex-1 bg-[#4FBE78]"></div>
        <div class="h-full flex-1 bg-[#4B92E8]"></div>
        <div class="h-full flex-1 bg-[#A276DC]"></div>
    </div>

    <script>
        lucide.createIcons();

        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').catch(function() {});
            });
        }
    </script>
</body>
</html>
