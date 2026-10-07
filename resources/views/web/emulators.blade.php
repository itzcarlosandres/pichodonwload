@extends('layouts.web')

@section('title', 'Directorio de Emuladores Oficiales para PC, Android & Steam Deck — ' . \App\Models\Setting::get('site_name', 'ROMHUB'))
@section('meta_description', 'Descarga los mejores emuladores oficiales y optimizados para PlayStation 2, PS1, GameCube, Wii, PSP, PS3, 3DS, Switch y RetroArch. Enlaces directos desde nuestro Servidor Privado Vault CDN y repositorios oficiales.')
@section('canonical', route('emulators'))

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-8 space-y-8"
      x-data="{
          selectedPlatform: 'all',
          search: '',
          openFaq: null,
          toggleFaq(index) {
              this.openFaq = this.openFaq === index ? null : index;
          }
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
            <span>Software Open Source • {{ count($emulators) }} Emuladores Oficiales & Estables</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-[#E5E0D8] pb-6">
            <div class="space-y-2.5 max-w-2xl">
                <h1 class="text-3xl sm:text-5xl font-black text-[#18181B] tracking-tight font-sans">
                    Emuladores Recomendados
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 font-sans leading-relaxed">
                    Directorio verificado de las mejores herramientas de software para revivir tus consolas retro favoritas en <strong>Windows PC, dispositivos móviles Android, macOS (Apple Silicon), Linux y consolas portátiles como Steam Deck y ROG Ally</strong>. Enlaces de descarga directa de alta velocidad y acceso a los repositorios oficiales de cada desarrollador.
                </p>
            </div>

            <!-- BIOS Link Card -->
            <a href="{{ route('bios') }}" class="flex items-center gap-3 p-3.5 rounded-2xl bg-white border-2 border-[#1E1E1E] hover:border-[#CE2D2D] font-mono shrink-0 shadow-sm transition-colors group">
                <div class="w-10 h-10 rounded-xl bg-[#FDF2F2] border border-[#FCA5A5] text-[#CE2D2D] flex items-center justify-center group-hover:bg-[#CE2D2D] group-hover:text-white transition-colors">
                    <i data-lucide="binary" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-[#18181B] group-hover:text-[#CE2D2D] transition-colors block">¿Necesitas Archivos BIOS?</span>
                    <span class="text-[10px] text-gray-500 block">Descargar packs para PS2, PS1, PS3, Sega →</span>
                </div>
            </a>
        </div>
    </section>

    <!-- Trust & Performance Badges Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white border border-[#DDD6CB] rounded-2xl p-3.5 flex items-center gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="zap" class="w-4 h-4"></i>
            </div>
            <div>
                <span class="text-xs font-black text-[#18181B] block">Servidor Vault CDN</span>
                <span class="text-[10px] text-gray-500 block">Descarga directa sin esperas</span>
            </div>
        </div>

        <div class="bg-white border border-[#DDD6CB] rounded-2xl p-3.5 flex items-center gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <i data-lucide="gamepad-2" class="w-4 h-4"></i>
            </div>
            <div>
                <span class="text-xs font-black text-[#18181B] block">Soporte Mandos</span>
                <span class="text-[10px] text-gray-500 block">DualSense, Xbox & Switch Pro</span>
            </div>
        </div>

        <div class="bg-white border border-[#DDD6CB] rounded-2xl p-3.5 flex items-center gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="award" class="w-4 h-4"></i>
            </div>
            <div>
                <span class="text-xs font-black text-[#18181B] block">RetroAchievements</span>
                <span class="text-[10px] text-gray-500 block">Logros en vivo en tus juegos</span>
            </div>
        </div>

        <div class="bg-white border border-[#DDD6CB] rounded-2xl p-3.5 flex items-center gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
            </div>
            <div>
                <span class="text-xs font-black text-[#18181B] block">Gráficos HD 4K</span>
                <span class="text-[10px] text-gray-500 block">Filtros CRT y Render Vulkan</span>
            </div>
        </div>
    </div>

    <!-- Search Bar & Filters -->
    <div class="space-y-3">
        <!-- Live Search Input -->
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </div>
            <input type="text"
                   x-model="search"
                   placeholder="Buscar emulador por nombre (ej. PCSX2, DuckStation, Dolphin) o sistema (ej. PS2, N64, 3DS)..."
                   class="w-full pl-10 pr-10 py-3 rounded-2xl bg-white border-2 border-[#1E1E1E] focus:border-[#CE2D2D] focus:ring-2 focus:ring-[#CE2D2D]/20 text-sm font-sans placeholder-gray-400 text-[#18181B] outline-none shadow-sm transition-all">
            <button x-show="search.length > 0"
                    @click="search = ''"
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-black">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

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
                <span>Android Móvil & Tablet</span>
            </button>

            <button @click="selectedPlatform = 'macOS'" 
                    :class="selectedPlatform === 'macOS' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                    class="px-4 py-2 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 cursor-pointer active:scale-95 shrink-0">
                <i data-lucide="laptop" class="w-3.5 h-3.5"></i>
                <span>macOS (Apple Silicon & Intel)</span>
            </button>

            <button @click="selectedPlatform = 'Linux'" 
                    :class="selectedPlatform === 'Linux' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                    class="px-4 py-2 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 cursor-pointer active:scale-95 shrink-0">
                <i data-lucide="terminal" class="w-3.5 h-3.5"></i>
                <span>Linux & Steam Deck</span>
            </button>
        </div>
    </div>

    <!-- Emulators Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($emulators as $emu)
            @php
                // Recommended formats per emulator system
                $formats = 'ISO / ROM';
                $sysLower = strtolower($emu->system . ' ' . $emu->name);
                if (str_contains($sysLower, 'ps2') || str_contains($sysLower, 'pcsx2')) {
                    $formats = 'CHD (Recomendado) / ISO / CSO';
                } elseif (str_contains($sysLower, 'ps1') || str_contains($sysLower, 'duckstation')) {
                    $formats = 'CHD (Recomendado) / BIN+CUE / PBP';
                } elseif (str_contains($sysLower, 'gamecube') || str_contains($sysLower, 'wii') || str_contains($sysLower, 'dolphin')) {
                    $formats = 'RVZ (Recomendado) / ISO / WBFS';
                } elseif (str_contains($sysLower, 'psp') || str_contains($sysLower, 'ppsspp')) {
                    $formats = 'CSO (Comprimido) / ISO / PBP';
                } elseif (str_contains($sysLower, 'dreamcast') || str_contains($sysLower, 'flycast')) {
                    $formats = 'CHD (Recomendado) / GDI / CDI';
                } elseif (str_contains($sysLower, 'gba') || str_contains($sysLower, 'snes') || str_contains($sysLower, 'genesis')) {
                    $formats = '.ZIP / .7Z / ROM directas';
                }
            @endphp
            <div x-show="(selectedPlatform === 'all' || {{ json_encode($emu->platforms ?? []) }}.includes(selectedPlatform)) && (!search || '{{ strtolower(addslashes($emu->name . ' ' . $emu->system . ' ' . $emu->description)) }}'.includes(search.toLowerCase().trim()))"
                 class="bg-white border-2 border-[#1E1E1E] rounded-3xl p-5 flex flex-col justify-between space-y-4 hover:shadow-md transition-all group">
                
                <div class="space-y-3.5">
                    <!-- Top row -->
                    <div class="flex items-start justify-between border-b border-[#E5E0D8] pb-3 gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-[#FAF7F2] border border-[#DDD6CB] group-hover:bg-[#FDF2F2] group-hover:border-[#FCA5A5] flex items-center justify-center text-[#18181B] group-hover:text-[#CE2D2D] transition-colors shrink-0">
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
                            <span class="text-[10px] font-mono text-gray-600 font-bold border border-[#DDD6CB] px-2 py-0.5 rounded-lg bg-[#FAF7F2] shrink-0">
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

                    <!-- Recommended Formats & Hardware Specs -->
                    <div class="bg-[#FAF7F2] border border-[#DDD6CB] rounded-2xl p-3 space-y-1.5 font-mono text-[10px] text-gray-600">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 font-bold">Formatos Óptimos:</span>
                            <strong class="text-[#18181B]">{{ $formats }}</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 font-bold">Render Recomendado:</span>
                            <span class="text-[#CE2D2D] font-bold">Vulkan 1.3 / Direct3D 12</span>
                        </div>
                    </div>

                    <!-- Platforms Bar -->
                    @if(!empty($emu->platforms))
                        <div class="flex items-center gap-1.5 text-[11px] font-mono text-gray-500 pt-1 border-t border-[#E5E0D8]">
                            <span class="font-bold text-gray-600">Sistemas:</span>
                            @foreach($emu->platforms as $plat)
                                <span class="px-2 py-0.5 rounded-lg bg-gray-100 text-gray-800 text-[10px] font-bold">{{ $plat }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Bottom CTA Buttons -->
                <div class="pt-2 flex items-center gap-2">
                    <a href="{{ $emu->download_url }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="flex-1 py-3 rounded-2xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white border-2 border-[#1E1E1E] font-black text-xs font-sans uppercase tracking-wider flex items-center justify-center gap-1.5 transition-all shadow-md shadow-red-500/20 active:scale-95 group-hover:border-black">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Descargar Oficial</span>
                    </a>
                    
                    @if($emu->website)
                    <a href="{{ $emu->website }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="p-3 rounded-2xl bg-[#FAF7F2] hover:bg-[#EDE7DE] text-gray-700 border-2 border-[#1E1E1E] hover:border-[#CE2D2D] transition-colors"
                       title="Sitio Web / Repositorio Oficial">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                    </a>
                    @endif
                </div>

            </div>
        @endforeach
    </div>

    <!-- FAQ & Performance Guide Accordion Section -->
    <section class="bg-white border-2 border-[#1E1E1E] rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FAF7F2] border border-[#DDD6CB] text-xs font-mono font-bold text-gray-700">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                <span>Preguntas Frecuentes & Optimización</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-[#18181B] font-sans">
                Guía de Rendimiento para Emuladores
            </h2>
            <p class="text-xs text-gray-600 font-sans">
                Consejos clave para obtener 60 FPS estables, evitar tirones de audio y reducir el tamaño de tus juegos en disco.
            </p>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="border border-[#DDD6CB] rounded-2xl overflow-hidden">
                <button @click="toggleFaq(1)" class="w-full p-4 text-left font-sans font-bold text-sm text-[#18181B] flex items-center justify-between bg-[#FAF7F2] hover:bg-[#EDE7DE] transition-colors">
                    <span>¿Cuál es el mejor renderizador gráfico: Vulkan, DirectX 12 u OpenGL?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-gray-500" :class="openFaq === 1 ? 'rotate-180 text-[#CE2D2D]' : ''"></i>
                </button>
                <div x-show="openFaq === 1" x-collapse class="p-4 text-xs font-sans text-gray-600 space-y-2 border-t border-[#DDD6CB]">
                    <p>En el 90% de los casos, <strong>Vulkan</strong> es la opción más recomendada debido a su menor uso de CPU y mejor gestión de compilación asíncrona de shaders (eliminando el molesto stuttering al entrar a zonas nuevas).</p>
                    <p>En tarjetas gráficas dedicadas de NVIDIA bajo Windows, <strong>DirectX 12</strong> también ofrece un rendimiento excelente. Deja <strong>OpenGL</strong> únicamente para emuladores antiguos o si un juego específico presenta fallos gráficos en Vulkan.</p>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="border border-[#DDD6CB] rounded-2xl overflow-hidden">
                <button @click="toggleFaq(2)" class="w-full p-4 text-left font-sans font-bold text-sm text-[#18181B] flex items-center justify-between bg-[#FAF7F2] hover:bg-[#EDE7DE] transition-colors">
                    <span>¿Por qué algunos juegos necesitan una BIOS y otros funcionan sin ella?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-gray-500" :class="openFaq === 2 ? 'rotate-180 text-[#CE2D2D]' : ''"></i>
                </button>
                <div x-show="openFaq === 2" x-collapse class="p-4 text-xs font-sans text-gray-600 space-y-2 border-t border-[#DDD6CB]">
                    <p>Muchos emuladores modernos (como DuckStation o PCSX2) incluyen una <em>HLE BIOS</em> (High-Level Emulation simulada por software). Sin embargo, ciertos juegos avanzados ejecutan rutinas matemáticas específicas o descifrado de hardware que solo la <strong>BIOS original de la consola</strong> puede resolver sin congelarse.</p>
                    <p>Puedes descargar el archivo de arranque oficial correspondiente desde nuestra <a href="{{ route('bios') }}" class="text-[#CE2D2D] font-bold hover:underline">sección de BIOS</a> para garantizar compatibilidad del 100%.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="border border-[#DDD6CB] rounded-2xl overflow-hidden">
                <button @click="toggleFaq(3)" class="w-full p-4 text-left font-sans font-bold text-sm text-[#18181B] flex items-center justify-between bg-[#FAF7F2] hover:bg-[#EDE7DE] transition-colors">
                    <span>¿Cómo reducir el peso de los juegos con formato CHD o RVZ?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-gray-500" :class="openFaq === 3 ? 'rotate-180 text-[#CE2D2D]' : ''"></i>
                </button>
                <div x-show="openFaq === 3" x-collapse class="p-4 text-xs font-sans text-gray-600 space-y-2 border-t border-[#DDD6CB]">
                    <p>Las imágenes ISO convencionales ocupan todo el espacio asignado al disco óptico (incluso los sectores vacíos con ceros). Al convertirlas a <strong>.CHD</strong> (para PS1, PS2 y Sega CD) o <strong>.RVZ</strong> (para GameCube y Wii en Dolphin), los emuladores pueden leer el juego comprimido directamente sin descomprimirlo en RAM, ahorrando hasta un <strong>60% de espacio en disco</strong> sin pérdida de calidad.</p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="border border-[#DDD6CB] rounded-2xl overflow-hidden">
                <button @click="toggleFaq(4)" class="w-full p-4 text-left font-sans font-bold text-sm text-[#18181B] flex items-center justify-between bg-[#FAF7F2] hover:bg-[#EDE7DE] transition-colors">
                    <span>¿Cómo configurar mandos inalámbricos (DualSense / Xbox / Switch Pro)?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-gray-500" :class="openFaq === 4 ? 'rotate-180 text-[#CE2D2D]' : ''"></i>
                </button>
                <div x-show="openFaq === 4" x-collapse class="p-4 text-xs font-sans text-gray-600 space-y-2 border-t border-[#DDD6CB]">
                    <p>La mayoría de los emuladores listados (DuckStation, PCSX2, Dolphin, RPCS3) soportan la API nativa <strong>SDL2</strong>. Simplemente conecta tu mando por Bluetooth o USB antes de abrir el emulador; la aplicación detectará automáticamente el perfil del mando asignando los botones, joysticks análogos y motores de vibración.</p>
                </div>
            </div>
        </div>
    </section>

</main>
@endsection
