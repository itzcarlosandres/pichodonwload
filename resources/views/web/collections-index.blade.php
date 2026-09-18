@extends('layouts.web')

@section('title', 'Sagas y Colecciones Legendarias de Videojuegos Retro — ' . \App\Models\Setting::get('site_name', 'ROMHUB'))
@section('meta_description', 'Descarga colecciones completas de sagas míticas: Pokémon, The Legend of Zelda, Super Mario, GTA, Resident Evil, Dragon Ball Z y más.')

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-8 space-y-8">

    <!-- Breadcrumbs -->
    <nav class="flex items-center gap-2 text-xs font-mono text-gray-500">
        <a href="{{ route('home') }}" class="hover:text-[#CE2D2D] transition-colors font-medium">INICIO</a>
        <span class="text-gray-400">/</span>
        <span class="text-[#CE2D2D] font-bold uppercase">SAGAS & COLECCIONES</span>
    </nav>

    <!-- Header Section -->
    <section class="space-y-4">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#CE2D2D] text-white text-xs font-mono font-bold shadow-sm">
            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
            <span>Franchises Hub • Catálogos Temáticos Multi-Plataforma</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-[#E5E0D8] pb-6">
            <div class="space-y-2 max-w-2xl">
                <h1 class="text-3xl sm:text-5xl font-black text-[#18181B] tracking-tight font-sans">
                    Sagas & Franquicias Míticas
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 font-sans leading-relaxed">
                    Accede a las cronologías y colecciones completas de las franquicias más queridas de la historia. Todos los títulos organizados desde sus orígenes en 8-bits hasta las consolas modernas.
                </p>
            </div>

            <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-white border-2 border-[#1E1E1E] font-mono shrink-0 shadow-sm">
                <div class="text-center px-2">
                    <span class="text-xl sm:text-2xl font-black text-[#CE2D2D] block">{{ count($franchises) }}</span>
                    <span class="text-[10px] text-gray-500 uppercase tracking-wider font-bold">Grandes Sagas</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Franchises Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($franchises as $fr)
            <a href="{{ route('collections.show', $fr->slug) }}" 
               class="bg-white border-2 border-[#1E1E1E] rounded-2xl overflow-hidden flex flex-col justify-between hover:-translate-y-1 hover:shadow-lg transition-all duration-200 group block">
                
                <!-- Franchise Cover Image / Banner -->
                <div class="h-44 bg-[#FAF7F2] relative overflow-hidden border-b border-[#E5E0D8]">
                    <img src="{{ $fr->image ?: 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=700&fit=crop' }}" 
                         alt="{{ $fr->name }}" 
                         loading="lazy"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex items-end p-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white shadow-md"
                                 style="background-color: {{ $fr->color ?: '#CE2D2D' }};">
                                <i data-lucide="{{ $fr->icon ?: 'sparkles' }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-black text-white font-sans drop-shadow-sm leading-tight">
                                    {{ $fr->name }}
                                </h2>
                                <span class="text-[10px] font-mono text-gray-300 block drop-shadow-sm">Ver Colección →</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Body Info -->
                <div class="p-4 space-y-3 flex-1 flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-bold text-[#CE2D2D] font-mono">
                            {{ $fr->subtitle }}
                        </p>
                        <p class="text-xs text-gray-600 font-sans mt-1.5 line-clamp-2 leading-relaxed">
                            {{ $fr->description }}
                        </p>
                    </div>

                    <div class="pt-3 border-t border-[#E5E0D8] flex items-center justify-between text-xs font-mono">
                        <span class="text-gray-500 font-medium flex items-center gap-1">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                            Multi-Consola
                        </span>
                        <span class="text-[#CE2D2D] font-bold group-hover:underline flex items-center gap-1">
                            <span>Explorar títulos</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </span>
                    </div>
                </div>

            </a>
        @endforeach
    </div>

</main>
@endsection
