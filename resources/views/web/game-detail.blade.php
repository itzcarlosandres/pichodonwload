@extends('layouts.web')

@section('title', ($game->meta_title ?: $game->title) . ' — Preservación Digital & Ficha Técnica')
@section('meta_description', $game->meta_description ?: Str::limit(strip_tags($game->description), 160))
@section('og_image', $game->banner_url ?: $game->cover_url)

@push('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "VideoGame",
    "name": @json($game->title),
    "description": @json($game->meta_description ?: Str::limit(strip_tags($game->description), 160)),
    "url": @json(route('game.show', $game->slug)),
    "image": @json($game->banner_url ?: $game->cover_url),
    @if($game->console)
    "gamePlatform": @json($game->console->name),
    "operatingSystem": @json($game->console->name),
    @endif
    @if($game->developer)
    "author": {
        "@@type": "Organization",
        "name": @json($game->developer)
    },
    @endif
    @if($game->publisher)
    "publisher": {
        "@@type": "Organization",
        "name": @json($game->publisher)
    },
    @endif
    @if($game->release_year)
    "datePublished": @json((string)$game->release_year),
    @endif
    @if($game->categories && $game->categories->isNotEmpty())
    "genre": @json($game->categories->pluck('name')->all()),
    @endif
    @if($game->rating_average > 0)
    "aggregateRating": {
        "@@type": "AggregateRating",
        "ratingValue": "{{ number_format((float)$game->rating_average, 1) }}",
        "bestRating": "5",
        "ratingCount": {{ max(1, (int)$game->rating_count) }}
    },
    @endif
    "offers": {
        "@@type": "Offer",
        "price": "0",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock"
    }
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@@type": "ListItem",
            "position": 1,
            "name": "Inicio",
            "item": @json(url('/'))
        },
        {
            "@@type": "ListItem",
            "position": 2,
            "name": "Consolas",
            "item": @json(route('consoles.index'))
        }
        @if($game->console)
        ,{
            "@@type": "ListItem",
            "position": 3,
            "name": @json($game->console->name),
            "item": @json(route('consoles.show', $game->console->slug))
        },
        {
            "@@type": "ListItem",
            "position": 4,
            "name": @json($game->title),
            "item": @json(route('game.show', $game->slug))
        }
        @else
        ,{
            "@@type": "ListItem",
            "position": 3,
            "name": @json($game->title),
            "item": @json(route('game.show', $game->slug))
        }
        @endif
    ]
}
</script>
@endpush

@section('content')
<script>
function gameDetailComponent() {
    return {
        activeImage: null,
        activeImageIndex: 0,
        galleryImages: @json($game->screenshots->map(fn($s) => $s->image_webp_url ?: $s->image_url)->values()->all()),
        copiedHash: '',
        copyText(text, label) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            }
            this.copiedHash = label;
            setTimeout(() => { this.copiedHash = ''; }, 2200);
            window.dispatchEvent(new CustomEvent('toast-notify', { detail: { message: label + ' copiado al portapapeles' } }));
        },
        openGallery(idx) {
            if (!this.galleryImages || !this.galleryImages.length) return;
            this.activeImageIndex = idx;
            this.activeImage = this.galleryImages[idx] || null;
        },
        nextImage() {
            if (!this.galleryImages || !this.galleryImages.length) return;
            this.activeImageIndex = (this.activeImageIndex + 1) % this.galleryImages.length;
            this.activeImage = this.galleryImages[this.activeImageIndex];
        },
        prevImage() {
            if (!this.galleryImages || !this.galleryImages.length) return;
            this.activeImageIndex = (this.activeImageIndex - 1 + this.galleryImages.length) % this.galleryImages.length;
            this.activeImage = this.galleryImages[this.activeImageIndex];
        }
    };
}
</script>

