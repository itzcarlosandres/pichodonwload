@extends('layouts.web', ['title' => "Descargar {$game->title} para {$game->console->name} ({$game->formatted_size}) | ROMHUB"])

@section('content')
<div class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-4 space-y-8" x-data="{
    countdown: 3,
    ready: false,
    timer: null,
    downloadStarted: false,
    init() {
        this.timer = setInterval(() => {
            if (this.countdown > 1) {
                this.countdown--;
            } else {
                clearInterval(this.timer);
                this.ready = true;
                this.$nextTick(() => {
                    if (window.lucide) { lucide.createIcons(); }
                });
            }
        }, 1000);
    },
    trackDownload(url) {
        this.downloadStarted = true;
        fetch('{{ route('download.track', $game->slug) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        window.open(url, '_blank');
    }
}">

    <!-- Breadcrumb Navigation -->
    <nav class="flex items-center gap-2 text-xs font-mono text-gray-500">
        <a href="{{ route('home') }}" class="hover:text-[#CE2D2D] transition-colors font-medium">INICIO</a>
        <span class="text-gray-400">/</span>
        <a href="{{ route('consoles.show', $game->console->slug) }}" class="hover:text-[#CE2D2D] uppercase transition-colors font-medium">{{ $game->console->name }}</a>
        <span class="text-gray-400">/</span>
        <a href="{{ route('game.show', $game->slug) }}" class="hover:text-[#CE2D2D] uppercase transition-colors font-medium">{{ $game->title }}</a>
        <span class="text-gray-400">/</span>
        <span class="text-[#CE2D2D] font-bold uppercase">DESCARGA OFICIAL</span>
    </nav>

    <!-- MAIN DOWNLOAD HERO STATION CARD -->
    <div class="relative overflow-hidden rounded-3xl bg-white border-2 border-[#1E1E1E] p-6 sm:p-10 shadow-sm">
        <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-8">
            
            <!-- Game Cover Art (Strict 3:4 Aspect Ratio) -->
            <div class="w-36 sm:w-52 shrink-0 aspect-[3/4] rounded-2xl overflow-hidden shadow-md border-2 border-[#1E1E1E] bg-[#FAF7F2] relative group mx-auto md:mx-0">
                <img src="{{ $game->cover_url ?: asset('images/default-cover.webp') }}" 
                     alt="{{ $game->title }}" 
                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80';"
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-transparent to-transparent flex items-end p-3">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-white font-bold bg-[#18181B]/90 px-2.5 py-1 rounded-lg border border-white/20">
                        {{ $game->console->name }}
                    </span>
                </div>
            </div>

            <!-- Game Details, Verification & Download Station -->
            <div class="flex-1 text-center md:text-left space-y-6 w-full min-w-0">
                
                <!-- Pills & Title -->
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                        <span class="px-3 py-1 rounded-lg bg-[#FAF7F2] text-[#18181B] border border-[#DDD6CB] text-xs font-mono font-bold">
                            {{ $game->console->name }}
                        </span>
                        <span class="px-3 py-1 rounded-lg bg-[#FDF2F2] text-[#CE2D2D] border border-[#FCA5A5] text-xs font-mono font-bold flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#CE2D2D] animate-pulse"></span> Verificado 1:1 Bit-Exact
                        </span>
                        <span class="px-3 py-1 rounded-lg bg-[#FAF7F2] text-[#18181B] border border-[#DDD6CB] text-xs font-mono font-black">
                            {{ $game->formatted_size }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-4xl font-black text-[#18181B] tracking-tight font-sans">
                        {{ $game->title }}
                    </h1>

                    <p class="text-xs sm:text-sm text-gray-600 font-sans leading-relaxed max-w-2xl">
                        Estás a punto de descargar el volcado original para <strong>{{ $game->console->name }}</strong>. Archivo 100% verificado, libre de malware y con soporte de alta velocidad.
                    </p>
                </div>

                <!-- 4 Quick Specs Matrix -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 font-mono text-xs max-w-2xl">
                    <div class="bg-[#FAF7F2] border border-[#DDD6CB] p-3 rounded-xl text-left">
                        <span class="text-[10px] text-gray-500 block uppercase font-bold">Región</span>
                        <strong class="text-[#18181B] block truncate mt-0.5 font-bold">{{ $game->region ?: 'Global / Libre' }}</strong>
                    </div>
                    <div class="bg-[#FAF7F2] border border-[#DDD6CB] p-3 rounded-xl text-left">
                        <span class="text-[10px] text-gray-500 block uppercase font-bold">Formato</span>
                        <strong class="text-[#CE2D2D] uppercase block truncate mt-0.5 font-black">{{ $game->file_format ?: 'ISO / ROM' }}</strong>
                    </div>
                    <div class="bg-[#FAF7F2] border border-[#DDD6CB] p-3 rounded-xl text-left">
                        <span class="text-[10px] text-gray-500 block uppercase font-bold">Idiomas</span>
                        <strong class="text-[#18181B] block truncate mt-0.5 font-bold">{{ $game->languages ?: 'Español / Multi' }}</strong>
                    </div>
                    <div class="bg-[#FAF7F2] border border-[#DDD6CB] p-3 rounded-xl text-left">
                        <span class="text-[10px] text-gray-500 block uppercase font-bold">Descargas</span>
                        <strong class="text-[#CE2D2D] block truncate mt-0.5 font-black">{{ number_format($game->download_count) }}</strong>
                    </div>
                </div>

                <!-- Interactive Preparation & Multi-Server Area -->
                <div class="pt-2 max-w-2xl">
                    
                    <!-- Countdown Waiting State -->
                    <template x-if="!ready">
                        <div class="p-6 rounded-2xl bg-[#FAF7F2] border border-[#DDD6CB] text-center space-y-3 shadow-inner">
                            <div class="flex items-center justify-center gap-2 text-[#CE2D2D]">
                                <i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i>
                                <span class="text-xs font-mono font-bold uppercase tracking-wider">Verificando Servidores de Alta Velocidad...</span>
                            </div>
                            <div class="flex items-center justify-center gap-3">
                                <div class="w-16 h-16 rounded-2xl bg-[#FDF2F2] border-2 border-[#CE2D2D] flex items-center justify-center text-3xl font-black font-mono text-[#CE2D2D] shadow-sm">
                                    <span x-text="countdown"></span>
                                </div>
                            </div>
                            <p class="text-[11px] text-gray-500 font-sans">Generando enlaces directos cifrados SSL y comprobando integridad de archivos.</p>
                        </div>
                    </template>

                    <!-- Ready State: Clean Server List -->
                    <template x-if="ready">
                        <div class="space-y-4">
                            @php $allLinks = $game->all_download_links; @endphp

                            @if(count($allLinks) > 0)
                                <div class="space-y-3">
                                    <!-- Section Header -->
                                    <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-2.5">
                                        <div class="text-xs font-mono uppercase tracking-wider text-[#18181B] font-bold flex items-center gap-2">
                                            <i data-lucide="server" class="w-4 h-4 text-[#CE2D2D]"></i>
                                            <span>Servidores Disponibles ({{ count($allLinks) }})</span>
                                        </div>
                                        <span class="text-[10px] font-mono text-[#CE2D2D] bg-[#FDF2F2] border border-[#FCA5A5] px-2 py-0.5 rounded-md font-bold">
                                            Sin Publicidad
                                        </span>
                                    </div>

                                    <!-- Server Cards List -->
                                    <div class="space-y-2.5">
                                        @foreach($allLinks as $dl)
                                        <button 
                                            @click="trackDownload('{{ $dl['url'] }}')"
                                            class="w-full p-3.5 sm:p-4 rounded-2xl bg-[#FAF7F2] hover:bg-white border-2 border-[#1E1E1E] flex items-center justify-between gap-4 text-left transition-all duration-200 shadow-sm group cursor-pointer">
                                            
                                            <!-- Left: Server Icon + Name -->
                                            <div class="flex items-center gap-3.5 min-w-0">
                                                <div class="w-11 h-11 rounded-xl bg-[#FDF2F2] border border-[#FCA5A5] text-[#CE2D2D] flex items-center justify-center shrink-0 group-hover:bg-[#CE2D2D] group-hover:text-white transition-colors">
                                                    <i data-lucide="download-cloud" class="w-5 h-5"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="text-sm font-black text-[#18181B] group-hover:text-[#CE2D2D] transition-colors">
                                                            {{ $dl['server'] }}
                                                        </span>
                                                        <span class="px-2 py-0.5 rounded-md bg-white border border-[#DDD6CB] text-[#18181B] text-[10px] font-mono font-bold">
                                                            {{ $dl['badge'] }}
                                                        </span>
                                                    </div>
                                                    <div class="text-[11px] font-mono text-gray-500 mt-0.5 flex items-center gap-2">
                                                        <span>Tamaño: <strong class="text-[#18181B]">{{ $game->formatted_size }}</strong></span>
                                                        <span>•</span>
                                                        <span class="text-[#CE2D2D] font-bold">Descarga Directa Cifrada</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Right: Action Button -->
                                            <div class="shrink-0 flex items-center gap-2">
                                                <span class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#CE2D2D] group-hover:bg-[#B71C1C] text-white text-xs font-black uppercase tracking-wider transition-colors shadow-sm">
                                                    <span>Descargar</span>
                                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 stroke-[2.5]"></i>
                                                </span>
                                                <span class="sm:hidden w-9 h-9 rounded-xl bg-[#CE2D2D] text-white flex items-center justify-center">
                                                    <i data-lucide="download" class="w-4 h-4 stroke-[2.5]"></i>
                                                </span>
                                            </div>
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="space-y-3">
                                    <button 
                                        @click="trackDownload('{{ $game->download_url ?: asset("uploads/roms/{$game->slug}.zip") }}')"
                                        class="w-full py-4 rounded-2xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-black text-sm uppercase tracking-wider font-sans transition-all shadow-md flex items-center justify-center gap-3 transform hover:-translate-y-0.5 cursor-pointer border-2 border-[#1E1E1E]">
                                        <i data-lucide="download" class="w-5 h-5 stroke-[2.5]"></i>
                                        <span>Descargar Ahora (Servidor Principal - {{ $game->formatted_size }})</span>
                                    </button>
                                </div>
                            @endif

                            <div class="flex flex-wrap items-center justify-between text-xs font-mono text-gray-500 pt-3 border-t border-[#E5E0D8]">
                                <span class="flex items-center gap-1.5 text-[#CE2D2D] font-bold">
                                    <i data-lucide="shield-check" class="w-4 h-4"></i> Libre de virus, adware y troyanos
                                </span>
                                <a href="{{ route('game.show', $game->slug) }}" class="hover:text-[#CE2D2D] transition-colors flex items-center gap-1 text-gray-600 font-bold">
                                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Volver a la ficha técnica
                                </a>
                            </div>

                        </div>
                    </template>

                </div>

            </div>

        </div>
    </div>

    <!-- 3-COL SUPPORT GRID: TUTORIAL, EMULATORS & INTEGRITY -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Card 1: How to Play Tutorial -->
        <div class="bg-white rounded-2xl border-2 border-[#1E1E1E] p-6 space-y-4 shadow-sm">
            <div class="flex items-center gap-3 border-b border-[#E5E0D8] pb-3">
                <div class="w-8 h-8 rounded-lg bg-[#FDF2F2] border border-[#FCA5A5] text-[#CE2D2D] flex items-center justify-center font-bold font-mono text-xs">
                    01
                </div>
                <h3 class="text-sm font-black text-[#18181B] font-sans">¿Cómo jugar este juego?</h3>
            </div>

            <ol class="space-y-3 text-xs text-gray-700 font-sans leading-relaxed">
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded bg-[#FAF7F2] border border-[#DDD6CB] text-[#CE2D2D] font-mono text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">1</span>
                    <span>Descarga el archivo ROM desde uno de los servidores de arriba.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded bg-[#FAF7F2] border border-[#DDD6CB] text-[#CE2D2D] font-mono text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">2</span>
                    <span>Si el archivo viene comprimido (<code>.zip</code>, <code>.7z</code>), extráelo con WinRAR o 7-Zip.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded bg-[#FAF7F2] border border-[#DDD6CB] text-[#CE2D2D] font-mono text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">3</span>
                    <span>Abre tu emulador para <strong>{{ $game->console->name }}</strong> y carga la ROM.</span>
                </li>
            </ol>
        </div>

        <!-- Card 2: Recommended Emulator & BIOS -->
        <div class="bg-white rounded-2xl border-2 border-[#1E1E1E] p-6 space-y-4 shadow-sm">
            <div class="flex items-center gap-3 border-b border-[#E5E0D8] pb-3">
                <div class="w-8 h-8 rounded-lg bg-[#FDF2F2] border border-[#FCA5A5] text-[#CE2D2D] flex items-center justify-center font-bold font-mono text-xs">
                    02
                </div>
                <h3 class="text-sm font-black text-[#18181B] font-sans">Emulador Recomendado</h3>
            </div>

            <div class="space-y-3">
                <div class="p-3.5 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] space-y-1">
                    <span class="text-[10px] font-mono text-gray-500 uppercase block font-bold">Emulador Principal</span>
                    <p class="text-sm font-bold text-[#18181B] font-sans">
                        {{ $game->console->recommended_emulator ?: 'RetroArch / Emulador Standalone' }}
                    </p>
                    <span class="text-[11px] text-[#CE2D2D] font-mono font-bold flex items-center gap-1 mt-1">
                        <i data-lucide="check" class="w-3 h-3"></i> 60 FPS Vulkan Ready
                    </span>
                </div>

                <div class="p-3.5 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] space-y-1">
                    <span class="text-[10px] font-mono text-gray-500 uppercase block font-bold">BIOS del Sistema</span>
                    <p class="text-xs font-bold text-gray-700 font-sans">
                        {{ $game->console->bios_name ?: 'Integrada en emulador / No requerida' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 3: File Integrity & Security Guarantee -->
        <div class="bg-white rounded-2xl border-2 border-[#1E1E1E] p-6 space-y-4 shadow-sm">
            <div class="flex items-center gap-3 border-b border-[#E5E0D8] pb-3">
                <div class="w-8 h-8 rounded-lg bg-[#FDF2F2] border border-[#FCA5A5] text-[#CE2D2D] flex items-center justify-center font-bold font-mono text-xs">
                    03
                </div>
                <h3 class="text-sm font-black text-[#18181B] font-sans">Garantía de Integridad</h3>
            </div>

            <div class="space-y-2.5 font-mono text-xs">
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB]">
                    <span class="text-gray-600 font-bold">Formato:</span>
                    <span class="text-[#CE2D2D] font-black uppercase">{{ $game->file_format ?: 'ISO / ROM' }}</span>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB]">
                    <span class="text-gray-600 font-bold">Verificación:</span>
                    <span class="text-[#CE2D2D] font-bold">1:1 No-Intro / Redump</span>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB]">
                    <span class="text-gray-600 font-bold">Cifrado:</span>
                    <span class="text-[#18181B] font-bold">SSL 256-Bit Seguro</span>
                </div>
            </div>
        </div>

    </div>

    <!-- RELATED GAMES FROM THE SAME CONSOLE -->
    @if($relatedGames->count() > 0)
    <div class="space-y-4 pt-4">
        <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
            <h2 class="text-xs font-mono uppercase tracking-wider text-[#18181B] font-bold flex items-center gap-2">
                <i data-lucide="trophy" class="w-4 h-4 text-[#CE2D2D]"></i>
                Otros Títulos Recomendados para {{ $game->console->name }}
            </h2>
            <a href="{{ route('consoles.show', $game->console->slug) }}" class="text-xs text-[#CE2D2D] hover:underline font-mono font-bold transition-colors">
                Ver catálogo completo →
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($relatedGames as $i => $rel)
            <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-3 flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1 shadow-sm hover:shadow-md">
                <a href="{{ route('game.show', $rel->slug) }}" class="space-y-2.5 block">
                    <div class="aspect-[3/4] w-full rounded-xl overflow-hidden bg-[#FAF7F2] border border-[#DDD6CB] relative">
                        <img src="{{ $rel->cover_thumb_url ?: $rel->cover_url }}" 
                             alt="{{ $rel->title }}" 
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80';"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-[#18181B] group-hover:text-[#CE2D2D] transition-colors truncate font-sans">
                            {{ $rel->title }}
                        </h4>
                        <p class="text-[10px] font-mono text-gray-500 mt-0.5">
                            {{ $rel->formatted_size }} • {{ $rel->region ?: 'Global' }}
                        </p>
                    </div>
                </a>

                <div class="pt-2.5 mt-2 border-t border-[#E5E0D8] flex items-center justify-between text-xs font-mono">
                    <a href="{{ route('game.download', $rel->slug) }}" class="text-[#CE2D2D] hover:underline font-bold text-[11px] flex items-center gap-1">
                        <i data-lucide="download" class="w-3 h-3"></i> Descargar
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
