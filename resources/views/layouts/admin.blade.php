<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', \App\Models\Setting::get('site_name', 'ROMHUB') . ' — Panel de Administración Central')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ \App\Models\Setting::get('site_favicon_url') ?: asset('favicon.ico') }}">

    <!-- Google Fonts: Bricolage Grotesque, Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Tailwind & Alpine compiled via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Bricolage Grotesque', sans-serif !important;
            letter-spacing: -0.02em;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-[#0A0C0F] text-[#E5E7EB] selection:bg-blue-600 selection:text-white">

    <!-- Admin Top Application Control Bar -->
    <header class="border-b border-[#232936] bg-[#11141A] sticky top-0 z-40">
        <div class="max-w-[1600px] mx-auto px-4 lg:px-6 h-14 flex items-center justify-between gap-4">
            
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                    @if(\App\Models\Setting::get('site_logo_type') === 'image' && \App\Models\Setting::get('site_logo_url'))
                        <img src="{{ \App\Models\Setting::get('site_logo_url') }}" alt="Admin" class="h-6 w-auto object-contain">
                    @else
                        <div class="w-6 h-6 rounded bg-blue-600 flex items-center justify-center text-white font-black text-xs shrink-0">
                            <i data-lucide="{{ \App\Models\Setting::get('site_logo_icon', 'layers') }}" class="w-3.5 h-3.5 text-white"></i>
                        </div>
                    @endif
                    <span class="font-bold text-white tracking-tight text-sm font-sans">
                        {{ \App\Models\Setting::get('site_logo_prefix', 'ROM') }}<span class="text-blue-500">{{ \App\Models\Setting::get('site_logo_suffix', 'HUB') }}</span> 
                        <span class="text-xs text-gray-400 font-normal">/ Admin Suite</span>
                    </span>
                </a>

                <div class="h-4 w-px bg-[#232936]"></div>

                <a href="{{ route('home') }}" target="_blank" class="text-xs text-gray-400 hover:text-white font-medium flex items-center gap-1 transition-colors">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Ver Web Pública
                </a>
            </div>

            <!-- Telemetry & Admin User Info -->
            <div class="flex items-center gap-3 text-xs font-mono">
                <div class="hidden md:flex items-center gap-2 px-2.5 py-1 rounded bg-[#0A0C0F] border border-[#232936] text-gray-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>R2 Bucket: <strong class="text-gray-200">{{ \App\Models\Setting::get('r2_bucket', 'romhub-production-vault') }}</strong></span>
                </div>

                <a href="{{ Route::has('admin.profile') ? route('admin.profile') : url('/admin/profile') }}" class="flex items-center gap-2 pl-2 pr-3 py-1 rounded bg-[#171B22] hover:bg-[#232936] border border-[#232936] transition-colors group" title="Editar mi cuenta y contraseña">
                    <div class="w-5 h-5 rounded bg-blue-600 text-white flex items-center justify-center font-bold text-[10px] group-hover:scale-105 transition-transform">
                        {{ substr(auth()->user()->name ?? 'AD', 0, 2) }}
                    </div>
                    <span class="text-gray-200 group-hover:text-white font-sans text-xs font-semibold">{{ auth()->user()->email ?? 'admin@romhub.io' }}</span>
                    <span class="px-1.5 py-0.2 rounded bg-blue-500/20 text-blue-400 text-[10px] font-mono">ADMIN</span>
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-gray-500 group-hover:text-blue-400 ml-0.5"></i>
                </a>
            </div>

        </div>
    </header>

    <!-- Admin Body: Sidebar + Main Content -->
    <div class="flex-1 max-w-[1600px] w-full mx-auto px-4 lg:px-6 py-6 flex flex-col lg:flex-row gap-6">
        
        <!-- Sidebar Navigation (Organized & Categorized) -->
        <aside class="w-full lg:w-64 bg-[#11141A] rounded-2xl border border-[#232936] p-4 flex flex-col justify-between shrink-0 space-y-6 h-fit shadow-xl">
            <div class="space-y-5">
                
                <!-- Sidebar Top Brand Header -->
                <div class="px-2 pb-3 border-b border-[#232936]/80 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-blue-400 font-bold">Panel de Control</div>
                        <div class="text-sm font-extrabold text-white font-heading mt-0.5">Administración</div>
                    </div>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" title="Sistema Operativo"></span>
                </div>

                <nav class="space-y-4 text-xs font-medium font-sans">
                    
                    <!-- GROUP 1: PRINCIPAL -->
                    <div class="space-y-1">
                        <div class="px-2.5 py-1 text-[10px] font-mono uppercase tracking-wider text-gray-500 font-bold">
                            Principal
                        </div>
                        
                        <a href="{{ route('admin.dashboard') }}" 
                           class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
                            <span class="flex items-center gap-2.5">
                                <i data-lucide="layout-dashboard" class="w-4 h-4 text-blue-400"></i> Dashboard
                            </span>
                            <span class="text-[10px] font-mono bg-blue-500/20 px-1.5 py-0.5 rounded text-blue-300 font-bold">Live</span>
                        </a>
                    </div>

                    <!-- GROUP 2: CATÁLOGO & PRESERVACIÓN -->
                    <div class="space-y-1">
                        <div class="px-2.5 py-1 text-[10px] font-mono uppercase tracking-wider text-gray-500 font-bold">
                            Catálogo & Bóveda
                        </div>

                        <!-- Juegos -->
                        <a href="{{ route('admin.games.index') }}" 
                           class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.games.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
                            <span class="flex items-center gap-2.5">
                                <i data-lucide="gamepad-2" class="w-4 h-4"></i> Videojuegos
                            </span>
                            <span class="text-[10px] font-mono text-gray-400 bg-[#0A0C0F] px-2 py-0.5 rounded border border-[#232936]">{{ \App\Models\Game::count() }}</span>
                        </a>

                        <!-- Consolas -->
                        <a href="{{ route('admin.consoles.index') }}" 
                           class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.consoles.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
                            <span class="flex items-center gap-2.5">
                                <i data-lucide="tv" class="w-4 h-4"></i> Consolas (20)
                            </span>
                            <span class="text-[10px] font-mono text-gray-400 bg-[#0A0C0F] px-2 py-0.5 rounded border border-[#232936]">20</span>
                        </a>

                        <!-- Sagas & Franquicias -->
                        <a href="{{ route('admin.franchises.index') }}" 
                           class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.franchises.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
                            <span class="flex items-center gap-2.5">
                                <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i> Sagas & Franquicias
                            </span>
                            <span class="text-[10px] font-mono text-amber-400 bg-amber-950/40 px-2 py-0.5 rounded border border-amber-500/20 font-bold">{{ \App\Models\Franchise::count() }}</span>
                        </a>

                        <!-- BIOS & Firmwares -->
                        <a href="{{ route('admin.bios.index') }}" 
                           class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.bios.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
                            <span class="flex items-center gap-2.5">
                                <i data-lucide="binary" class="w-4 h-4 text-blue-400"></i> Bóveda de BIOS
                            </span>
                            <span class="text-[10px] font-mono text-blue-400 bg-blue-950/40 px-2 py-0.5 rounded border border-blue-500/20 font-bold">{{ \App\Models\Bios::count() }}</span>
                        </a>

                        <!-- Emuladores -->
                        <a href="{{ route('admin.emulators.index') }}" 
                           class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.emulators.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
                            <span class="flex items-center gap-2.5">
                                <i data-lucide="cpu" class="w-4 h-4 text-emerald-400"></i> Emuladores
                            </span>
                            <span class="text-[10px] font-mono text-emerald-400 bg-emerald-950/40 px-2 py-0.5 rounded border border-emerald-500/20 font-bold">{{ \App\Models\Emulator::count() }}</span>
                        </a>

                        <!-- Categorías -->
                        <a href="{{ route('admin.categories.index') }}" 
                           class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.categories.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
                            <span class="flex items-center gap-2.5">
                                <i data-lucide="tag" class="w-4 h-4"></i> Categorías & Géneros
                            </span>
                            <span class="text-[10px] font-mono text-gray-400 bg-[#0A0C0F] px-2 py-0.5 rounded border border-[#232936]">{{ \App\Models\Category::count() }}</span>
                        </a>

                        <!-- Insignias -->
                        <a href="{{ route('admin.badges.index') }}" 
                           class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.badges.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
                            <span class="flex items-center gap-2.5">
                                <i data-lucide="award" class="w-4 h-4"></i> Insignias & Badges
                            </span>
                            <span class="text-[10px] font-mono text-emerald-400 bg-emerald-950/40 px-2 py-0.5 rounded border border-emerald-500/20 font-bold">{{ \App\Models\Badge::count() }}</span>
                        </a>
                    </div>

                    <!-- GROUP 3: CONTENIDO & COMUNIDAD -->
                    <div class="space-y-1">
                        <div class="px-2.5 py-1 text-[10px] font-mono uppercase tracking-wider text-gray-500 font-bold">
                            Contenido & Usuarios
                        </div>

                        <!-- Banners -->
                        <a href="{{ route('admin.banners.index') }}" 
                           class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.banners.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
                            <span class="flex items-center gap-2.5">
                                <i data-lucide="image" class="w-4 h-4"></i> Banners de Portada
                            </span>
                            <span class="text-[10px] font-mono text-gray-400 bg-[#0A0C0F] px-2 py-0.5 rounded border border-[#232936]">{{ \App\Models\Banner::where('is_active', true)->count() }}</span>
                        </a>

                        <!-- Reseñas -->
                        <a href="{{ route('admin.reviews.index') }}" 
                           class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.reviews.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
                            <span class="flex items-center gap-2.5">
                                <i data-lucide="message-square" class="w-4 h-4"></i> Moderación Reseñas
                            </span>
                            @php $pendingCount = \App\Models\Review::where('is_approved', false)->count(); @endphp
                            @if($pendingCount > 0)
                                <span class="text-[10px] font-mono bg-amber-500/20 text-amber-300 border border-amber-500/30 px-1.5 py-0.5 rounded font-bold">{{ $pendingCount }} pendientes</span>
                            @else
                                <span class="text-[10px] font-mono text-gray-500 bg-[#0A0C0F] px-2 py-0.5 rounded border border-[#232936]">{{ \App\Models\Review::count() }}</span>
                            @endif
                        </a>

                        <!-- Usuarios -->
                        <a href="{{ route('admin.users.index') }}" 
                           class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.users.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
                            <span class="flex items-center gap-2.5">
                                <i data-lucide="users" class="w-4 h-4"></i> Usuarios & Roles
                            </span>
                            <span class="text-[10px] font-mono text-gray-400 bg-[#0A0C0F] px-2 py-0.5 rounded border border-[#232936]">{{ \App\Models\User::count() }}</span>
                        </a>
                    </div>

                    <!-- GROUP 4: SISTEMA & SERVICIOS -->
                    <div class="space-y-1">
                        <div class="px-2.5 py-1 text-[10px] font-mono uppercase tracking-wider text-gray-500 font-bold">
                            Sistema & Servicios
                        </div>

                        <!-- Configuración Global -->
                        <a href="{{ route('admin.settings.index') }}" 
                           class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
                            <span class="flex items-center gap-2.5">
                                <i data-lucide="settings" class="w-4 h-4"></i> Configuración Global
                            </span>
                            <span class="text-[10px] font-mono text-purple-400 bg-purple-950/40 border border-purple-500/30 px-1.5 py-0.5 rounded font-bold">Gemini IA</span>
                        </a>

                        <!-- Mi Cuenta & Seguridad -->
                        <a href="{{ Route::has('admin.profile') ? route('admin.profile') : url('/admin/profile') }}" 
                           class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between transition-all {{ request()->is('admin/profile*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
                            <span class="flex items-center gap-2.5">
                                <i data-lucide="shield-check" class="w-4 h-4"></i> Mi Cuenta & Seguridad
                            </span>
                            <span class="text-[10px] font-mono text-blue-400 bg-blue-950/40 border border-blue-500/30 px-1.5 py-0.5 rounded font-bold">Admin</span>
                        </a>
                    </div>

                </nav>
            </div>

            <!-- Server Telemetry & Status Widget -->
            <div class="p-3.5 bg-[#0A0C0F] rounded-xl border border-[#232936] space-y-2.5 text-[11px] font-mono">
                <div class="flex items-center justify-between text-gray-400">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Cloudflare R2:
                    </span>
                    <span class="text-white font-bold">Conectado</span>
                </div>
                <div class="w-full h-1.5 rounded-full bg-[#171B22] border border-[#232936] overflow-hidden">
                    <div class="h-full bg-blue-500 rounded-full" style="width: 42%;"></div>
                </div>
                <div class="flex justify-between items-center text-[10px] text-gray-500">
                    <span>Motor IA: <strong class="text-purple-400">Gemini</strong></span>
                    <span class="text-emerald-400 font-bold">v12.0 Ready</span>
                </div>
            </div>
        </aside>

        <!-- Main Admin Content Area -->
        <section class="flex-1 min-w-0 space-y-6">
            
            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="bg-emerald-950/80 border border-emerald-500/30 text-emerald-300 p-4 rounded-xl text-xs flex items-center gap-2 shadow-md">
                    <i data-lucide="check-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-950/80 border border-rose-500/30 text-rose-300 p-4 rounded-xl text-xs flex items-center gap-2 shadow-md">
                    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 text-rose-400"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </section>

    </div>

    <!-- Toast Notification Container -->
    <div x-data="{ show: false, message: '' }" 
         x-on:toast-notify.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 3500)"
         x-show="show" 
         x-transition
         x-cloak
         class="fixed bottom-4 right-4 z-50 bg-[#171B22] px-4 py-2.5 rounded-lg border border-[#232936] shadow-2xl flex items-center gap-2 text-xs text-white">
        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
        <span x-text="message"></span>
    </div>

    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>