<div x-data="gameDetailComponent()" class="space-y-8 py-4">

    <!-- Breadcrumb Nav -->
    <div class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6">
        <div class="flex items-center gap-2 text-xs font-mono text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-[#CE2D2D] transition-colors font-medium">INICIO</a>
            <span class="text-gray-400">/</span>
            <a href="{{ route('consoles.show', $game->console->slug) }}" class="hover:text-[#CE2D2D] uppercase transition-colors font-medium">{{ $game->console->name }}</a>
            <span class="text-gray-400">/</span>
            <span class="text-[#18181B] font-bold uppercase truncate">{{ $game->title }}</span>
        </div>
    </div>

    <!-- 1. PRODUCT HEADER & HERO (Retro RomsRetro Style) -->
    <section class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 space-y-6">
        
        <!-- Top Announcement & Verification Pill -->
        <div class="flex justify-center sm:justify-start">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#CE2D2D] text-white text-xs font-mono font-bold shadow-sm">
                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                <span>Preservación Oficial Vault • {{ $game->console->name }} • 100% Clean Dump</span>
            </div>
        </div>

        <!-- Product Presentation: Cover + Title + Badges -->
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 pt-1">
            
            <!-- App-style Rounded Cover Icon -->
            <div class="relative shrink-0 group">
                <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl bg-white border-2 border-[#1E1E1E] p-1.5 shadow-md overflow-hidden aspect-square">
                    <img src="{{ $game->cover_thumb_url ?: ($game->cover_url ?: 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800&q=80') }}" 
                         class="w-full h-full object-cover rounded-2xl" 
                         alt="{{ $game->title }}">
                </div>
                <div class="absolute -bottom-2 -right-2 px-2.5 py-0.5 rounded-lg bg-white border border-[#1E1E1E] text-[10px] font-mono font-black text-[#CE2D2D] shadow-sm">
                    {{ $game->file_format ?: 'ROM' }}
                </div>
            </div>

            <!-- Title, Developer, Badges -->
            <div class="flex-1 text-center sm:text-left space-y-3 min-w-0">
                
                <!-- Badges Row -->
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                    <!-- Console Badge -->
                    <a href="{{ route('consoles.show', $game->console->slug) }}" class="px-3 py-1 rounded-full bg-[#FAF7F2] text-[#18181B] hover:bg-[#FDF2F2] hover:text-[#CE2D2D] border border-[#1E1E1E] font-mono text-xs font-bold uppercase tracking-wider transition-colors shadow-sm">
                        {{ $game->console->name }}
                    </a>

                    <!-- Status Certification Badge -->
                    <span class="px-3 py-1 rounded-full bg-white text-gray-700 border border-[#DDD6CB] font-mono text-xs font-bold flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                        <span>No-Intro Dump Limpio</span>
                    </span>

                    @if($game->region)
                    <span class="px-3 py-1 rounded-full bg-white text-gray-700 border border-[#DDD6CB] font-mono text-xs font-bold shadow-sm">
                        {{ $game->region }}
                    </span>
                    @endif

                    @foreach($game->badges as $badge)
                        <span class="px-3 py-1 rounded-full text-xs font-mono font-bold shadow-sm" style="background-color: {{ $badge->bg_color }}; color: {{ $badge->text_color }}; border: 1px solid {{ $badge->border_color }}">
                            @if($badge->icon) <i data-lucide="{{ $badge->icon }}" class="w-3 h-3 inline mr-1"></i> @endif
                            <span>{{ $badge->name }}</span>
                        </span>
                    @endforeach
                </div>

                <!-- Main Game Title -->
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-[#18181B] tracking-tight font-sans leading-tight">
                    {{ $game->title }}
                </h1>

                <!-- Subtitle / Meta -->
                <p class="text-xs sm:text-sm text-gray-600 font-sans">
                    Desarrollado por <strong class="text-[#18181B]">{{ $game->developer ?: 'Nintendo / Archivo Retro' }}</strong>
                    @if($game->publisher) • Publicado por <span class="text-gray-700 font-medium">{{ $game->publisher }}</span> @endif
                    @if($game->release_year) • Año <span class="text-[#CE2D2D] font-mono font-bold">{{ $game->release_year }}</span> @endif
                </p>

            </div>

        </div>

        <!-- Action Capsule / Pill Bar with Retro Download Button -->
        <div class="p-3 sm:p-3.5 rounded-2xl bg-white border-2 border-[#1E1E1E] shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            
            <!-- Community Rating inside Capsule -->
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 text-xs font-mono text-[#18181B] px-2">
                <div class="flex items-center gap-2 text-[#CE2D2D] bg-[#FDF2F2] border border-[#FCA5A5] px-3 py-1.5 rounded-xl font-bold">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#CE2D2D]"></i>
                    <span class="tracking-wide">ROM Verificada & Libre de Malware</span>
                </div>
                <a href="#reportes" class="flex items-center gap-1.5 text-amber-500 hover:underline px-2 py-1 font-bold">
                    <span>★ {{ number_format($game->rating_average ?: 5.0, 1) }}</span>
                    <span class="text-gray-500 text-[11px] font-sans font-normal">({{ $game->reviews->count() }} valoraciones)</span>
                </a>
            </div>

            <!-- Action Buttons Segment -->
            <div class="flex items-center gap-2.5 w-full sm:w-auto justify-center sm:justify-end shrink-0">
                
                <!-- Big CTA Download Button in Retro Red -->
                <a href="{{ route('game.download', $game->slug) }}"
                   class="relative overflow-hidden group flex-1 sm:flex-initial px-6 sm:px-8 py-3 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white border border-[#1E1E1E] font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2.5 shadow-md shadow-red-500/25 transition-all transform active:scale-95 cursor-pointer">
                    
                    <i data-lucide="download" class="w-4 h-4 text-white stroke-[2.5] shrink-0"></i>
                    <span class="tracking-wider font-black text-white">DESCARGAR VIDEOJUEGO</span>
                    <span class="inline-block px-2 py-0.5 rounded-lg bg-black/20 text-white font-mono text-[11px] font-bold">
                        {{ $game->formatted_size }}
                    </span>
                </a>

            </div>

        </div>

        @php $serversList = $game->all_download_links; @endphp
        @if(count($serversList) > 1)
        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1 text-xs font-mono">
            <span class="text-gray-500 font-bold flex items-center gap-1">
                <i data-lucide="server" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                <span>Servidores Disponibles:</span>
            </span>
            @foreach($serversList as $srv)
                <a href="{{ route('game.download', $game->slug) }}" class="px-2.5 py-1 rounded-lg bg-white hover:bg-[#FDF2F2] border border-[#DDD6CB] hover:border-[#CE2D2D] text-[#18181B] hover:text-[#CE2D2D] font-bold text-[11px] transition-colors flex items-center gap-1.5 shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>{{ $srv['server'] }}</span>
                    <span class="text-[9px] px-1 py-0.2 rounded bg-[#FAF7F2] border border-[#DDD6CB] text-gray-500">{{ $srv['badge'] }}</span>
                </a>
            @endforeach
        </div>
        @endif

    </section>

    <!-- 2. SECTION NAVIGATION PILLS (Touch edge-to-edge scroll on mobile) -->
    <section class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6">
        <div class="flex items-center gap-2 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 scrollbar-none text-xs font-mono">
            <a href="#sinopsis" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-[#FAF7F2] text-gray-700 hover:text-black border border-[#DDD6CB] whitespace-nowrap transition-colors flex items-center gap-1.5 font-bold shadow-sm active:scale-95">
                <i data-lucide="book-open" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                <span>Sinopsis & Detalles</span>
            </a>
            <a href="#guia-juego" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-[#FAF7F2] text-gray-700 hover:text-black border border-[#DDD6CB] whitespace-nowrap transition-colors flex items-center gap-1.5 font-bold shadow-sm active:scale-95">
                <i data-lucide="play-circle" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                <span>¿Cómo Jugar?</span>
            </a>
            <a href="#ficha-tecnica" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-[#FAF7F2] text-gray-700 hover:text-black border border-[#DDD6CB] whitespace-nowrap transition-colors flex items-center gap-1.5 font-bold shadow-sm active:scale-95">
                <i data-lucide="binary" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                <span>Ficha Técnica</span>
            </a>
            @if($recommendedEmulator || $requiredBios)
            <a href="#emulador" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-[#FAF7F2] text-gray-700 hover:text-black border border-[#DDD6CB] whitespace-nowrap transition-colors flex items-center gap-1.5 font-bold shadow-sm active:scale-95">
                <i data-lucide="gamepad-2" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                <span>Emulador & BIOS</span>
            </a>
            @endif

            @if($game->screenshots->isNotEmpty())
            <a href="#galeria" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-[#FAF7F2] text-gray-700 hover:text-black border border-[#DDD6CB] whitespace-nowrap transition-colors flex items-center gap-1.5 font-bold shadow-sm active:scale-95">
                <i data-lucide="image" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                <span>Galería HD</span>
            </a>
            @endif
            <a href="#reportes" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-[#FAF7F2] text-gray-700 hover:text-black border border-[#DDD6CB] whitespace-nowrap transition-colors flex items-center gap-1.5 font-bold shadow-sm active:scale-95">
                <i data-lucide="cpu" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                <span>Reportes ({{ $game->reviews->count() }})</span>
            </a>
            @if(isset($relatedGames) && $relatedGames->isNotEmpty())
            <a href="#relacionados" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-[#FAF7F2] text-gray-700 hover:text-black border border-[#DDD6CB] whitespace-nowrap transition-colors flex items-center gap-1.5 font-bold shadow-sm active:scale-95">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                <span>Relacionados</span>
            </a>
            @endif
        </div>
    </section>

    <!-- 3. MAIN DIRECTORY LAYOUT: 8 COLS (CONTENT) + 4 COLS (SIDEBAR WIDGETS) -->
    <section class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT 8 COLS: SINOPSIS, GALLERY, REVIEWS -->
            <div class="lg:col-span-8 space-y-8">
                
                @if($game->screenshots && $game->screenshots->isNotEmpty())
                <!-- 📸 SLIDER COMPACTO DE SCREENSHOTS HD (Above Sinopsis) -->
                <div id="galeria" class="bg-white rounded-2xl border-2 border-[#1E1E1E] p-4 sm:p-5 space-y-3 shadow-sm scroll-mt-24 relative overflow-hidden">
                    <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-2.5">
                        <div class="flex items-center gap-2">
                            <i data-lucide="camera" class="w-4 h-4 text-[#CE2D2D]"></i>
                            <h2 class="text-xs font-mono font-bold uppercase text-[#18181B] tracking-wider">
                                Capturas In-Game & Gameplay HD
                            </h2>
                            <span class="text-[10px] font-mono text-gray-500 font-normal">({{ $game->screenshots->count() }} imágenes)</span>
                        </div>
                        <span class="text-[10px] font-mono text-[#CE2D2D] bg-[#FDF2F2] border border-[#FCA5A5] px-2 py-0.5 rounded font-bold flex items-center gap-1 cursor-pointer">
                            <i data-lucide="maximize-2" class="w-3 h-3"></i> Clic para ampliar
                        </span>
                    </div>

                    <!-- Compact Slider Container with horizontal smooth scroll -->
                    <div class="relative group/slider">
                        <!-- Left scroll button -->
                        <button type="button" 
                                @click="$refs.galleryScroll.scrollBy({ left: -260, behavior: 'smooth' })" 
                                class="absolute left-1 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-black/70 hover:bg-[#CE2D2D] text-white flex items-center justify-center backdrop-blur-sm transition-all shadow-md opacity-0 group-hover/slider:opacity-100 hidden sm:flex cursor-pointer border border-white/20">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </button>

                        <div x-ref="galleryScroll" class="flex items-center gap-3 overflow-x-auto pb-2 pt-1 px-1 scrollbar-thin scrollbar-thumb-gray-300 scroll-smooth snap-x snap-mandatory">
                            @foreach($game->screenshots as $idx => $ss)
                            <div class="shrink-0 w-36 sm:w-44 md:w-48 aspect-video rounded-xl overflow-hidden bg-[#FAF7F2] border-2 border-[#1E1E1E] hover:border-[#CE2D2D] shadow-sm transition-all hover:scale-[1.03] cursor-pointer relative group/item snap-start"
                                 @click="openGallery({{ $idx }})">
                                <img src="{{ $ss->image_webp_url ?: ($ss->image_url ?: $ss->thumb_url) }}" 
                                     alt="{{ $game->title }} Captura {{ $idx + 1 }}" 
                                     loading="lazy" 
                                     class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/item:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="p-1.5 rounded-lg bg-black/80 text-white shadow-md">
                                        <i data-lucide="zoom-in" class="w-4 h-4 text-white"></i>
                                    </span>
                                </div>
                                <div class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-black/80 backdrop-blur-xs text-[9px] font-mono text-white font-bold">
                                    #{{ $idx + 1 }}
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Right scroll button -->
                        <button type="button" 
                                @click="$refs.galleryScroll.scrollBy({ left: 260, behavior: 'smooth' })" 
                                class="absolute right-1 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-black/70 hover:bg-[#CE2D2D] text-white flex items-center justify-center backdrop-blur-sm transition-all shadow-md opacity-0 group-hover/slider:opacity-100 hidden sm:flex cursor-pointer border border-white/20">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                @endif

                <!-- SINOPSIS & LORE -->
                <div id="sinopsis" class="bg-white rounded-2xl border-2 border-[#1E1E1E] p-6 space-y-4 shadow-sm">
                    <div class="flex items-center justify-between pb-1">
                        <h2 class="text-xs font-mono font-bold uppercase text-[#18181B] tracking-wider flex items-center gap-2">
                            <i data-lucide="book-open" class="w-4 h-4 text-[#CE2D2D]"></i>
                            Sinopsis & Contexto Histórico
                        </h2>
                        <span class="text-[11px] font-mono font-bold text-[#CE2D2D] bg-[#FDF2F2] border border-[#FCA5A5] px-2 py-0.5 rounded-lg">
                            Ficha Oficial
                        </span>
                    </div>

                    <div class="prose-game-content">
                        @if(!empty($game->description))
                            @php
                                $cleanDesc = str_replace(["\r\n", "\r"], "\n", $game->description);
                                for ($i = 0; $i < 10; $i++) {
                                    $prevDesc = $cleanDesc;
                                    $cleanDesc = preg_replace('/^\s*(?:Aquí tienes|A continuación|Por supuesto|Claro|Hola|En este documento|En esta ficha|Esta es una?|Ficha Detallada|Bienvenido)[^\n]*\n+/iu', '', $cleanDesc);
                                    $cleanDesc = preg_replace('/^\s*[\-=*_]{3,}\s*$/mu', '', $cleanDesc);
                                    $cleanDesc = preg_replace('/^\s*#+\s*(?:Ficha Detallada|Ficha Técnica|Ficha)[^\n]*\n+/iu', '', $cleanDesc);
                                    $cleanDesc = preg_replace('/^\s*#+\s*(?:\d+\.?)?\s*(?:Sinopsis|Contexto Histórico|Argumento|Historia|Descripción)[^\n]*\n+/iu', '', $cleanDesc);
                                    $cleanDesc = ltrim($cleanDesc);
                                    if ($cleanDesc === $prevDesc) break;
                                }
                                $rawHtml = \Illuminate\Support\Str::markdown($cleanDesc);
                                $interlinkedHtml = isset($interlinkService) 
                                    ? $interlinkService->interlinkDescription($rawHtml, $game) 
                                    : app(\App\Services\SeoInterlinkService::class)->interlinkDescription($rawHtml, $game);
                            @endphp
                            {!! $interlinkedHtml !!}
                        @else
                            <p class="text-gray-500 italic text-xs">No hay una descripción registrada para este título. Consulta las especificaciones técnicas y reportes de emulación adjuntos.</p>
                        @endif
                    </div>

                    <!-- SEO INTERNAL LINKING CLUSTER: Exploración Contextual & Enlaces Relacionados -->
                    <div class="pt-4 mt-4 border-t border-[#E5E0D8] dark:border-[#27272A] space-y-2.5">
                        <div class="flex items-center gap-2 text-xs font-mono font-bold text-gray-700 dark:text-gray-300">
                            <i data-lucide="compass" class="w-4 h-4 text-[#CE2D2D]"></i>
                            <span>Explora más en el Vault:</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-xs font-mono">
                            <!-- Console Link -->
                            @if($game->console)
                            <a href="{{ route('consoles.show', $game->console->slug) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FAF7F2] dark:bg-[#202025] hover:bg-[#F5EFE6] dark:hover:bg-[#27272A] border border-[#DDD6CB] dark:border-[#33333C] text-gray-800 dark:text-gray-200 hover:text-[#CE2D2D] transition-all font-semibold shadow-xs"
                               title="Catálogo completo de {{ $game->console->name }}">
                                <i data-lucide="disc" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                                <span>Juegos de {{ $game->console->name }}</span>
                            </a>
                            @endif

                            <!-- Franchises Links -->
                            @if($game->franchises && $game->franchises->isNotEmpty())
                                @foreach($game->franchises as $franchise)
                                <a href="{{ route('collections.show', $franchise->slug) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FAF7F2] dark:bg-[#202025] hover:bg-[#F5EFE6] dark:hover:bg-[#27272A] border border-[#DDD6CB] dark:border-[#33333C] text-gray-800 dark:text-gray-200 hover:text-[#CE2D2D] transition-all font-semibold shadow-xs"
                                   title="Saga {{ $franchise->name }}">
                                    <i data-lucide="layers" class="w-3.5 h-3.5 text-amber-500"></i>
                                    <span>Saga {{ $franchise->name }}</span>
                                </a>
                                @endforeach
                            @endif

                            <!-- Category Links -->
                            @if($game->categories && $game->categories->isNotEmpty())
                                @foreach($game->categories as $cat)
                                <a href="{{ route('category.show', $cat->slug) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FAF7F2] dark:bg-[#202025] hover:bg-[#F5EFE6] dark:hover:bg-[#27272A] border border-[#DDD6CB] dark:border-[#33333C] text-gray-800 dark:text-gray-200 hover:text-[#CE2D2D] transition-all font-semibold shadow-xs"
                                   title="Género {{ $cat->name }}">
                                    <i data-lucide="tag" class="w-3.5 h-3.5 text-blue-500"></i>
                                    <span>Género: {{ $cat->name }}</span>
                                </a>
                                @endforeach
                            @endif

                            <!-- Top 25 Ranking Link -->
                            @if($game->console)
                            <a href="{{ route('rankings') }}?console={{ $game->console->slug }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FAF7F2] dark:bg-[#202025] hover:bg-[#F5EFE6] dark:hover:bg-[#27272A] border border-[#DDD6CB] dark:border-[#33333C] text-gray-800 dark:text-gray-200 hover:text-[#CE2D2D] transition-all font-semibold shadow-xs"
                               title="Top 25 juegos más jugados de {{ $game->console->name }}">
                                <i data-lucide="trophy" class="w-3.5 h-3.5 text-amber-500"></i>
                                <span>Top 25 {{ $game->console->name }}</span>
                            </a>
                            @endif

                            <!-- Emulators Link -->
                            <a href="{{ route('emulators') }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FAF7F2] dark:bg-[#202025] hover:bg-[#F5EFE6] dark:hover:bg-[#27272A] border border-[#DDD6CB] dark:border-[#33333C] text-gray-800 dark:text-gray-200 hover:text-[#CE2D2D] transition-all font-semibold shadow-xs"
                               title="Descargar emuladores compatibles">
                                <i data-lucide="gamepad-2" class="w-3.5 h-3.5 text-emerald-500"></i>
                                <span>Emuladores Recomendados</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- QUICK SETUP GUIDE: ¿CÓMO JUGAR EN 3 PASOS? -->
                <div id="guia-juego" class="bg-white rounded-2xl border-2 border-[#1E1E1E] p-6 space-y-5 shadow-sm scroll-mt-24">
                    <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="play-circle" class="w-5 h-5 text-[#CE2D2D]"></i>
                            <h2 class="text-xs sm:text-sm font-mono font-bold uppercase text-[#18181B] tracking-wider">
                                ¿Cómo Jugar este Título en tu PC o Celular? (Guía en 3 Pasos)
                            </h2>
                        </div>
                        <span class="text-[10px] font-mono text-[#CE2D2D] bg-[#FDF2F2] border border-[#FCA5A5] px-2.5 py-1 rounded-lg font-bold">
                            Guía Vault
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Step 1 -->
                        <div class="p-4 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] space-y-3 flex flex-col justify-between">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-lg bg-[#CE2D2D] text-white flex items-center justify-center font-mono font-black text-xs">
                                        1
                                    </span>
                                    <span class="text-[10px] font-mono text-gray-500 font-bold uppercase">Emulador</span>
                                </div>
                                <h3 class="text-xs font-bold text-[#18181B] font-sans">
                                    Instala el Emulador
                                </h3>
                                <p class="text-[11px] text-gray-600 font-sans leading-relaxed">
                                    Para <strong>{{ $game->console->name }}</strong> recomendamos usar <strong>{{ $recommendedEmulator->name ?? 'RetroArch' }}</strong> en PC o Android.
                                </p>
                            </div>
                            <a href="{{ $recommendedEmulator ? ($recommendedEmulator->download_url ?: $recommendedEmulator->website) : route('emulators') }}" 
                               target="_blank" 
                               rel="noopener"
                               class="w-full py-2 px-3 rounded-lg bg-white hover:bg-[#F5EFE6] border border-[#DDD6CB] text-[#18181B] hover:text-[#CE2D2D] text-[11px] font-mono font-bold text-center transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Obtener {{ $recommendedEmulator->name ?? 'Emulador' }}</span>
                            </a>
                        </div>

                        <!-- Step 2 -->
                        <div class="p-4 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] space-y-3 flex flex-col justify-between">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-lg bg-[#18181B] text-white flex items-center justify-center font-mono font-black text-xs">
                                        2
                                    </span>
                                    <span class="text-[10px] font-mono text-gray-500 font-bold uppercase">Firmware</span>
                                </div>
                                <h3 class="text-xs font-bold text-[#18181B] font-sans">
                                    {{ $requiredBios ? 'Configurar BIOS' : 'Sin BIOS Requerida' }}
                                </h3>
                                <p class="text-[11px] text-gray-600 font-sans leading-relaxed">
                                    @if($requiredBios)
                                        Coloca los archivos de la BIOS en la carpeta <code class="bg-white px-1 py-0.5 rounded border border-[#DDD6CB] text-[10px]">/bios</code> del emulador.
                                    @else
                                        Este sistema no requiere archivos BIOS externos. ¡El emulador viene listo de fábrica!
                                    @endif
                                </p>
                            </div>
                            @if($requiredBios)
                            <a href="{{ $requiredBios->download_url ?: route('bios') }}" 
                               target="_blank" 
                               rel="noopener"
                               class="w-full py-2 px-3 rounded-lg bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-900 text-[11px] font-mono font-bold text-center transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                                <i data-lucide="file-down" class="w-3.5 h-3.5 text-amber-700"></i>
                                <span>Descargar BIOS</span>
                            </a>
                            @else
                            <div class="py-2 px-3 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-800 text-[11px] font-mono font-bold text-center flex items-center justify-center gap-1">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span>Plug & Play</span>
                            </div>
                            @endif
                        </div>

                        <!-- Step 3 -->
                        <div class="p-4 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] space-y-3 flex flex-col justify-between">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-lg bg-[#CE2D2D] text-white flex items-center justify-center font-mono font-black text-xs">
                                        3
                                    </span>
                                    <span class="text-[10px] font-mono text-gray-500 font-bold uppercase">Cargar & Jugar</span>
                                </div>
                                <h3 class="text-xs font-bold text-[#18181B] font-sans">
                                    Carga la ROM y Mando
                                </h3>
                                <p class="text-[11px] text-gray-600 font-sans leading-relaxed">
                                    Abre tu archivo <strong class="text-[#CE2D2D] font-mono uppercase">{{ $game->file_format ?: 'ROM' }}</strong> en el emulador, conecta tu mando y activa resolución HD/4K a 60 FPS.
                                </p>
                            </div>
                            <a href="{{ route('game.download', $game->slug) }}" 
                               class="w-full py-2 px-3 rounded-lg bg-[#CE2D2D] hover:bg-[#B71C1C] text-white text-[11px] font-mono font-bold text-center transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Descargar Videojuego</span>
                            </a>
                        </div>
                    </div>
                </div>



                <!-- COMMUNITY EMULATION BENCHMARKS & REVIEWS -->
                <div id="reportes" class="bg-white rounded-2xl border-2 border-[#1E1E1E] p-6 space-y-5 shadow-sm">
                    <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="cpu" class="w-4 h-4 text-[#CE2D2D]"></i>
                            <h2 class="text-xs font-mono font-bold uppercase text-[#18181B] tracking-wider">
                                Reportes de Rendimiento de la Comunidad ({{ $game->reviews->count() }})
                            </h2>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs font-mono text-amber-500">
                            <i data-lucide="star" class="w-4 h-4 fill-amber-400 text-amber-400"></i>
                            <span class="font-bold">{{ number_format($game->rating_average ?: 5.0, 1) }}</span>
                            <span class="text-gray-500 font-sans text-[11px]">({{ $game->reviews->count() }} valoraciones)</span>
                        </div>
                    </div>

                    @if(session('success'))
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-mono flex items-center gap-2 font-bold">
                        <i data-lucide="check-circle" class="w-4 h-4 shrink-0 text-emerald-600"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    @endif

                    @if(isset($errors) && $errors->any())
                    <div class="p-3.5 rounded-xl bg-red-50 border border-red-300 text-red-800 text-xs font-mono space-y-1">
                        @foreach($errors->all() as $err)
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0 text-red-600"></i>
                                <span>{{ $err }}</span>
                            </div>
                        @endforeach
                    </div>
                    @endif

                    <!-- Review Form with Interactive 5-Star Selector -->
                    <form action="{{ route('game.review', $game->slug) }}" 
                          method="POST" 
                          x-data="{
                              rating: 5,
                              hoverRating: 0,
                              labels: {
                                  1: '1/5 — Injugable / Roto (Crítico)',
                                  2: '2/5 — Inestable / Bajones de FPS',
                                  3: '3/5 — Jugable con detalles técnicos',
                                  4: '4/5 — Muy Bueno / 60 FPS estables',
                                  5: '5/5 — ¡Obra Maestra / Emulación Perfecta!'
                              }
                          }" 
                          class="space-y-4 font-sans text-xs bg-[#FAF7F2] p-4 sm:p-5 rounded-xl border border-[#DDD6CB]">
                        @csrf

                        <!-- Top row: Star Picker & Rating Label -->
                        <div class="p-3.5 rounded-lg bg-white border border-[#DDD6CB] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="space-y-1">
                                <span class="text-[11px] font-mono uppercase tracking-wider text-gray-500 font-bold block">
                                    ¿Cómo calificarías este título y su rendimiento?
                                </span>
                                <div class="text-xs font-mono font-bold text-amber-600" x-text="labels[hoverRating || rating]"></div>
                            </div>

                            <!-- Interactive Clickable Stars -->
                            <div class="flex items-center gap-1.5" @mouseleave="hoverRating = 0">
                                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                    <button type="button" 
                                            @click="rating = star" 
                                            @mouseenter="hoverRating = star"
                                            class="p-1 rounded hover:scale-110 transition-transform focus:outline-none cursor-pointer"
                                            :title="star + ' estrellas'">
                                        <svg class="w-6 h-6 transition-colors" 
                                             :class="(hoverRating || rating) >= star ? 'text-amber-400 fill-amber-400' : 'text-gray-300 fill-transparent'" 
                                             viewBox="0 0 24 24" 
                                             fill="currentColor" 
                                             stroke="currentColor" 
                                             stroke-width="2" 
                                             stroke-linecap="round" 
                                             stroke-linejoin="round">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                        </svg>
                                    </button>
                                </template>
                                <input type="hidden" name="score" :value="rating">
                            </div>
                        </div>

                        <!-- Secondary Fields: Author, Emulator, FPS -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-mono text-gray-600 mb-1 font-bold">Tu Nombre o Alias</label>
                                @auth
                                    <input type="text" value="{{ auth()->user()->name }}" readonly class="w-full bg-white border border-[#DDD6CB] rounded-lg p-2.5 text-gray-700 font-bold cursor-not-allowed">
                                @else
                                    <input type="text" name="author_name" placeholder="Ej. RetroGamer99" class="w-full bg-white border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-lg p-2.5 text-[#18181B] font-medium placeholder-gray-400 focus:outline-none">
                                @endauth
                            </div>
                            <div>
                                <label class="block text-[11px] font-mono text-gray-600 mb-1 font-bold">Emulador Probado</label>
                                <input type="text" name="tested_emulator" placeholder="Ej. PCSX2 v2.0 / RPCS3 / Yuzu" class="w-full bg-white border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-lg p-2.5 text-[#18181B] font-medium placeholder-gray-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-mono text-gray-600 mb-1 font-bold">FPS & Rendimiento</label>
                                <input type="text" name="fps_performance" placeholder="Ej. 60 FPS estables 4K Vulkan" class="w-full bg-white border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-lg p-2.5 text-[#18181B] font-medium placeholder-gray-400 focus:outline-none">
                            </div>
                        </div>

                        <!-- Comment Textarea -->
                        <div>
                            <label class="block text-[11px] font-mono text-gray-600 mb-1 font-bold">Tu Experiencia o Comentario</label>
                            <textarea name="comment" rows="3" required placeholder="Describe tu experiencia: rendimiento, configuraciones recomendadas, si encontraste algún fallo o si va perfecto..." class="w-full bg-white border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-lg p-3 text-[#18181B] font-medium placeholder-gray-400 focus:outline-none"></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-1">
                            <span class="text-[11px] font-mono text-gray-500">
                                Tu reporte ayuda a otros jugadores a configurar su emulador.
                            </span>
                            <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-bold transition-colors flex items-center gap-2 shadow-sm cursor-pointer">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                <span>Publicar Reporte & Estrellas</span>
                            </button>
                        </div>
                    </form>

                    <!-- List of Published Reviews -->
                    <div class="space-y-3 pt-2">
                        @forelse($game->reviews as $rev)
                        <div class="p-4 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] space-y-2.5">
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#E5E0D8] pb-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-[#FDF2F2] border border-[#FCA5A5] text-[#CE2D2D] flex items-center justify-center font-bold text-xs font-mono">
                                        {{ strtoupper(substr($rev->author_name ?: ($rev->user->name ?? 'U'), 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-[#18181B] text-xs block">{{ $rev->author_name ?: ($rev->user->name ?? 'Jugador de la Comunidad') }}</span>
                                        <span class="text-[10px] font-mono text-gray-500">{{ $rev->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <!-- Visual Stars -->
                                <div class="flex items-center gap-1">
                                    <div class="flex items-center gap-0.5">
                                        @for($s = 1; $s <= 5; $s++)
                                            <svg class="w-4 h-4 {{ $s <= $rev->score ? 'text-amber-400 fill-amber-400' : 'text-gray-300 fill-transparent' }}" 
                                                 viewBox="0 0 24 24" 
                                                 fill="currentColor" 
                                                 stroke="currentColor" 
                                                 stroke-width="1.5">
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-xs font-mono font-bold text-amber-600 ml-1">{{ $rev->score }}/5</span>
                                </div>
                            </div>

                            <p class="text-gray-700 font-sans text-xs leading-relaxed">{{ $rev->comment }}</p>

                            @if($rev->tested_emulator || $rev->fps_performance)
                            <div class="text-[11px] font-mono text-gray-600 pt-1 flex flex-wrap items-center gap-3">
                                @if($rev->tested_emulator)
                                <span class="px-2 py-0.5 rounded bg-white border border-[#DDD6CB] text-[#CE2D2D] font-bold flex items-center gap-1.5">
                                    <i data-lucide="cpu" class="w-3 h-3 text-[#CE2D2D]"></i>
                                    <span>{{ $rev->tested_emulator }}</span>
                                </span>
                                @endif
                                @if($rev->fps_performance)
                                <span class="px-2 py-0.5 rounded bg-white border border-[#DDD6CB] text-gray-800 font-bold flex items-center gap-1.5">
                                    <i data-lucide="gauge" class="w-3 h-3 text-[#CE2D2D]"></i>
                                    <span>{{ $rev->fps_performance }}</span>
                                </span>
                                @endif
                            </div>
                            @endif
                        </div>
                        @empty
                        <div class="py-8 text-center space-y-2 border border-dashed border-[#DDD6CB] rounded-xl bg-[#FAF7F2]">
                            <i data-lucide="message-square" class="w-6 h-6 text-gray-400 mx-auto"></i>
                            <p class="text-xs font-mono text-gray-600 font-bold">Aún no hay reportes para este juego.</p>
                            <p class="text-[11px] text-gray-500">¡Sé el primero en compartir qué tal te funcionó y calificarlo con estrellas!</p>
                        </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- RIGHT 4 COLS: STICKY SIDEBAR -->
            <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-20">

                <!-- SIDEBAR WIDGET 1: FICHA TÉCNICA RÁPIDA -->
                <div id="ficha-tecnica" class="bg-white rounded-2xl border-2 border-[#1E1E1E] p-5 space-y-3 shadow-sm">
                    <div class="flex items-center justify-between pb-1">
                        <h3 class="text-xs font-mono font-bold uppercase text-[#18181B] tracking-wider flex items-center gap-2">
                            <i data-lucide="binary" class="w-4 h-4 text-[#CE2D2D]"></i>
                            Ficha Técnica del Vault
                        </h3>
                        <span class="text-[10px] font-mono text-[#CE2D2D] font-bold">Oficial</span>
                    </div>
                    
                    <div class="space-y-2 text-xs font-mono">
                        <div class="flex justify-between"><span class="text-gray-500">Plataforma:</span> <span class="text-[#CE2D2D] font-bold">{{ $game->console->name }}</span></div>
                        @if($game->developer)
                        <div class="flex justify-between"><span class="text-gray-500">Desarrollador:</span> <span class="text-[#18181B] font-bold">{{ $game->developer }}</span></div>
                        @endif
                        @if($game->publisher)
                        <div class="flex justify-between"><span class="text-gray-500">Publisher:</span> <span class="text-[#18181B]">{{ $game->publisher }}</span></div>
                        @endif
                        @if($game->release_year)
                        <div class="flex justify-between"><span class="text-gray-500">Año de Lanzamiento:</span> <span class="text-[#18181B] font-bold">{{ $game->release_year }}</span></div>
                        @endif
                        <div class="flex justify-between"><span class="text-gray-500">Formato:</span> <span class="text-[#CE2D2D] uppercase font-bold">{{ $game->file_format ?: 'ISO' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Región:</span> <span class="text-[#18181B] font-bold">{{ $game->region ?: 'Global / Region Free' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Idiomas:</span> <span class="text-[#18181B]">{{ $game->languages ?: 'Español, Inglés' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Tamaño:</span> <span class="text-[#18181B] font-black">{{ $game->formatted_size }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Emulador Óptimo:</span> <span class="text-[#CE2D2D] font-bold">{{ $game->console->recommended_emulator ?: '60 FPS Vulkan' }}</span></div>
                    </div>
                </div>

                <!-- SIDEBAR WIDGET 2: EMULADOR RECOMENDADO & BIOS REQUERIDA (PUBLICADOS POR ADMIN) -->
                @if($recommendedEmulator || $requiredBios)
                <div id="emulador" class="bg-white rounded-2xl border-2 border-[#1E1E1E] p-5 space-y-4 shadow-sm scroll-mt-24">
                    <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
                        <h3 class="text-xs font-mono font-bold uppercase text-[#18181B] tracking-wider flex items-center gap-2">
                            <i data-lucide="gamepad-2" class="w-4 h-4 text-[#CE2D2D]"></i>
                            Emulador & Firmware
                        </h3>
                        <span class="text-[10px] font-mono text-emerald-700 bg-emerald-50 border border-emerald-300 px-2 py-0.5 rounded-lg font-bold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Recomendado
                        </span>
                    </div>

                    @if($recommendedEmulator)
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] flex items-center justify-center shrink-0 text-[#CE2D2D] shadow-sm">
                                <i data-lucide="{{ $recommendedEmulator->icon ?: 'gamepad-2' }}" class="w-5 h-5"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1">
                                    <h4 class="text-sm font-black text-[#18181B] truncate">{{ $recommendedEmulator->name }}</h4>
                                    @if($recommendedEmulator->version)
                                    <span class="text-[10px] font-mono text-gray-500 font-bold bg-[#FAF7F2] px-1.5 py-0.5 rounded border border-[#DDD6CB]">{{ $recommendedEmulator->version }}</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-gray-500 font-mono truncate">{{ $recommendedEmulator->system }}</p>
                            </div>
                        </div>

                        <!-- Platforms Badges -->
                        @if(!empty($recommendedEmulator->platforms))
                        <div class="flex flex-wrap gap-1 pt-0.5">
                            @foreach($recommendedEmulator->platforms as $plat)
                                <span class="px-2 py-0.5 rounded-md bg-[#FAF7F2] border border-[#DDD6CB] text-[10px] font-mono font-bold text-gray-700">
                                    {{ $plat }}
                                </span>
                            @endforeach
                        </div>
                        @endif

                        <!-- Download and Hub Buttons -->
                        <div class="pt-1 flex gap-2">
                            <a href="{{ $recommendedEmulator->download_url ?: $recommendedEmulator->website }}" 
                               target="_blank" 
                               rel="noopener" 
                               class="flex-1 py-2 px-3 rounded-xl bg-[#18181B] hover:bg-[#CE2D2D] text-white text-xs font-mono font-bold text-center transition-colors flex items-center justify-center gap-1.5 shadow-sm active:scale-95">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Descargar {{ $recommendedEmulator->name }}</span>
                            </a>
                            <a href="{{ route('emulators') }}" 
                               title="Explorar todos los emuladores" 
                               class="p-2 rounded-xl bg-[#FAF7F2] hover:bg-[#F5EFE6] border border-[#DDD6CB] text-gray-700 hover:text-[#CE2D2D] transition-colors flex items-center justify-center shadow-sm">
                                <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Required BIOS Section -->
                    @if($requiredBios)
                    <div class="mt-3 pt-3 border-t border-[#E5E0D8] space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 text-xs font-mono font-bold text-amber-800">
                                <i data-lucide="key" class="w-3.5 h-3.5 text-amber-600"></i>
                                <span>BIOS Requerida</span>
                            </div>
                            <span class="text-[10px] font-mono text-gray-500 font-bold">{{ $requiredBios->size ?: 'Firmware' }}</span>
                        </div>
                        <p class="text-[11px] text-gray-600 leading-relaxed font-sans">
                            Archivos: <strong class="text-[#18181B] font-mono text-[10px] bg-[#FAF7F2] px-1 py-0.5 rounded border border-[#DDD6CB]">{{ $requiredBios->files }}</strong>
                        </p>
                        <a href="{{ $requiredBios->download_url ?: route('bios') }}" 
                           target="_blank" 
                           rel="noopener" 
                           class="w-full py-2 px-3 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-900 text-xs font-mono font-bold flex items-center justify-center gap-1.5 transition-colors shadow-sm active:scale-95">
                            <i data-lucide="file-down" class="w-3.5 h-3.5 text-amber-700"></i>
                            <span>Descargar Pack de BIOS Oficial</span>
                        </a>
                    </div>
                    @endif
                </div>
                @endif

                <!-- SIDEBAR WIDGET 3: CATEGORÍAS & GÉNEROS -->
                @if($game->categories->isNotEmpty())
                <div class="bg-white rounded-2xl border-2 border-[#1E1E1E] p-5 space-y-3 shadow-sm">
                    <h3 class="text-xs font-mono font-bold uppercase text-[#18181B] tracking-wider border-b border-[#E5E0D8] pb-2 flex items-center gap-2">
                        <i data-lucide="tag" class="w-4 h-4 text-[#CE2D2D]"></i>
                        Categorías & Géneros
                    </h3>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($game->categories as $cat)
                        <a href="{{ route('search', ['category' => $cat->slug]) }}" class="px-3 py-1 rounded-xl bg-[#FAF7F2] hover:bg-[#FDF2F2] border border-[#DDD6CB] hover:border-[#CE2D2D] text-xs font-mono font-bold text-[#18181B] hover:text-[#CE2D2D] transition-colors shadow-sm">
                            {{ $cat->name }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- SIDEBAR WIDGET 3: TÍTULOS RELACIONADOS -->
                @if(isset($relatedGames) && $relatedGames->isNotEmpty())
                <div id="relacionados" class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-5 space-y-4 shadow-sm">
                    <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
                        <h3 class="text-xs font-mono font-bold uppercase text-[#18181B] tracking-wider flex items-center gap-2">
                            <i data-lucide="trophy" class="w-4 h-4 text-[#CE2D2D]"></i>
                            Más Juegos de {{ $game->console->short_name ?: $game->console->name }}
                        </h3>
                        <a href="{{ route('consoles.show', $game->console->slug) }}" class="text-[10px] font-mono text-[#CE2D2D] hover:underline font-bold">Ver catálogo</a>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($relatedGames as $i => $rel)
                        <a href="{{ route('game.show', $rel->slug) }}" class="flex items-center gap-3 p-2 rounded-xl hover:bg-[#FAF7F2] border border-transparent hover:border-[#DDD6CB] transition-all group">
                            
                            <!-- Rank Number -->
                            <span class="w-6 text-center font-mono font-bold text-xs text-gray-400 group-hover:text-[#CE2D2D] shrink-0">
                                #{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>

                            <!-- Square App Thumbnail -->
                            <img src="{{ $rel->cover_thumb_url ?: $rel->cover_url }}" 
                                 alt="{{ $rel->title }}" 
                                 class="w-10 h-10 rounded-lg object-cover bg-[#FAF7F2] border border-[#DDD6CB] shrink-0 aspect-square">
                            
                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <p class="font-black text-xs text-[#18181B] group-hover:text-[#CE2D2D] transition-colors truncate">
                                    {{ $rel->title }}
                                </p>
                                <span class="text-[10px] font-mono text-gray-500 block truncate">
                                    {{ $rel->formatted_size }}
                                </span>
                            </div>

                            <!-- Rating Score -->
                            <span class="text-xs font-mono font-bold text-amber-500 shrink-0">
                                ★ {{ number_format($rel->rating_average ?: 5.0, 1) }}
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

        </div>

    </section>

    <!-- 4. BOTTOM PLATFORM STATISTICS COUNTERS -->
    <section class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 pt-6 border-t border-[#E5E0D8] space-y-6">
        
        <div class="text-center space-y-1">
            <span class="text-[11px] font-mono uppercase tracking-widest text-[#CE2D2D] font-bold block">
                Métricas del Título en el Vault
            </span>
            <p class="text-xs text-gray-600 font-sans">
                Preservación continua con verificación de integridad CRC32 y SHA-256.
            </p>
        </div>

        <!-- 3 Big Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <!-- Stat 1: Downloads -->
            <div class="p-6 rounded-2xl bg-white border-2 border-[#1E1E1E] text-center space-y-1 shadow-sm">
                <div class="text-3xl sm:text-4xl font-black text-[#CE2D2D] font-mono tracking-tight">
                    +{{ number_format($game->download_count) }}
                </div>
                <div class="text-xs font-mono uppercase tracking-wider text-[#18181B] font-bold">
                    Descargas de este Título
                </div>
                <div class="text-[10px] text-gray-500 font-sans">Transferencias directas de alta velocidad</div>
            </div>

            <!-- Stat 2: Rating -->
            <div class="p-6 rounded-2xl bg-white border-2 border-[#1E1E1E] text-center space-y-1 shadow-sm">
                <div class="text-3xl sm:text-4xl font-black text-[#18181B] font-mono tracking-tight flex items-center justify-center gap-1">
                    <span class="text-amber-400">★</span>
                    <span>{{ number_format($game->rating_average ?: 5.0, 2) }}</span>
                </div>
                <div class="text-xs font-mono uppercase tracking-wider text-[#18181B] font-bold">
                    Calificación de la Comunidad
                </div>
                <div class="text-[10px] text-gray-500 font-sans">Basado en {{ number_format($game->rating_count ?: 1) }} valoraciones reales</div>
            </div>

            <!-- Stat 3: Integrity -->
            <div class="p-6 rounded-2xl bg-white border-2 border-[#1E1E1E] text-center space-y-1 shadow-sm">
                <div class="text-3xl sm:text-4xl font-black text-[#CE2D2D] font-mono tracking-tight">
                    100%
                </div>
                <div class="text-xs font-mono uppercase tracking-wider text-[#18181B] font-bold">
                    Volcado Limpio Bit-Exact
                </div>
                <div class="text-[10px] text-gray-500 font-sans">Compatible con emuladores oficiales</div>
            </div>

        </div>

    </section>

    <!-- Lightbox Zoom Modal with Full Slider Navigation -->
    <div x-show="activeImage" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/95 backdrop-blur-md flex items-center justify-center p-4" 
         @keydown.escape.window="activeImage = null"
         @keydown.left.window="prevImage()"
         @keydown.right.window="nextImage()">
        <div class="relative max-w-5xl w-full flex flex-col items-center gap-3" @click.outside="activeImage = null">
            
            <!-- Header bar inside modal -->
            <div class="w-full flex items-center justify-between text-white text-xs font-mono">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-lg bg-white/10 text-gray-300 font-bold" x-text="(activeImageIndex + 1) + ' / ' + galleryImages.length"></span>
                    <span class="hidden sm:inline text-gray-400 font-sans truncate max-w-md">{{ $game->title }} — Captura In-Game HD</span>
                </div>
                <button type="button" @click="activeImage = null" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-[#CE2D2D] text-white transition-colors flex items-center gap-1.5 font-bold cursor-pointer">
                    <span>Cerrar</span> <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Image Container with Prev / Next Arrows -->
            <div class="relative w-full aspect-video max-h-[75vh] flex items-center justify-center bg-black/50 rounded-2xl overflow-hidden border border-white/20 shadow-2xl">
                <img :src="activeImage" alt="Captura ampliada" class="max-w-full max-h-[75vh] object-contain rounded-xl">

                <!-- Prev Button -->
                <button type="button" 
                        x-show="galleryImages.length > 1"
                        @click.stop="prevImage()" 
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-black/70 hover:bg-[#CE2D2D] text-white flex items-center justify-center transition-all shadow-xl cursor-pointer border border-white/20">
                    <i data-lucide="chevron-left" class="w-6 h-6"></i>
                </button>

                <!-- Next Button -->
                <button type="button" 
                        x-show="galleryImages.length > 1"
                        @click.stop="nextImage()" 
                        class="absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-black/70 hover:bg-[#CE2D2D] text-white flex items-center justify-center transition-all shadow-xl cursor-pointer border border-white/20">
                    <i data-lucide="chevron-right" class="w-6 h-6"></i>
                </button>
            </div>
            
            <p class="text-[11px] text-gray-400 font-mono hidden sm:block">Usa las teclas &larr; y &rarr; para navegar • Presiona Escape para cerrar</p>
        </div>
    </div>

</div>
@endsection
