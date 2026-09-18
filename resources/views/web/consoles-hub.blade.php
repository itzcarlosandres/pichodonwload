@extends('layouts.web')

@section('title', 'Directorio de 20 Consolas y Ecosistemas de Videojuegos — ' . \App\Models\Setting::get('site_name', 'ROMHUB'))
@section('meta_description', 'Explora las 20 consolas de emulación preservadas: PlayStation, Nintendo Switch, GameCube, Xbox 360, Dreamcast y más.')

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-8 space-y-8" 
      x-data="{ 
          selectedManufacturer: 'all',
          search: '',
          resetFilters() {
              this.selectedManufacturer = 'all';
              this.search = '';
          }
      }">

    <!-- Breadcrumb Navigation -->
    <nav class="flex items-center gap-2 text-xs font-mono text-gray-500">
        <a href="{{ route('home') }}" class="hover:text-[#CE2D2D] transition-colors font-medium">INICIO</a>
        <span class="text-gray-400">/</span>
        <span class="text-[#CE2D2D] font-bold uppercase">SISTEMAS Y CONSOLAS RETRO</span>
    </nav>

    <!-- HERO HEADER -->
    <section class="space-y-4">
        <div class="flex justify-start">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#CE2D2D] text-white text-xs font-mono font-bold shadow-sm">
                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                <span>Directorio de Hardware • 20 Arquitecturas Preservadas</span>
            </div>
        </div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-[#E5E0D8] pb-6">
            <div class="space-y-2 max-w-2xl">
                <h1 class="text-3xl sm:text-4xl font-black text-[#18181B] tracking-tight font-sans">
                    Ecosistemas y Consolas
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 font-sans leading-relaxed">
                    Explora y accede a catálogos completos con imágenes de disco y cartuchos verificados 1:1, organizados por fabricante y emuladores compatibles.
                </p>
            </div>

            <!-- Quick Counter Card -->
            <div class="flex items-center gap-4 p-3.5 rounded-2xl bg-white border-2 border-[#1E1E1E] font-mono shrink-0 shadow-sm">
                <div class="text-center px-2">
                    <span class="text-xl sm:text-2xl font-black text-[#18181B] block">{{ $consoles->count() }}</span>
                    <span class="text-[10px] text-gray-500 uppercase tracking-wider font-bold">Consolas</span>
                </div>
                <div class="w-px h-8 bg-[#E5E0D8]"></div>
                <div class="text-center px-2">
                    <span class="text-xl sm:text-2xl font-black text-[#CE2D2D] block">{{ number_format($consoles->sum('games_count')) }}</span>
                    <span class="text-[10px] text-gray-500 uppercase tracking-wider font-bold">ROMs Activas</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ACTION SEARCH BAR & STATUS CAPSULE -->
    <section class="p-3 sm:p-3.5 rounded-2xl bg-white border-2 border-[#1E1E1E] shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <!-- Live Filter Input -->
        <div class="relative w-full sm:w-96 flex items-center">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3.5 pointer-events-none"></i>
            <input type="text" 
                   x-model="search"
                   placeholder="Buscar consola (ej. PS2, GameCube, Switch, 360)..." 
                   class="w-full pl-10 pr-8 py-2 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] text-xs font-mono text-[#18181B] placeholder-gray-400 focus:outline-none focus:border-[#CE2D2D] transition-colors">
            
            <button x-show="search.length > 0" 
                    @click="search = ''" 
                    class="absolute right-3 text-gray-400 hover:text-black text-xs font-mono cursor-pointer" 
                    style="display: none;">
                ✕
            </button>
        </div>

        <!-- Status Pill -->
        <div class="flex items-center gap-2 text-xs font-mono text-gray-600">
            <span class="text-gray-400">Filtrando:</span>
            <span class="text-[#CE2D2D] font-bold uppercase" x-text="selectedManufacturer === 'all' ? 'Todas las Marcas' : selectedManufacturer"></span>
            <span class="text-gray-300">•</span>
            <span class="text-gray-600 font-medium">Emulación Verificada</span>
        </div>
    </section>

    <!-- HORIZONTAL MANUFACTURER CHIPS -->
    <section class="space-y-2">
        <div class="flex items-center gap-2 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 scrollbar-none text-xs font-mono">
            
            <!-- All Filter Chip -->
            <button @click="selectedManufacturer = 'all'" 
                    :class="selectedManufacturer === 'all' ? 'bg-[#CE2D2D] text-white font-bold border-[#CE2D2D] shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                    class="px-4 py-2 rounded-xl border whitespace-nowrap transition-all duration-200 flex items-center gap-1.5 cursor-pointer active:scale-95 shrink-0">
                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                <span>Todas las Marcas ({{ $consoles->count() }})</span>
            </button>

            <!-- Sony PlayStation Chip -->
            @php $sonyCount = $consoles->where('manufacturer', 'Sony')->count(); @endphp
            @if($sonyCount > 0)
            <button @click="selectedManufacturer = 'Sony'" 
                    :class="selectedManufacturer === 'Sony' ? 'bg-[#CE2D2D] text-white font-bold border-[#CE2D2D] shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                    class="px-4 py-2 rounded-xl border whitespace-nowrap transition-all duration-200 flex items-center gap-1.5 cursor-pointer active:scale-95 shrink-0">
                <i data-lucide="disc" class="w-3.5 h-3.5"></i>
                <span>Sony PlayStation ({{ $sonyCount }})</span>
            </button>
            @endif

            <!-- Nintendo Chip -->
            @php $nintendoCount = $consoles->where('manufacturer', 'Nintendo')->count(); @endphp
            @if($nintendoCount > 0)
            <button @click="selectedManufacturer = 'Nintendo'" 
                    :class="selectedManufacturer === 'Nintendo' ? 'bg-[#CE2D2D] text-white font-bold border-[#CE2D2D] shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                    class="px-4 py-2 rounded-xl border whitespace-nowrap transition-all duration-200 flex items-center gap-1.5 cursor-pointer active:scale-95 shrink-0">
                <i data-lucide="gamepad-2" class="w-3.5 h-3.5"></i>
                <span>Nintendo ({{ $nintendoCount }})</span>
            </button>
            @endif

            <!-- Microsoft Xbox Chip -->
            @php $msCount = $consoles->where('manufacturer', 'Microsoft')->count(); @endphp
            @if($msCount > 0)
            <button @click="selectedManufacturer = 'Microsoft'" 
                    :class="selectedManufacturer === 'Microsoft' ? 'bg-[#CE2D2D] text-white font-bold border-[#CE2D2D] shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                    class="px-4 py-2 rounded-xl border whitespace-nowrap transition-all duration-200 flex items-center gap-1.5 cursor-pointer active:scale-95 shrink-0">
                <i data-lucide="box" class="w-3.5 h-3.5"></i>
                <span>Microsoft Xbox ({{ $msCount }})</span>
            </button>
            @endif

            <!-- Sega Chip -->
            @php $segaCount = $consoles->where('manufacturer', 'Sega')->count(); @endphp
            @if($segaCount > 0)
            <button @click="selectedManufacturer = 'Sega'" 
                    :class="selectedManufacturer === 'Sega' ? 'bg-[#CE2D2D] text-white font-bold border-[#CE2D2D] shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                    class="px-4 py-2 rounded-xl border whitespace-nowrap transition-all duration-200 flex items-center gap-1.5 cursor-pointer active:scale-95 shrink-0">
                <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                <span>Sega ({{ $segaCount }})</span>
            </button>
            @endif

        </div>
    </section>

    <!-- 20 CONSOLES RESPONSIVE GRID -->
    <section class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5 sm:gap-4">
            @foreach($consoles as $console)
            <div x-show="(selectedManufacturer === 'all' || selectedManufacturer.toLowerCase() === '{{ strtolower($console->manufacturer) }}') && ('{{ strtolower($console->name) }} {{ strtolower($console->short_name) }} {{ strtolower($console->manufacturer) }}'.includes(search.toLowerCase().trim()))" 
                 class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-5 flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1 shadow-sm hover:shadow-md">
                
                <div class="space-y-4">
                    <!-- Top Brand Tag & Generation Tag -->
                    <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
                        <span class="px-2.5 py-0.5 rounded-lg bg-[#FAF7F2] text-[#18181B] border border-[#DDD6CB] text-[10px] font-mono font-black uppercase tracking-wider">
                            {{ $console->manufacturer }}
                        </span>
                        <span class="text-[10px] font-mono text-gray-500 font-bold">
                            {{ $console->generation ? 'Gen ' . $console->generation : 'Retro' }}
                        </span>
                    </div>

                    <!-- Console Icon, Name & Hardware Details -->
                    <div class="flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-[#FAF7F2] border border-[#E5E0D8] flex items-center justify-center text-[#18181B] group-hover:bg-[#FDF2F2] group-hover:text-[#CE2D2D] group-hover:border-[#FCA5A5] transition-colors shrink-0">
                            @if(stripos($console->manufacturer, 'Sony') !== false)
                                <i data-lucide="disc" class="w-6 h-6"></i>
                            @elseif(stripos($console->manufacturer, 'Nintendo') !== false)
                                <i data-lucide="gamepad-2" class="w-6 h-6"></i>
                            @elseif(stripos($console->manufacturer, 'Microsoft') !== false)
                                <i data-lucide="box" class="w-6 h-6"></i>
                            @elseif(stripos($console->manufacturer, 'Sega') !== false)
                                <i data-lucide="zap" class="w-6 h-6"></i>
                            @else
                                <i data-lucide="cpu" class="w-6 h-6"></i>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm sm:text-base font-black text-[#18181B] group-hover:text-[#CE2D2D] transition-colors truncate font-sans">
                                <a href="{{ route('consoles.show', $console->slug) }}">{{ $console->name }}</a>
                            </h2>
                            <p class="text-[11px] font-mono text-gray-600 flex items-center gap-1 mt-0.5 truncate">
                                <i data-lucide="cpu" class="w-3 h-3 text-[#CE2D2D] shrink-0"></i>
                                <span class="truncate">{{ $console->recommended_emulator ?: '60 FPS Vulkan' }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Description -->
                    <p class="text-xs text-gray-600 font-sans line-clamp-2 leading-relaxed">
                        {{ $console->description ?: 'Plataforma legendaria preservada con soporte para emuladores modernos de alta fidelidad.' }}
                    </p>
                </div>

                <!-- Card Bottom Bar: Count & CTA -->
                <div class="pt-4 mt-4 border-t border-[#E5E0D8] flex items-center justify-between font-mono text-xs">
                    <span class="text-[#CE2D2D] font-bold flex items-center gap-1.5 text-xs">
                        <i data-lucide="database" class="w-3.5 h-3.5"></i>
                        {{ $console->games_count }} Títulos
                    </span>

                    <a href="{{ route('consoles.show', $console->slug) }}" 
                       class="px-3.5 py-1.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white border border-[#1E1E1E] transition-all flex items-center gap-1 font-bold text-xs shadow-sm">
                        <span>Explorar</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>

            </div>
            @endforeach
        </div>

        <!-- No Results Empty State for Live Search -->
        <div x-cloak 
             x-show="search.length > 0 && Array.from($el.parentElement.querySelectorAll('.grid > div')).every(el => el.style.display === 'none')" 
             class="p-12 text-center space-y-3 bg-white rounded-2xl border-2 border-dashed border-[#1E1E1E]">
            <i data-lucide="search-x" class="w-8 h-8 text-gray-400 mx-auto"></i>
            <h3 class="text-sm font-mono font-bold text-[#18181B] uppercase">No se encontraron consolas</h3>
            <p class="text-xs text-gray-600 font-sans">No hay ningún sistema que coincida con "<span x-text="search" class="text-[#CE2D2D] font-bold"></span>".</p>
            <button @click="resetFilters()" class="px-4 py-2 rounded-xl bg-[#CE2D2D] text-white text-xs font-mono font-bold hover:bg-[#B71C1C] transition-colors cursor-pointer shadow-sm">
                Restablecer Búsqueda
            </button>
        </div>
    </section>

</main>
@endsection
