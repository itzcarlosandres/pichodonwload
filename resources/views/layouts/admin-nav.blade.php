<nav class="space-y-4 text-xs font-medium font-sans">
    
    <!-- GROUP 1: PRINCIPAL -->
    <div class="space-y-1">
        <div class="px-2.5 py-1 text-[10px] font-mono uppercase tracking-wider text-gray-500 font-bold">
            Principal
        </div>
        
        <a href="{{ route('admin.dashboard') }}" 
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
            <span class="flex items-center gap-2.5">
                <i data-lucide="layout-dashboard" class="w-4 h-4 text-blue-400"></i> Dashboard
            </span>
            <span class="text-[10px] font-mono bg-blue-500/20 px-1.5 py-0.5 rounded text-blue-300 font-bold">Live</span>
        </a>
    </div>

    <!-- GROUP 2: CATÁLOGO & AUTOMATIZACIÓN -->
    <div class="space-y-1">
        <div class="px-2.5 py-1 text-[10px] font-mono uppercase tracking-wider text-gray-500 font-bold">
            Catálogo & Bóveda
        </div>

        <!-- Catálogo 1-Clic -->
        <a href="{{ route('admin.scraper.catalog') }}" 
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.scraper.catalog*') ? 'bg-purple-600/20 text-purple-300 border border-purple-500/40 font-semibold shadow-sm shadow-purple-500/20' : 'text-purple-400/90 hover:bg-purple-950/30 hover:text-purple-200 border border-purple-500/20' }}">
            <span class="flex items-center gap-2.5">
                <i data-lucide="layers" class="w-4 h-4 text-purple-400"></i> Catálogo 1-Clic
            </span>
            <span class="text-[9px] font-mono text-emerald-400 bg-emerald-950/60 px-1.5 py-0.5 rounded border border-emerald-500/30 font-bold uppercase tracking-wider">Auto</span>
        </a>

        <!-- Extractor URL -->
        <a href="{{ route('admin.scraper.demo') }}" 
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.scraper.demo*') ? 'bg-purple-600/20 text-purple-300 border border-purple-500/40 font-semibold shadow-sm shadow-purple-500/20' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
            <span class="flex items-center gap-2.5">
                <i data-lucide="sparkles" class="w-4 h-4 text-purple-400"></i> Extractor por URL
            </span>
            <span class="text-[9px] font-mono text-gray-500 bg-[#0A0C0F] px-1.5 py-0.5 rounded border border-[#232936]">Manual</span>
        </a>

        <!-- Juegos -->
        <a href="{{ route('admin.games.index') }}" 
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.games.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
            <span class="flex items-center gap-2.5">
                <i data-lucide="gamepad-2" class="w-4 h-4"></i> Videojuegos
            </span>
            <span class="text-[10px] font-mono text-gray-400 bg-[#0A0C0F] px-2 py-0.5 rounded border border-[#232936]">{{ \App\Models\Game::count() }}</span>
        </a>

        <!-- Consolas -->
        <a href="{{ route('admin.consoles.index') }}" 
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.consoles.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
            <span class="flex items-center gap-2.5">
                <i data-lucide="tv" class="w-4 h-4"></i> Consolas (20)
            </span>
            <span class="text-[10px] font-mono text-gray-400 bg-[#0A0C0F] px-2 py-0.5 rounded border border-[#232936]">20</span>
        </a>

        <!-- Sagas & Franquicias -->
        <a href="{{ route('admin.franchises.index') }}" 
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.franchises.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
            <span class="flex items-center gap-2.5">
                <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i> Sagas & Franquicias
            </span>
            <span class="text-[10px] font-mono text-amber-400 bg-amber-950/40 px-2 py-0.5 rounded border border-amber-500/20 font-bold">{{ \App\Models\Franchise::count() }}</span>
        </a>

        <!-- BIOS & Firmwares -->
        <a href="{{ route('admin.bios.index') }}" 
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.bios.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
            <span class="flex items-center gap-2.5">
                <i data-lucide="binary" class="w-4 h-4 text-blue-400"></i> Bóveda de BIOS
            </span>
            <span class="text-[10px] font-mono text-blue-400 bg-blue-950/40 px-2 py-0.5 rounded border border-blue-500/20 font-bold">{{ \App\Models\Bios::count() }}</span>
        </a>

        <!-- Emuladores -->
        <a href="{{ route('admin.emulators.index') }}" 
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.emulators.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
            <span class="flex items-center gap-2.5">
                <i data-lucide="cpu" class="w-4 h-4 text-emerald-400"></i> Emuladores
            </span>
            <span class="text-[10px] font-mono text-emerald-400 bg-emerald-950/40 px-2 py-0.5 rounded border border-emerald-500/20 font-bold">{{ \App\Models\Emulator::count() }}</span>
        </a>

        <!-- Categorías -->
        <a href="{{ route('admin.categories.index') }}" 
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.categories.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
            <span class="flex items-center gap-2.5">
                <i data-lucide="tag" class="w-4 h-4"></i> Categorías & Géneros
            </span>
            <span class="text-[10px] font-mono text-gray-400 bg-[#0A0C0F] px-2 py-0.5 rounded border border-[#232936]">{{ \App\Models\Category::count() }}</span>
        </a>

        <!-- Insignias -->
        <a href="{{ route('admin.badges.index') }}" 
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.badges.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
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
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.banners.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
            <span class="flex items-center gap-2.5">
                <i data-lucide="image" class="w-4 h-4"></i> Banners de Portada
            </span>
            <span class="text-[10px] font-mono text-gray-400 bg-[#0A0C0F] px-2 py-0.5 rounded border border-[#232936]">{{ \App\Models\Banner::where('is_active', true)->count() }}</span>
        </a>

        <!-- Reseñas -->
        <a href="{{ route('admin.reviews.index') }}" 
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.reviews.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
            <span class="flex items-center gap-2.5">
                <i data-lucide="message-square" class="w-4 h-4"></i> Moderación Reseñas
            </span>
            @php $pendingCount = \App\Models\Review::where('is_approved', false)->count(); @endphp
            @if($pendingCount > 0)
                <span class="text-[10px] font-mono bg-amber-500/20 text-amber-300 border border-amber-500/30 px-1.5 py-0.5 rounded font-bold">{{ $pendingCount }}</span>
            @else
                <span class="text-[10px] font-mono text-gray-500 bg-[#0A0C0F] px-2 py-0.5 rounded border border-[#232936]">{{ \App\Models\Review::count() }}</span>
            @endif
        </a>

        <!-- Usuarios -->
        <a href="{{ route('admin.users.index') }}" 
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.users.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
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
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
            <span class="flex items-center gap-2.5">
                <i data-lucide="settings" class="w-4 h-4"></i> Configuración Global
            </span>
            <span class="text-[10px] font-mono text-purple-400 bg-purple-950/40 border border-purple-500/30 px-1.5 py-0.5 rounded font-bold">Gemini IA</span>
        </a>

        <!-- Mi Cuenta & Seguridad -->
        <a href="{{ Route::has('admin.profile') ? route('admin.profile') : url('/admin/profile') }}" 
           class="w-full px-3 py-2 rounded-xl flex items-center justify-between transition-all {{ request()->is('admin/profile*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-semibold shadow-sm shadow-blue-500/10' : 'text-gray-400 hover:bg-[#171B22] hover:text-white border border-transparent' }}">
            <span class="flex items-center gap-2.5">
                <i data-lucide="shield-check" class="w-4 h-4"></i> Mi Cuenta & Seguridad
            </span>
            <span class="text-[10px] font-mono text-blue-400 bg-blue-950/40 border border-blue-500/30 px-1.5 py-0.5 rounded font-bold">Admin</span>
        </a>
    </div>

</nav>
