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
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #232936;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #3B82F6;
        }
    </style>
</head>
<body x-data="{ mobileMenuOpen: false }" 
      x-init="$watch('mobileMenuOpen', value => { if (value) $nextTick(() => { if (window.lucide) window.lucide.createIcons(); }); })"
      class="min-h-screen flex flex-col bg-[#0A0C0F] text-[#E5E7EB] selection:bg-blue-600 selection:text-white">

    <!-- Admin Top Application Control Bar -->
    <header class="border-b border-[#232936] bg-[#11141A] sticky top-0 z-40 shadow-lg">
        <div class="max-w-[1600px] mx-auto px-4 lg:px-6 h-14 flex items-center justify-between gap-3">
            
            <!-- Left: Mobile Hamburger Toggle + Logo -->
            <div class="flex items-center gap-3">
                
                <!-- Hamburger Button (Mobile / Tablets < lg) -->
                <button type="button" 
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        aria-label="Abrir Menú de Navegación"
                        class="lg:hidden p-2 rounded-xl bg-[#171B22] hover:bg-[#232936] border border-[#232936] text-gray-300 hover:text-white transition-all cursor-pointer flex items-center justify-center">
                    <i data-lucide="menu" class="w-5 h-5 text-blue-400"></i>
                </button>

                <!-- Brand Logo & Title -->
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                    @if(\App\Models\Setting::get('site_logo_type') === 'image' && \App\Models\Setting::get('site_logo_url'))
                        <img src="{{ \App\Models\Setting::get('site_logo_url') }}" alt="Admin" class="h-6 w-auto object-contain">
                    @else
                        <div class="w-6 h-6 rounded bg-blue-600 flex items-center justify-center text-white font-black text-xs shrink-0 shadow-md shadow-blue-600/30">
                            <i data-lucide="{{ \App\Models\Setting::get('site_logo_icon', 'layers') }}" class="w-3.5 h-3.5 text-white"></i>
                        </div>
                    @endif
                    <span class="font-bold text-white tracking-tight text-sm font-sans flex items-center gap-1">
                        <span>{{ \App\Models\Setting::get('site_logo_prefix', 'ROM') }}<span class="text-blue-500">{{ \App\Models\Setting::get('site_logo_suffix', 'HUB') }}</span></span>
                        <span class="hidden sm:inline text-xs text-gray-400 font-normal">/ Admin</span>
                    </span>
                </a>

                <div class="hidden sm:block h-4 w-px bg-[#232936]"></div>

                <!-- Web Pública Link -->
                <a href="{{ route('home') }}" target="_blank" class="hidden sm:flex text-xs text-gray-400 hover:text-white font-medium items-center gap-1.5 transition-colors px-2 py-1 rounded-lg hover:bg-[#171B22]">
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-blue-400"></i>
                    <span>Ver Web Pública</span>
                </a>
            </div>

            <!-- Right: Telemetry & Admin User Info -->
            <div class="flex items-center gap-2.5 text-xs font-mono">
                
                <!-- Cloudflare Status (Hidden on small mobile) -->
                <div class="hidden md:flex items-center gap-2 px-2.5 py-1 rounded-xl bg-[#0A0C0F] border border-[#232936] text-gray-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="truncate max-w-[140px]">R2: <strong class="text-gray-200">{{ \App\Models\Setting::get('r2_bucket', 'romhub-vault') }}</strong></span>
                </div>

                <!-- User Profile / Session Button -->
                <a href="{{ Route::has('admin.profile') ? route('admin.profile') : url('/admin/profile') }}" 
                   class="flex items-center gap-2 pl-1.5 pr-2.5 py-1 rounded-xl bg-[#171B22] hover:bg-[#232936] border border-[#232936] transition-colors group" 
                   title="Editar mi cuenta y contraseña">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-[11px] group-hover:scale-105 transition-transform shadow-sm">
                        {{ substr(auth()->user()->name ?? 'AD', 0, 2) }}
                    </div>
                    <span class="text-gray-200 group-hover:text-white font-sans text-xs font-semibold max-w-[100px] sm:max-w-[160px] md:max-w-none truncate">
                        {{ auth()->user()->email ?? 'admin' }}
                    </span>
                    <span class="hidden sm:inline px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-400 text-[10px] font-mono font-bold">ADMIN</span>
                </a>
            </div>

        </div>
    </header>

    <!-- MOBILE OFF-CANVAS SLIDE-OVER DRAWER (ESTILO HAMBURGUESA) -->
    <div x-show="mobileMenuOpen" 
         x-cloak
         class="relative z-50 lg:hidden" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop Overlay -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileMenuOpen = false"
             class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>

        <!-- Slide-over Drawer Panel -->
        <div class="fixed inset-0 flex z-50">
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="relative mr-16 flex w-full max-w-xs flex-1">
                
                <div class="flex h-full w-full flex-col bg-[#11141A] border-r border-[#232936] shadow-2xl p-5 overflow-y-auto custom-scrollbar space-y-6">
                    
                    <!-- Drawer Header & Close Button -->
                    <div class="flex items-center justify-between pb-3 border-b border-[#232936]">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded bg-blue-600 flex items-center justify-center text-white font-bold text-xs">
                                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                            </div>
                            <span class="text-sm font-black text-white font-heading">Menú Principal</span>
                        </div>
                        <button type="button" 
                                @click="mobileMenuOpen = false"
                                class="p-1.5 rounded-lg bg-[#171B22] text-gray-400 hover:text-white border border-[#232936]">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Navigation Links Modular Include -->
                    <div @click="if ($event.target.closest('a')) mobileMenuOpen = false">
                        @include('layouts.admin-nav')
                    </div>

                    <!-- Drawer Footer Status Widget -->
                    <div class="pt-4 border-t border-[#232936] text-[11px] font-mono text-gray-400 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Cloudflare R2:
                            </span>
                            <span class="text-white font-bold">Activo</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Motor IA: <strong class="text-purple-400">Gemini</strong></span>
                            <span class="text-emerald-400 font-bold">v12.0 Ready</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Admin Body: Sticky Desktop Sidebar + Main Content -->
    <div class="flex-1 max-w-[1600px] w-full mx-auto px-4 lg:px-6 py-6 flex flex-col lg:flex-row gap-6">
        
        <!-- Desktop Sidebar Navigation (Hidden on < lg, sticky on desktop) -->
        <aside class="hidden lg:flex w-64 bg-[#11141A] rounded-2xl border border-[#232936] p-4 flex-col justify-between shrink-0 space-y-6 h-[calc(100vh-5.5rem)] sticky top-20 shadow-xl overflow-y-auto custom-scrollbar">
            <div class="space-y-5">
                
                <!-- Sidebar Top Brand Header -->
                <div class="px-2 pb-3 border-b border-[#232936]/80 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-blue-400 font-bold">Panel de Control</div>
                        <div class="text-sm font-extrabold text-white font-heading mt-0.5">Administración</div>
                    </div>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" title="Sistema Operativo"></span>
                </div>

                <!-- Navigation List Included Modularly -->
                @include('layouts.admin-nav')
            </div>

            <!-- Server Telemetry & Status Widget -->
            <div class="p-3 bg-[#0A0C0F] rounded-xl border border-[#232936] space-y-2 text-[11px] font-mono mt-auto">
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
                    <span class="text-emerald-400 font-bold">v12.0</span>
                </div>
            </div>
        </aside>

        <!-- Main Admin Content Area (Takes 100% width on mobile, and fills space smoothly) -->
        <main class="flex-1 min-w-0 space-y-6 w-full">
            
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
        </main>

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
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) window.lucide.createIcons();
        });
        if (window.lucide) window.lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>
