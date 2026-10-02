@extends('layouts.web')

@section('title', 'Bóveda Oficial de BIOS & Firmwares para Emuladores — ' . \App\Models\Setting::get('site_name', 'ROMHUB'))
@section('meta_description', 'Descarga directa de los 38 packs de BIOS oficiales y verificados (No-Intro & Redump) para PlayStation, Nintendo, Sega, Xbox y Arcade. Sin publicidad y a máxima velocidad desde nuestro Servidor Privado Vault CDN.')

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-8 space-y-8"
      x-data="{
          search: '',
          selectedCategory: 'all',
          copiedMd5: null,
          activeGuideEmu: 'pcsx2',
          copiedPath: null,
          copyText(text, id) {
              navigator.clipboard.writeText(text);
              this.copiedPath = id;
              setTimeout(() => { if (this.copiedPath === id) this.copiedPath = null; }, 2000);
          },
          copyHash(text, id) {
              navigator.clipboard.writeText(text);
              this.copiedMd5 = id;
              setTimeout(() => { if (this.copiedMd5 === id) this.copiedMd5 = null; }, 2000);
          },
          matches(bios) {
              const query = this.search.toLowerCase().trim();
              const cat = this.selectedCategory;
              
              // Category match
              let catMatch = true;
              if (cat === 'playstation') {
                  catMatch = bios.system.toLowerCase().includes('playstation') || bios.system.toLowerCase().includes('ps');
              } else if (cat === 'nintendo') {
                  catMatch = bios.system.toLowerCase().includes('nintendo') || bios.system.toLowerCase().includes('game boy') || bios.system.toLowerCase().includes('gamecube') || bios.system.toLowerCase().includes('snes') || bios.system.toLowerCase().includes('famicom') || bios.system.toLowerCase().includes('satellaview');
              } else if (cat === 'sega') {
                  catMatch = bios.system.toLowerCase().includes('sega') || bios.system.toLowerCase().includes('dreamcast') || bios.system.toLowerCase().includes('saturn') || bios.system.toLowerCase().includes('genesis') || bios.system.toLowerCase().includes('master system');
              } else if (cat === 'xbox') {
                  catMatch = bios.system.toLowerCase().includes('xbox');
              } else if (cat === 'arcade_pc') {
                  catMatch = !bios.system.toLowerCase().includes('playstation') && !bios.system.toLowerCase().includes('sega') && !bios.system.toLowerCase().includes('nintendo') && !bios.system.toLowerCase().includes('xbox');
              }
              
              if (!catMatch) return false;
              if (!query) return true;
              
              return bios.system.toLowerCase().includes(query) ||
                     (bios.files && bios.files.toLowerCase().includes(query)) ||
                     (bios.emulator && bios.emulator.toLowerCase().includes(query)) ||
                     (bios.description && bios.description.toLowerCase().includes(query));
          }
      }">

    <!-- Breadcrumbs -->
    <nav class="flex items-center gap-2 text-xs font-mono text-gray-500">
        <a href="{{ route('home') }}" class="hover:text-[#CE2D2D] transition-colors font-medium">INICIO</a>
        <span class="text-gray-400">/</span>
        <span class="text-[#CE2D2D] font-bold uppercase">BÓVEDA DE BIOS & FIRMWARES</span>
    </nav>

    <!-- Header Section -->
    <section class="space-y-4">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#CE2D2D] text-white text-xs font-mono font-bold shadow-sm">
            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
            <span>Vault de Preservación Digital • {{ count($biosList) }} Packs Verificados No-Intro & Redump</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-[#E5E0D8] pb-6">
            <div class="space-y-2.5 max-w-2xl">
                <h1 class="text-3xl sm:text-5xl font-black text-[#18181B] tracking-tight font-sans">
                    BIOS & Firmwares Oficiales
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 font-sans leading-relaxed">
                    Descarga directa de los archivos de arranque de sistema y firmware originales indispensables para emulación de bajo nivel (LLE) en <strong>PCSX2, DuckStation, RPCS3, Dolphin, Flycast, MelonDS y RetroArch</strong>. Dumps limpios 1:1, sin acortadores, sin contraseñas y servidos directamente desde nuestro <strong>Servidor Privado Vault CDN</strong> de alta velocidad.
                </p>
            </div>

            <!-- Quick Notice Badge -->
            <a href="{{ route('emulators') }}" class="flex items-center gap-3 p-3.5 rounded-2xl bg-white border-2 border-[#1E1E1E] hover:border-[#CE2D2D] font-mono shrink-0 shadow-sm transition-colors group">
                <div class="w-10 h-10 rounded-xl bg-[#FDF2F2] border border-[#FCA5A5] text-[#CE2D2D] flex items-center justify-center group-hover:bg-[#CE2D2D] group-hover:text-white transition-colors">
                    <i data-lucide="cpu" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-[#18181B] group-hover:text-[#CE2D2D] transition-colors block">¿Buscas Emuladores?</span>
                    <span class="text-[10px] text-gray-500 block">Ver directorio de software oficial →</span>
                </div>
            </a>
        </div>
    </section>

    <!-- Trust & Quality Badges Grid -->
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
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </div>
            <div>
                <span class="text-xs font-black text-[#18181B] block">Redump & No-Intro</span>
                <span class="text-[10px] text-gray-500 block">Dumps verificados 100% limpios</span>
            </div>
        </div>

        <div class="bg-white border border-[#DDD6CB] rounded-2xl p-3.5 flex items-center gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <i data-lucide="lock-open" class="w-4 h-4"></i>
            </div>
            <div>
                <span class="text-xs font-black text-[#18181B] block">Sin Contraseñas</span>
                <span class="text-[10px] text-gray-500 block">Descomprime y juega al instante</span>
            </div>
        </div>

        <div class="bg-white border border-[#DDD6CB] rounded-2xl p-3.5 flex items-center gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="hash" class="w-4 h-4"></i>
            </div>
            <div>
                <span class="text-xs font-black text-[#18181B] block">Hashes Públicos</span>
                <span class="text-[10px] text-gray-500 block">MD5 verificable con 1 clic</span>
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
                   placeholder="Buscar BIOS por consola, nombre de archivo (ej. SCPH-1001, bios7.bin) o emulador (ej. PCSX2, DuckStation)..."
                   class="w-full pl-10 pr-10 py-3 rounded-2xl bg-white border-2 border-[#1E1E1E] focus:border-[#CE2D2D] focus:ring-2 focus:ring-[#CE2D2D]/20 text-sm font-sans placeholder-gray-400 text-[#18181B] outline-none shadow-sm transition-all">
            <button x-show="search.length > 0"
                    @click="search = ''"
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-black">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Filter Chips by Ecosystem -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 -mx-4 px-4 sm:mx-0 sm:px-0 scrollbar-none text-xs font-mono">
            <button @click="selectedCategory = 'all'" 
                    :class="selectedCategory === 'all' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                    class="px-3.5 py-1.5 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 cursor-pointer shrink-0">
                <i data-lucide="binary" class="w-3.5 h-3.5"></i>
                <span>Todos los Packs ({{ count($biosList) }})</span>
            </button>

            <button @click="selectedCategory = 'playstation'" 
                    :class="selectedCategory === 'playstation' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                    class="px-3.5 py-1.5 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 cursor-pointer shrink-0">
                <i data-lucide="disc" class="w-3.5 h-3.5"></i>
                <span>PlayStation (PS1, PS2, PS3)</span>
            </button>

            <button @click="selectedCategory = 'nintendo'" 
                    :class="selectedCategory === 'nintendo' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                    class="px-3.5 py-1.5 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 cursor-pointer shrink-0">
                <i data-lucide="gamepad-2" class="w-3.5 h-3.5"></i>
                <span>Nintendo (N64, GC, GBA, NDS, SNES)</span>
            </button>

            <button @click="selectedCategory = 'sega'" 
                    :class="selectedCategory === 'sega' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                    class="px-3.5 py-1.5 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 cursor-pointer shrink-0">
                <i data-lucide="radio" class="w-3.5 h-3.5"></i>
                <span>Sega (Dreamcast, Saturn, Mega-CD)</span>
            </button>

            <button @click="selectedCategory = 'xbox'" 
                    :class="selectedCategory === 'xbox' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                    class="px-3.5 py-1.5 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 cursor-pointer shrink-0">
                <i data-lucide="terminal" class="w-3.5 h-3.5"></i>
                <span>Xbox Clásica</span>
            </button>

            <button @click="selectedCategory = 'arcade_pc'" 
                    :class="selectedCategory === 'arcade_pc' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white text-gray-700 hover:text-black border-[#DDD6CB] hover:bg-[#FAF7F2]'"
                    class="px-3.5 py-1.5 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 cursor-pointer shrink-0">
                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                <span>Arcade, Amiga, 3DO & Otros</span>
            </button>
        </div>
    </div>

    <!-- Interactive Installation Paths Widget -->
    <section class="bg-white border-2 border-[#1E1E1E] rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-[#FDF2F2] text-[#CE2D2D] flex items-center justify-center font-bold">
                    <i data-lucide="folder-cog" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-[#18181B] font-sans">Guía Rápida: Dónde Colocar los Archivos BIOS</h2>
                    <p class="text-[11px] text-gray-500 font-sans">Selecciona tu emulador para ver las rutas predeterminadas de instalación</p>
                </div>
            </div>
            <span class="text-[10px] font-mono font-bold bg-[#FAF7F2] border border-[#DDD6CB] px-2.5 py-1 rounded-lg text-gray-700 hidden sm:inline-block">
                Copiar con 1 Clic
            </span>
        </div>

        <!-- Emulator Selector Chips -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none text-xs font-mono">
            <button @click="activeGuideEmu = 'pcsx2'"
                    :class="activeGuideEmu === 'pcsx2' ? 'bg-[#CE2D2D] text-white shadow-sm' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 hover:bg-[#EDE7DE] dark:hover:bg-[#272730]'"
                    class="px-3 py-1.5 rounded-xl border border-[#DDD6CB] dark:border-[#27272A] font-bold transition-all shrink-0">
                PCSX2 (PS2)
            </button>
            <button @click="activeGuideEmu = 'duckstation'"
                    :class="activeGuideEmu === 'duckstation' ? 'bg-[#CE2D2D] text-white shadow-sm' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 hover:bg-[#EDE7DE] dark:hover:bg-[#272730]'"
                    class="px-3 py-1.5 rounded-xl border border-[#DDD6CB] dark:border-[#27272A] font-bold transition-all shrink-0">
                DuckStation (PS1)
            </button>
            <button @click="activeGuideEmu = 'rpcs3'"
                    :class="activeGuideEmu === 'rpcs3' ? 'bg-[#CE2D2D] text-white shadow-sm' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 hover:bg-[#EDE7DE] dark:hover:bg-[#272730]'"
                    class="px-3 py-1.5 rounded-xl border border-[#DDD6CB] dark:border-[#27272A] font-bold transition-all shrink-0">
                RPCS3 (PS3)
            </button>
            <button @click="activeGuideEmu = 'flycast'"
                    :class="activeGuideEmu === 'flycast' ? 'bg-[#CE2D2D] text-white shadow-sm' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 hover:bg-[#EDE7DE] dark:hover:bg-[#272730]'"
                    class="px-3 py-1.5 rounded-xl border border-[#DDD6CB] dark:border-[#27272A] font-bold transition-all shrink-0">
                Flycast (Dreamcast)
            </button>
            <button @click="activeGuideEmu = 'retroarch'"
                    :class="activeGuideEmu === 'retroarch' ? 'bg-[#CE2D2D] text-white shadow-sm' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 hover:bg-[#EDE7DE] dark:hover:bg-[#272730]'"
                    class="px-3 py-1.5 rounded-xl border border-[#DDD6CB] dark:border-[#27272A] font-bold transition-all shrink-0">
                RetroArch (Todos)
            </button>
        </div>

        <!-- Dynamic Guide Content by Emulator -->
        <div class="bg-[#FAF7F2] border border-[#DDD6CB] rounded-2xl p-4 text-xs font-sans space-y-3">
            <!-- PCSX2 -->
            <div x-show="activeGuideEmu === 'pcsx2'" class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-[#18181B]">Ruta en Windows (PCSX2):</span>
                    <button @click="copyText('%USERPROFILE%\\Documents\\PCSX2\\bios\\', 'pcsx2-win')" class="font-mono text-[10px] text-[#CE2D2D] hover:underline font-bold flex items-center gap-1">
                        <span x-text="copiedPath === 'pcsx2-win' ? '¡Copiado!' : 'Copiar Ruta'"></span>
                        <i data-lucide="copy" class="w-3 h-3"></i>
                    </button>
                </div>
                <code class="block bg-white p-2 rounded-xl border border-[#DDD6CB] font-mono text-[11px] text-gray-800 select-all">
                    %USERPROFILE%\Documents\PCSX2\bios\ (o la carpeta portable \bios\ junto al .exe)
                </code>
                <p class="text-gray-600 text-[11px]">En <strong>Android (NetherSX2)</strong>: Crea una carpeta llamada <code class="font-mono bg-white px-1 py-0.5 rounded border border-[#DDD6CB]">/BIOS/</code> en el almacenamiento interno y vincúlala en <em>Ajustes de Sistema > Directorio de BIOS</em>.</p>
            </div>

            <!-- DuckStation -->
            <div x-show="activeGuideEmu === 'duckstation'" class="space-y-2" style="display: none;">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-[#18181B]">Ruta en Windows (DuckStation):</span>
                    <button @click="copyText('%USERPROFILE%\\Documents\\DuckStation\\bios\\', 'duck-win')" class="font-mono text-[10px] text-[#CE2D2D] hover:underline font-bold flex items-center gap-1">
                        <span x-text="copiedPath === 'duck-win' ? '¡Copiado!' : 'Copiar Ruta'"></span>
                        <i data-lucide="copy" class="w-3 h-3"></i>
                    </button>
                </div>
                <code class="block bg-white p-2 rounded-xl border border-[#DDD6CB] font-mono text-[11px] text-gray-800 select-all">
                    %USERPROFILE%\Documents\DuckStation\bios\ (Recomendado: SCPH-1001.bin o SCPH-5502.bin)
                </code>
                <p class="text-gray-600 text-[11px]">Abre DuckStation, ve a <em>Configuración > Ajustes de BIOS</em> y haz clic en <strong>Buscar en directorio</strong> para autodetectar la BIOS de todas las regiones.</p>
            </div>

            <!-- RPCS3 -->
            <div x-show="activeGuideEmu === 'rpcs3'" class="space-y-2" style="display: none;">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-[#18181B]">Instalación de Firmware en RPCS3:</span>
                </div>
                <code class="block bg-white p-2 rounded-xl border border-[#DDD6CB] font-mono text-[11px] text-gray-800">
                    Arrastra el archivo PS3UPDAT.PUP descargado directamente a la ventana principal de RPCS3
                </code>
                <p class="text-gray-600 text-[11px]">También puedes ir al menú superior: <em>File > Install Firmware</em> y seleccionar el archivo <code class="font-mono bg-white px-1 py-0.5 rounded border border-[#DDD6CB]">PS3UPDAT.PUP</code>. RPCS3 compilará los módulos PPU/SPU automáticamente.</p>
            </div>

            <!-- Flycast -->
            <div x-show="activeGuideEmu === 'flycast'" class="space-y-2" style="display: none;">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-[#18181B]">Ruta de Dreamcast (Flycast / Redream):</span>
                    <button @click="copyText('%APPDATA%\\Flycast\\data\\', 'flycast-win')" class="font-mono text-[10px] text-[#CE2D2D] hover:underline font-bold flex items-center gap-1">
                        <span x-text="copiedPath === 'flycast-win' ? '¡Copiado!' : 'Copiar Ruta'"></span>
                        <i data-lucide="copy" class="w-3 h-3"></i>
                    </button>
                </div>
                <code class="block bg-white p-2 rounded-xl border border-[#DDD6CB] font-mono text-[11px] text-gray-800 select-all">
                    %APPDATA%\Flycast\data\ (Colocar: dc_boot.bin y dc_flash.bin)
                </code>
                <p class="text-gray-600 text-[11px]">Esto habilita el arranque original con la espiral naranja/azul de Sega y permite formatear las tarjetas de memoria virtual (VMU).</p>
            </div>

            <!-- RetroArch -->
            <div x-show="activeGuideEmu === 'retroarch'" class="space-y-2" style="display: none;">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-[#18181B]">Ruta Universal en RetroArch:</span>
                    <button @click="copyText('RetroArch\\system\\', 'ra-win')" class="font-mono text-[10px] text-[#CE2D2D] hover:underline font-bold flex items-center gap-1">
                        <span x-text="copiedPath === 'ra-win' ? '¡Copiado!' : 'Copiar Ruta'"></span>
                        <i data-lucide="copy" class="w-3 h-3"></i>
                    </button>
                </div>
                <code class="block bg-white p-2 rounded-xl border border-[#DDD6CB] font-mono text-[11px] text-gray-800 select-all">
                    Carpeta principal de RetroArch > system\
                </code>
                <p class="text-gray-600 text-[11px]">En Android o Steam Deck, copia los archivos sueltos (.bin, .rom) directamente dentro del directorio <code class="font-mono bg-white px-1 py-0.5 rounded border border-[#DDD6CB]">system/</code>. Todos los núcleos (Beetle PSX, Genesis Plus GX, Snes9x) los detectarán automáticamente.</p>
            </div>
        </div>
    </section>

    <!-- BIOS Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($biosList as $bios)
            @php
                $biosJson = json_encode([
                    'id' => $bios->id,
                    'system' => $bios->system,
                    'files' => $bios->files,
                    'emulator' => $bios->emulator,
                    'description' => $bios->description,
                ]);

                // Detect Region
                $sysLower = strtolower($bios->system . ' ' . $bios->version);
                $regionBadge = 'Todas las Regiones';
                $regionColor = 'bg-gray-100 text-gray-800 border-gray-200';
                if (str_contains($sysLower, 'all regions') || str_contains($sysLower, 'complete') || str_contains($sysLower, 'pack')) {
                    $regionBadge = 'Colección Multi-Región (USA / EUR / JPN)';
                    $regionColor = 'bg-blue-50 text-blue-700 border-blue-200';
                } elseif (str_contains($sysLower, 'japan') || str_contains($sysLower, 'jp')) {
                    $regionBadge = 'NTSC-J (Japón)';
                    $regionColor = 'bg-rose-50 text-rose-700 border-rose-200';
                } elseif (str_contains($sysLower, 'europe') || str_contains($sysLower, 'pal')) {
                    $regionBadge = 'PAL (Europa 50Hz)';
                    $regionColor = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                } elseif (str_contains($sysLower, 'usa') || str_contains($sysLower, 'us')) {
                    $regionBadge = 'NTSC-U (América 60Hz)';
                    $regionColor = 'bg-amber-50 text-amber-700 border-amber-200';
                }
            @endphp
            <div x-show="matches({{ $biosJson }})"
                 class="bg-white border-2 border-[#1E1E1E] rounded-3xl p-5 flex flex-col justify-between space-y-4 hover:shadow-md transition-all group">
                
                <div class="space-y-3.5">
                    <!-- Top header -->
                    <div class="flex items-start justify-between border-b border-[#E5E0D8] pb-3 gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-[#FAF7F2] border border-[#DDD6CB] group-hover:bg-[#FDF2F2] group-hover:border-[#FCA5A5] flex items-center justify-center text-[#18181B] group-hover:text-[#CE2D2D] transition-colors shrink-0">
                                <i data-lucide="binary" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-black text-[#18181B] group-hover:text-[#CE2D2D] transition-colors font-sans">
                                    {{ $bios->system }}
                                </h2>
                                <span class="text-[10px] font-mono text-gray-500 block">{{ $bios->version }}</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl bg-[#FAF7F2] text-[#CE2D2D] border border-[#DDD6CB] text-[10px] font-mono font-black shrink-0">
                            {{ $bios->size }}
                        </span>
                    </div>

                    <!-- Region and Quality Pills -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="px-2.5 py-0.5 rounded-lg border text-[10px] font-mono font-bold {{ $regionColor }}">
                            {{ $regionBadge }}
                        </span>
                        <span class="px-2 py-0.5 rounded-lg border border-[#DDD6CB] bg-[#FAF7F2] text-[10px] font-mono text-gray-600 flex items-center gap-1">
                            <i data-lucide="zap" class="w-3 h-3 text-amber-500"></i>
                            <span>Servidor Vault CDN</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-lg border border-emerald-200 bg-emerald-50 text-[10px] font-mono text-emerald-700 font-bold">
                            ✓ Redump OK
                        </span>
                    </div>

                    <!-- Description -->
                    <p class="text-xs text-gray-600 font-sans leading-relaxed">
                        {{ $bios->description }}
                    </p>

                    <!-- Technical Matrix -->
                    <div class="bg-[#FAF7F2] border border-[#DDD6CB] rounded-2xl p-3.5 space-y-2 font-mono text-[11px]">
                        @if($bios->files)
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-500 shrink-0 font-bold">Archivos:</span>
                            <strong class="text-[#18181B] truncate text-right text-[10px]" title="{{ $bios->files }}">{{ $bios->files }}</strong>
                        </div>
                        @endif
                        @if($bios->emulator)
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-500 shrink-0 font-bold">Emuladores:</span>
                            <span class="text-[#CE2D2D] font-bold truncate text-right text-[10px]" title="{{ $bios->emulator }}">{{ $bios->emulator }}</span>
                        </div>
                        @endif
                        @if($bios->md5)
                        <div class="flex items-center justify-between pt-1.5 border-t border-[#E5E0D8] text-[10px]">
                            <span class="text-gray-400 font-bold">MD5 Checksum:</span>
                            <div class="flex items-center gap-1.5">
                                <code class="text-gray-700 font-mono select-all bg-white px-1.5 py-0.5 rounded border border-[#DDD6CB]">{{ $bios->md5 }}</code>
                                <button type="button"
                                        @click="copyHash('{{ $bios->md5 }}', {{ $bios->id }})"
                                        class="text-gray-400 hover:text-[#CE2D2D] transition-colors p-1"
                                        title="Copiar hash MD5">
                                    <span x-show="copiedMd5 !== {{ $bios->id }}">
                                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    </span>
                                    <span x-show="copiedMd5 === {{ $bios->id }}" class="text-green-600 text-[9px] font-bold">
                                        ¡Copiado!
                                    </span>
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Bottom CTA Download Button (URL Protegida & Streaming Seguro) -->
                <div class="pt-2">
                    <a href="{{ route('bios.download', ['id' => $bios->id, 'slug' => \Illuminate\Support\Str::slug($bios->system)]) }}" 
                       class="w-full py-3 rounded-2xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white border-2 border-[#1E1E1E] font-black text-xs font-sans uppercase tracking-wider flex items-center justify-center gap-2 transition-all shadow-md shadow-red-500/20 active:scale-95 group-hover:border-black cursor-pointer">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Descargar desde Servidor Vault CDN ({{ $bios->size }})</span>
                    </a>
                </div>


            </div>
        @endforeach
    </div>

</main>
@endsection
