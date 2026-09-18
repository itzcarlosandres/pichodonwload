@extends('layouts.web')

@section('title', 'Directorio de Emuladores Oficiales para PC, Android & Steam Deck — ' . \App\Models\Setting::get('site_name', 'ROMHUB'))
@section('meta_description', 'Descarga los mejores emuladores oficiales para PlayStation 2, PS1, GameCube, Wii, PSP, PS3, 3DS y GBA: PCSX2, DuckStation, Dolphin, PPSSPP y RetroArch.')

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-8 space-y-8"
      x-data="{
          selectedPlatform: 'all',
          search: ''
      }">

    <!-- Breadcrumbs -->
    <nav class="flex items-center gap-2 text-xs font-mono text-gray-500">
        <a href="{{ route('home') }}" class="hover:text-[#CE2D2D] transition-colors font-medium">INICIO</a>
        <span class="text-gray-400">/</span>
        <span class="text-[#CE2D2D] font-bold uppercase">DIRECTORIO DE EMULADORES</span>
    </nav>

    <!-- Header Section -->
    <section class="space-y-4">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#CE2D2D] text-white text-xs font-mono font-bold shadow-sm">
            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
            <span>Software Open Source • Enlaces Oficiales & Estables</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-[#E5E0D8] pb-6">
            <div class="space-y-2 max-w-2xl">
                <h1 class="text-3xl sm:text-5xl font-black text-[#18181B] tracking-tight font-sans">
                    Emuladores Recomendados
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 font-sans leading-relaxed">
                    Descarga directa a los repositorios oficiales de los emuladores más potentes y optimizados para computadoras, teléfonos móviles y consolas portátiles.
                </p>
            </div>

            <!-- BIOS Link Card -->
            <a href="{{ route('bios') }}" class="flex items-center gap-3 p-3.5 rounded-2xl bg-white border-2 border-[#1E1E1E] hover:border-[#CE2D2D] font-mono shrink-0 shadow-sm transition-colors group">
                <div class="w-10 h-10 rounded-xl bg-[#FDF2F2] border border-[#FCA5A5] text-[#CE2D2D] flex items-center justify-center group-hover:bg-[#CE2D2D] group-hover:text-white transition-colors">
                    <i data-lucide="binary" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-[#18181B] group-hover:text-[#CE2D2D] transition-colors block">¿Necesitas BIOS?</span>
                    <span class="text-[10px] text-gray-500 block">Descargar pack de BIOS PS2, PS1, PS3 →</span>
                </div>
            </a>
        </div>
    </section>

    <!-- Filter Chips by Operating System (Touch edge-to-edge scroll on mobile) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 scrollbar-none text-xs font-mono">
        <button @click="selectedPlatform = 'all'" 
                :class="selectedPlatform === 'all' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                class="px-4 py-2 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 cursor-pointer active:scale-95 shrink-0">
            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
            <span>Todos los Sistemas ({{ count($emulators) }})</span>
        </button>

        <button @click="selectedPlatform = 'Windows'" 
                :class="selectedPlatform === 'Windows' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                class="px-4 py-2 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 cursor-pointer active:scale-95 shrink-0">
            <i data-lucide="monitor" class="w-3.5 h-3.5"></i>
            <span>Windows PC</span>
        </button>

        <button @click="selectedPlatform = 'Android'" 
                :class="selectedPlatform === 'Android' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                class="px-4 py-2 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 cursor-pointer active:scale-95 shrink-0">
            <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
            <span>Android</span>
        </button>

        <button @click="selectedPlatform = 'macOS'" 
                :class="selectedPlatform === 'macOS' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                class="px-4 py-2 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 cursor-pointer active:scale-95 shrink-0">
            <i data-lucide="laptop" class="w-3.5 h-3.5"></i>
            <span>macOS Apple Silicon / Intel</span>
        </button>

        <button @click="selectedPlatform = 'Linux'" 
                :class="selectedPlatform === 'Linux' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                class="px-4 py-2 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 cursor-pointer active:scale-95 shrink-0">
            <i data-lucide="terminal" class="w-3.5 h-3.5"></i>
            <span>Linux & Steam Deck</span>
        </button>
    </div>

    <!-- Emulators Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($emulators as $emu)
            <div x-show="selectedPlatform === 'all' || {{ json_encode($emu->platforms ?? []) }}.includes(selectedPlatform)"
                 class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-5 flex flex-col justify-between space-y-4 hover:shadow-md transition-all group">
                
                <div class="space-y-3.5">
                    <!-- Top row -->
                    <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] group-hover:bg-[#FDF2F2] group-hover:border-[#FCA5A5] flex items-center justify-center text-[#18181B] group-hover:text-[#CE2D2D] transition-colors shrink-0">
                                <i data-lucide="{{ $emu->icon ?: 'cpu' }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-black text-[#18181B] group-hover:text-[#CE2D2D] transition-colors font-sans flex items-center gap-2">
                                    <span>{{ $emu->name }}</span>
                                    @if($emu->version)
                                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-[#EDE7DE] text-gray-700 font-bold">{{ $emu->version }}</span>
                                    @endif
                                </h2>
                                <span class="text-[11px] font-mono font-bold text-[#CE2D2D] block">{{ $emu->system }}</span>
                            </div>
                        </div>
                        @if($emu->license)
                            <span class="text-[10px] font-mono text-gray-500 font-bold border border-[#DDD6CB] px-2 py-0.5 rounded-lg bg-[#FAF7F2]">
                                {{ $emu->license }}
                            </span>
                        @endif
                    </div>

                    <!-- Description -->
                    <p class="text-xs text-gray-600 font-sans leading-relaxed">
                        {{ $emu->description }}
                    </p>

                    <!-- Features Tags -->
                    @if(!empty($emu->features))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($emu->features as $feat)
                                <span class="px-2 py-0.5 rounded-md bg-[#FAF7F2] border border-[#DDD6CB] text-[10px] font-mono text-gray-700 font-medium">
                                    ✓ {{ $feat }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <!-- Platforms Bar -->
                    @if(!empty($emu->platforms))
                        <div class="flex items-center gap-1.5 text-[11px] font-mono text-gray-500 pt-1 border-t border-[#E5E0D8]">
                            <span class="font-bold text-gray-600">Plataformas:</span>
                            @foreach($emu->platforms as $plat)
                                <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-[10px] font-bold">{{ $plat }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Bottom CTA Buttons -->
                <div class="pt-2 flex items-center gap-2">
                    <a href="{{ $emu->download_url }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="flex-1 py-2.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white border border-[#1E1E1E] font-black text-xs font-sans uppercase tracking-wider flex items-center justify-center gap-1.5 transition-all shadow-md shadow-red-500/20 active:scale-95">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Descargar Oficial</span>
                    </a>
                    
                    @if($emu->website)
                    <a href="{{ $emu->website }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="p-2.5 rounded-xl bg-[#FAF7F2] hover:bg-[#EDE7DE] text-gray-700 border border-[#DDD6CB] hover:border-[#1E1E1E] transition-colors"
                       title="Sitio Web Oficial">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                    </a>
                    @endif
                </div>

            </div>
        @endforeach
    </div>

</main>
@endsection
