@extends('layouts.web')

@section('title', 'Descargar BIOS Oficiales y Verificadas para Emuladores — ' . \App\Models\Setting::get('site_name', 'ROMHUB'))
@section('meta_description', 'Descarga directa de los 38 packs de BIOS oficiales (No-Intro y Redump) para PlayStation, Nintendo, Sega, Xbox y Arcade con hashes MD5 verificados.')

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-8 space-y-8"
      x-data="{
          search: '',
          selectedCategory: 'all',
          copiedMd5: null,
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
        <span class="text-[#CE2D2D] font-bold uppercase">ARCHIVOS BIOS & FIRMWARE</span>
    </nav>

    <!-- Header Section -->
    <section class="space-y-4">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#CE2D2D] text-white text-xs font-mono font-bold shadow-sm">
            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
            <span>Vault de Preservación • {{ count($biosList) }} Packs Verificados No-Intro & Redump</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-[#E5E0D8] pb-6">
            <div class="space-y-2 max-w-2xl">
                <h1 class="text-3xl sm:text-5xl font-black text-[#18181B] tracking-tight font-sans">
                    BIOS & Firmware para Emuladores
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 font-sans leading-relaxed">
                    Descarga los archivos de arranque oficiales indispensables para ejecutar juegos en <strong>PCSX2, DuckStation, RPCS3, Dolphin, MelonDS, Flycast y RetroArch</strong>. Dumps limpios directos sin acortadores ni malware.
                </p>
            </div>

            <!-- Quick Notice Badge -->
            <a href="{{ route('emulators') }}" class="flex items-center gap-3 p-3.5 rounded-2xl bg-white border-2 border-[#1E1E1E] hover:border-[#CE2D2D] font-mono shrink-0 shadow-sm transition-colors group">
                <div class="w-10 h-10 rounded-xl bg-[#FDF2F2] border border-[#FCA5A5] text-[#CE2D2D] flex items-center justify-center group-hover:bg-[#CE2D2D] group-hover:text-white transition-colors">
                    <i data-lucide="cpu" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-[#18181B] group-hover:text-[#CE2D2D] transition-colors block">¿Buscas Emuladores?</span>
                    <span class="text-[10px] text-gray-500 block">Ver directorio de emuladores oficiales →</span>
                </div>
            </a>
        </div>
    </section>

    <!-- Search Bar & Filters -->
    <div class="space-y-3">
        <!-- Live Search Input -->
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </div>
            <input type="text"
                   x-model="search"
                   placeholder="Buscar BIOS por consola, nombre de archivo (ej. SCPH-1001, bios7.bin) o emulador..."
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

    <!-- Instructions Banner -->
    <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] text-[#CE2D2D] flex items-center justify-center shrink-0">
                <i data-lucide="help-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-xs font-bold font-mono text-[#18181B] uppercase">¿Cómo instalar los archivos BIOS?</h3>
                <p class="text-xs text-gray-600 font-sans mt-0.5">Descarga el archivo comprimido (.zip / .7z), descomprímelo si es necesario y copia los archivos (.bin / .rom) en la carpeta <code class="text-xs font-mono font-bold bg-[#FAF7F2] px-1 py-0.5 rounded border border-[#DDD6CB]">/bios</code> de tu emulador favorito.</p>
            </div>
        </div>
    </div>

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
            @endphp
            <div x-show="matches({{ $biosJson }})"
                 class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-5 flex flex-col justify-between space-y-4 hover:shadow-md transition-all group">
                
                <div class="space-y-3">
                    <!-- Top header -->
                    <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] group-hover:bg-[#FDF2F2] group-hover:border-[#FCA5A5] flex items-center justify-center text-[#18181B] group-hover:text-[#CE2D2D] transition-colors shrink-0">
                                <i data-lucide="binary" class="w-4.5 h-4.5"></i>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-black text-[#18181B] group-hover:text-[#CE2D2D] transition-colors font-sans">
                                    {{ $bios->system }}
                                </h2>
                                <span class="text-[10px] font-mono text-gray-500 block">{{ $bios->version }}</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-[#FAF7F2] text-[#CE2D2D] border border-[#DDD6CB] text-[10px] font-mono font-black shrink-0">
                            {{ $bios->size }}
                        </span>
                    </div>

                    <!-- Description -->
                    <p class="text-xs text-gray-600 font-sans leading-relaxed">
                        {{ $bios->description }}
                    </p>

                    <!-- Technical Matrix -->
                    <div class="bg-[#FAF7F2] border border-[#DDD6CB] rounded-xl p-3 space-y-1.5 font-mono text-[11px]">
                        @if($bios->files)
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-500 shrink-0">Archivos:</span>
                            <strong class="text-[#18181B] truncate text-right text-[10px]" title="{{ $bios->files }}">{{ $bios->files }}</strong>
                        </div>
                        @endif
                        @if($bios->emulator)
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-500 shrink-0">Emulador:</span>
                            <span class="text-[#CE2D2D] font-bold truncate text-right text-[10px]" title="{{ $bios->emulator }}">{{ $bios->emulator }}</span>
                        </div>
                        @endif
                        @if($bios->md5)
                        <div class="flex items-center justify-between pt-1 border-t border-[#E5E0D8] text-[10px]">
                            <span class="text-gray-400">MD5:</span>
                            <div class="flex items-center gap-1.5">
                                <code class="text-gray-600 font-mono select-all">{{ $bios->md5 }}</code>
                                <button type="button"
                                        @click="copyHash('{{ $bios->md5 }}', {{ $bios->id }})"
                                        class="text-gray-400 hover:text-[#CE2D2D] transition-colors"
                                        title="Copiar hash MD5">
                                    <span x-show="copiedMd5 !== {{ $bios->id }}">
                                        <i data-lucide="copy" class="w-3 h-3"></i>
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

                <!-- Bottom CTA Download Button -->
                <div class="pt-2">
                    <a href="{{ $bios->download_url }}" 
                       target="_blank"
                       rel="noopener noreferrer"
                       class="w-full py-2.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white border border-[#1E1E1E] font-black text-xs font-sans uppercase tracking-wider flex items-center justify-center gap-2 transition-all shadow-md shadow-red-500/20 active:scale-95">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Descargar BIOS Directa ({{ $bios->size }})</span>
                    </a>
                </div>

            </div>
        @endforeach
    </div>

</main>
@endsection
