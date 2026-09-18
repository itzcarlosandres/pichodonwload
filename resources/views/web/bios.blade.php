@extends('layouts.web')

@section('title', 'Descargar BIOS Oficiales y Verificadas para Emuladores — ' . \App\Models\Setting::get('site_name', 'ROMHUB'))
@section('meta_description', 'Descarga archivos BIOS oficiales y volcados limpios (No-Intro) para PlayStation 2, PS1, PS3, Dreamcast, Nintendo DS y GBA con hashes verificados.')

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-8 space-y-8">

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
            <span>Vault de Preservación • 100% Hashes MD5 / SHA-1 Verificados</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-[#E5E0D8] pb-6">
            <div class="space-y-2 max-w-2xl">
                <h1 class="text-3xl sm:text-5xl font-black text-[#18181B] tracking-tight font-sans">
                    BIOS & Firmware para Emuladores
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 font-sans leading-relaxed">
                    Descarga los archivos de sistema originales indispensables para arrancar juegos en <strong>PCSX2, DuckStation, RPCS3, Flycast y MelonDS</strong>. Archivos limpios sin corrupción ni malware.
                </p>
            </div>

            <!-- Quick Notice Badge -->
            <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-white border-2 border-[#1E1E1E] font-mono shrink-0 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-[#FDF2F2] border border-[#FCA5A5] text-[#CE2D2D] flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-[#18181B] block">Sin Publicidad Ni Acortadores</span>
                    <span class="text-[10px] text-gray-500 block">Descarga directa SSL cifrada</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Instructions Banner -->
    <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] text-[#CE2D2D] flex items-center justify-center shrink-0">
                <i data-lucide="help-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-xs font-bold font-mono text-[#18181B] uppercase">¿Cómo instalar los archivos BIOS?</h3>
                <p class="text-xs text-gray-600 font-sans mt-0.5">Descomprime el archivo `.zip` y copia los archivos `.bin` o `.rom` dentro de la carpeta `/bios` de tu emulador favorito.</p>
            </div>
        </div>
        <a href="{{ route('emulators') }}" class="px-4 py-2 rounded-xl bg-[#EDE7DE] hover:bg-[#E2DACF] text-[#18181B] border border-[#DDD6CB] text-xs font-mono font-bold whitespace-nowrap flex items-center gap-1.5 transition-colors shrink-0">
            <span>Ver Directorio de Emuladores</span>
            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
        </a>
    </div>

    <!-- BIOS Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($biosList as $bios)
            <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-5 flex flex-col justify-between space-y-4 hover:shadow-md transition-all group">
                
                <div class="space-y-3">
                    <!-- Top header -->
                    <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] group-hover:bg-[#FDF2F2] group-hover:border-[#FCA5A5] flex items-center justify-center text-[#18181B] group-hover:text-[#CE2D2D] transition-colors">
                                <i data-lucide="binary" class="w-4.5 h-4.5"></i>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-black text-[#18181B] group-hover:text-[#CE2D2D] transition-colors font-sans">
                                    {{ $bios->system }}
                                </h2>
                                <span class="text-[10px] font-mono text-gray-500 block">{{ $bios->version }}</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-[#FAF7F2] text-[#CE2D2D] border border-[#DDD6CB] text-[10px] font-mono font-black">
                            {{ $bios->size }}
                        </span>
                    </div>

                    <!-- Description -->
                    <p class="text-xs text-gray-600 font-sans leading-relaxed">
                        {{ $bios->description }}
                    </p>

                    <!-- Technical Matrix -->
                    <div class="bg-[#FAF7F2] border border-[#DDD6CB] rounded-xl p-3 space-y-1.5 font-mono text-[11px]">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Archivos incluidos:</span>
                            <strong class="text-[#18181B] truncate max-w-[200px]">{{ $bios->files }}</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Emulador compatible:</span>
                            <span class="text-[#CE2D2D] font-bold truncate max-w-[200px]">{{ $bios->emulator }}</span>
                        </div>
                        @if($bios->md5)
                        <div class="flex items-center justify-between pt-1 border-t border-[#E5E0D8] text-[10px]">
                            <span class="text-gray-400">MD5:</span>
                            <code class="text-gray-600 font-mono select-all">{{ $bios->md5 }}</code>
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
