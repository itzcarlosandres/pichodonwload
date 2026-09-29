@extends('layouts.admin')

@section('title', 'ROMHUB — Centro de Control Administrativo & Telemetría')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans">Panel de Control & Telemetría</h1>
            <p class="text-xs text-gray-400 font-mono mt-0.5">Estadísticas en tiempo real del vault de emulación, almacenamiento R2 y métricas de descarga</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.games.create') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-wide flex items-center gap-2 transition-all shadow-lg shadow-blue-600/30">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Nuevo Videojuego
            </a>
            <a href="{{ route('admin.settings.index') }}" class="px-3.5 py-2 rounded-xl bg-[#11141A] hover:bg-[#171B22] border border-[#232936] text-gray-300 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="settings" class="w-4 h-4 text-gray-400"></i> Ajustes
            </a>
        </div>
    </div>

    <!-- 4 High-Impact KPI Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Metric 1: Total Titles -->
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-3 relative overflow-hidden group hover:border-blue-500/40 transition-all">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-xs font-mono font-semibold uppercase">Catálogo Activo</span>
                <div class="p-2 rounded-lg bg-blue-600/10 text-blue-400 border border-blue-500/20 group-hover:bg-blue-600 group-hover:text-white transition-all">
                    <i data-lucide="gamepad-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <span class="text-3xl font-black text-white font-mono">{{ $totalGames }}</span>
                <span class="text-[11px] text-gray-400 ml-1.5">ROMs / ISOs</span>
            </div>
            <div class="text-[11px] font-mono text-emerald-400 flex items-center gap-1">
                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> 20 Consolas indexadas
            </div>
        </div>

        <!-- Metric 2: Total Bandwidth / Downloads -->
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-3 relative overflow-hidden group hover:border-emerald-500/40 transition-all">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-xs font-mono font-semibold uppercase">Descargas Globales</span>
                <div class="p-2 rounded-lg bg-emerald-600/10 text-emerald-400 border border-emerald-500/20 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                    <i data-lucide="download-cloud" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <span class="text-3xl font-black text-white font-mono">{{ number_format($totalDownloads) }}</span>
                <span class="text-[11px] text-gray-400 ml-1.5">transferencias</span>
            </div>
            <div class="text-[11px] font-mono text-emerald-400 flex items-center gap-1">
                <i data-lucide="check" class="w-3.5 h-3.5"></i> CDN R2 sin límite
            </div>
        </div>

        <!-- Metric 3: Total Users -->
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-3 relative overflow-hidden group hover:border-purple-500/40 transition-all">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-xs font-mono font-semibold uppercase">Usuarios Registrados</span>
                <div class="p-2 rounded-lg bg-purple-600/10 text-purple-400 border border-purple-500/20 group-hover:bg-purple-600 group-hover:text-white transition-all">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <span class="text-3xl font-black text-white font-mono">{{ $totalUsers }}</span>
                <span class="text-[11px] text-gray-400 ml-1.5">preservadores</span>
            </div>
            <div class="text-[11px] font-mono text-purple-400 flex items-center gap-1">
                <i data-lucide="shield" class="w-3.5 h-3.5"></i> Sistema XP Activo
            </div>
        </div>

        <!-- Metric 4: Moderation Queue -->
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-3 relative overflow-hidden group hover:border-amber-500/40 transition-all">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-xs font-mono font-semibold uppercase">Moderación Pendiente</span>
                <div class="p-2 rounded-lg bg-amber-600/10 text-amber-400 border border-amber-500/20 group-hover:bg-amber-600 group-hover:text-white transition-all">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <span class="text-3xl font-black text-white font-mono">{{ $pendingReviews }}</span>
                <span class="text-[11px] text-gray-400 ml-1.5">reportes</span>
            </div>
            <div class="text-[11px] font-mono {{ $pendingReviews > 0 ? 'text-amber-400 font-bold' : 'text-gray-500' }}">
                {{ $pendingReviews > 0 ? 'Requiere revisión inmediata' : 'Todo moderado' }}
            </div>
        </div>

    </div>

    <!-- Interactive Analytics & Telemetry Charts Section (Chart.js) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Chart 1: Downloads Trend (2 columns) -->
        <div class="lg:col-span-2 bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#232936] pb-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-bold text-white font-sans flex items-center gap-2">
                            <i data-lucide="activity" class="w-4 h-4 text-blue-400"></i> Tendencia de Descargas & Ancho de Banda
                        </h2>
                        <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20 text-[10px] font-mono flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span> {{ (int) $totalDownloads }} descargas acumuladas
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-400 font-mono mt-0.5">Historial en tiempo real de transferencias (Comenzará a trazar el tráfico con las primeras descargas)</p>
                </div>
                <div class="flex items-center gap-2 text-[10px] font-mono">
                    <span class="px-2 py-1 rounded bg-[#0A0C0F] border border-[#232936] text-blue-400 font-semibold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> Descargas
                    </span>
                    <span class="px-2 py-1 rounded bg-[#0A0C0F] border border-[#232936] text-emerald-400 font-semibold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Visitas Únicas
                    </span>
                </div>
            </div>
            <div class="h-64 w-full relative">
                <canvas id="downloadsChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Ecosystem Share Doughnut (1 column) -->
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-4 flex flex-col justify-between">
            <div class="border-b border-[#232936] pb-3">
                <h2 class="text-sm font-bold text-white font-sans flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-4 h-4 text-purple-400"></i> Cuota por Fabricante
                </h2>
                <p class="text-[11px] text-gray-400 font-mono mt-0.5">Distribución de títulos por ecosistema</p>
            </div>
            <div class="h-52 w-full relative flex items-center justify-center">
                <canvas id="ecosystemChart"></canvas>
            </div>
            <div class="grid grid-cols-2 gap-2 text-[11px] font-mono pt-2 border-t border-[#232936]/60">
                <div class="flex items-center gap-1.5 text-gray-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Sony ({{ $storageStats['sony_pct'] }}%)
                </div>
                <div class="flex items-center gap-1.5 text-gray-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Nintendo ({{ $storageStats['nintendo_pct'] }}%)
                </div>
                <div class="flex items-center gap-1.5 text-gray-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Xbox ({{ $storageStats['xbox_pct'] }}%)
                </div>
                <div class="flex items-center gap-1.5 text-gray-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span> Sega ({{ $storageStats['sega_pct'] }}%)
                </div>
            </div>
        </div>

    </div>

    <!-- Storage Telemetry Breakdown by Ecosystem -->
    <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-white font-sans flex items-center gap-2">
                    <i data-lucide="hard-drive" class="w-4 h-4 text-blue-400"></i> Distribución de Almacenamiento en Cloudflare R2
                </h2>
                <p class="text-xs text-gray-400 font-mono mt-0.5">Total estimado en disco: <strong class="text-white">{{ $storageStats['total_gb'] }} GB</strong></p>
            </div>
            <span class="px-3 py-1 rounded bg-[#0A0C0F] border border-[#232936] text-[11px] font-mono text-emerald-400 flex items-center gap-1.5 w-fit">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Almacenamiento S3 / R2 Sincronizado
            </span>
        </div>

        <!-- Segmented Progress Bar -->
        <div class="w-full h-3 rounded-lg bg-[#0A0C0F] border border-[#232936] overflow-hidden flex">
            <div class="bg-blue-600 h-full transition-all" style="width: {{ $storageStats['sony_pct'] }}%" title="Sony: {{ $storageStats['sony_pct'] }}%"></div>
            <div class="bg-rose-600 h-full transition-all" style="width: {{ $storageStats['nintendo_pct'] }}%" title="Nintendo: {{ $storageStats['nintendo_pct'] }}%"></div>
            <div class="bg-emerald-600 h-full transition-all" style="width: {{ $storageStats['xbox_pct'] }}%" title="Microsoft: {{ $storageStats['xbox_pct'] }}%"></div>
            <div class="bg-sky-500 h-full transition-all" style="width: {{ $storageStats['sega_pct'] }}%" title="Sega: {{ $storageStats['sega_pct'] }}%"></div>
        </div>

        <!-- Legend -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-mono pt-1">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-blue-600"></span>
                <span class="text-gray-300">Sony PlayStation ({{ $storageStats['sony_pct'] }}%)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-rose-600"></span>
                <span class="text-gray-300">Nintendo ({{ $storageStats['nintendo_pct'] }}%)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-emerald-600"></span>
                <span class="text-gray-300">Microsoft Xbox ({{ $storageStats['xbox_pct'] }}%)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-sky-500"></span>
                <span class="text-gray-300">Sega ({{ $storageStats['sega_pct'] }}%)</span>
            </div>
        </div>
    </div>

    <!-- Top Most Downloaded & Popular Games Ranking Section -->
    <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-4 shadow-xl"
         x-data="{ rankingTab: 'downloads', limit: 5 }">
        
        <!-- Header with Interactive Filter Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#232936] pb-3">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-white font-sans flex items-center gap-2">
                        <i data-lucide="flame" class="w-4 h-4 text-amber-400"></i>
                        <span>Top Títulos Más Populares & Descargados</span>
                    </h3>
                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-mono flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span> Ranking en Vivo
                    </span>
                </div>
                <p class="text-[11px] text-gray-400 font-mono mt-0.5">Monitoreo de títulos con mayor demanda y tráfico en el catálogo</p>
            </div>

            <!-- Controls: Tabs (Descargas vs Vistas) & Limit Toggle -->
            <div class="flex flex-wrap items-center gap-2 font-mono text-xs">
                
                <!-- Tab Selector -->
                <div class="flex items-center p-1 bg-[#0A0C0F] border border-[#232936] rounded-xl">
                    <button type="button" 
                            @click="rankingTab = 'downloads'" 
                            :class="rankingTab === 'downloads' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-gray-400 hover:text-white'"
                            class="px-3 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Más Descargados</span>
                    </button>
                    <button type="button" 
                            @click="rankingTab = 'views'" 
                            :class="rankingTab === 'views' ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30' : 'text-gray-400 hover:text-white'"
                            class="px-3 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        <span>Más Visitados</span>
                    </button>
                </div>

                <!-- Limit Toggle: Top 5 vs Top 10 -->
                <button type="button" 
                        @click="limit = (limit === 5 ? 10 : 5)" 
                        class="px-3 py-1.5 rounded-xl bg-[#171B22] hover:bg-[#232936] border border-[#232936] text-gray-300 hover:text-white transition-colors cursor-pointer text-xs flex items-center gap-1.5"
                        :title="limit === 5 ? 'Expandir a Top 10' : 'Reducir a Top 5'">
                    <i data-lucide="layers" class="w-3.5 h-3.5 text-blue-400"></i>
                    <span x-text="limit === 5 ? 'Top 5' : 'Top 10'"></span>
                </button>

                <a href="{{ route('admin.games.index') }}" class="p-1.5 rounded-xl bg-[#171B22] hover:bg-blue-600 text-gray-300 hover:text-white border border-[#232936] transition-colors" title="Ver catálogo completo de juegos">
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>

        @php
            $maxDownloads = $topGames->max('download_count') ?: 1;
            $maxViews = $topViewedGames->max('views_count') ?: 1;
        @endphp

        <!-- TAB 1: MÁS DESCARGADOS -->
        <div x-show="rankingTab === 'downloads'" class="divide-y divide-[#232936]/60">
            @forelse($topGames as $idx => $game)
                @php
                    $pct = round(($game->download_count / $maxDownloads) * 100);
                    $convRate = $game->views_count > 0 ? round(($game->download_count / $game->views_count) * 100, 1) : 0;
                @endphp
                <div x-show="{{ $idx }} < limit" 
                     class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 first:pt-0 last:pb-0 hover:bg-[#171B22]/40 rounded-xl px-2.5 transition-colors group">
                    
                    <!-- Left: Podio + Cover + Title & Metadata -->
                    <div class="flex items-center gap-3.5 min-w-0">
                        
                        <!-- Ranking Podio Medal -->
                        <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center font-mono font-black text-xs shadow-sm
                            {{ $idx === 0 ? 'bg-gradient-to-tr from-amber-500 to-yellow-400 text-black shadow-amber-500/25 ring-2 ring-amber-400/40' : 
                              ($idx === 1 ? 'bg-gradient-to-tr from-slate-300 to-gray-200 text-black shadow-slate-400/20 ring-1 ring-slate-300/40' : 
                              ($idx === 2 ? 'bg-gradient-to-tr from-amber-700 to-amber-600 text-white shadow-amber-700/20' : 
                              'bg-[#0A0C0F] text-gray-400 border border-[#232936]')) }}">
                            @if($idx === 0)
                                <span class="flex items-center gap-0.5">👑 1</span>
                            @elseif($idx === 1)
                                <span>🥈 2</span>
                            @elseif($idx === 2)
                                <span>🥉 3</span>
                            @else
                                <span>#{{ $idx + 1 }}</span>
                            @endif
                        </div>

                        <!-- Game Artwork Thumbnail -->
                        <div class="relative shrink-0">
                            <img src="{{ $game->cover_thumb_url ?: ($game->cover_url ?: asset('images/placeholder-cover.svg')) }}" 
                                 alt="{{ $game->title }}" 
                                 onerror="this.onerror=null; this.src='{{ asset('images/placeholder-cover.svg') }}';"
                                 class="w-11 h-14 object-cover rounded-lg bg-[#0A0C0F] border border-[#232936] shadow-sm group-hover:scale-105 transition-transform duration-300">
                        </div>

                        <!-- Title, Console Pill & Specs -->
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('game.show', $game->slug) }}" 
                                   target="_blank" 
                                   class="text-xs font-bold text-white truncate font-sans hover:text-blue-400 transition-colors"
                                   title="Ver en la web pública: {{ $game->title }}">
                                    {{ $game->title }}
                                </a>
                                @if($game->status === 'PUBLISHED')
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0" title="Publicado"></span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0" title="Borrador"></span>
                                @endif
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2 mt-1 text-[10px] font-mono text-gray-400">
                                <span class="px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-400 border border-blue-500/20 font-semibold font-mono">
                                    {{ $game->console->short_name ?: ($game->console->name ?? 'ROM') }}
                                </span>
                                <span>•</span>
                                <span>{{ $game->formatted_size }}</span>
                                <span>•</span>
                                <span class="text-amber-400 font-bold flex items-center gap-0.5">
                                    ★ {{ number_format($game->rating_average ?: 5.0, 1) }}
                                </span>
                                <span>•</span>
                                <span class="text-gray-400 font-mono">
                                    👁️ {{ number_format($game->views_count) }} vistas
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Demand Bar + Stats + Action Buttons -->
                    <div class="flex items-center gap-4 sm:w-80 shrink-0 justify-between sm:justify-end">
                        
                        <!-- Demand Progress Bar against #1 -->
                        <div class="w-32 hidden md:block">
                            <div class="flex justify-between text-[10px] font-mono text-gray-400 mb-1">
                                <span>Demanda</span>
                                <span class="text-blue-400 font-bold">{{ $pct }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-[#0A0C0F] rounded-full overflow-hidden border border-[#232936]">
                                <div class="bg-gradient-to-r from-blue-500 to-emerald-400 h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        <!-- Numerical Stats -->
                        <div class="text-right min-w-[70px]">
                            <span class="text-sm font-black text-white font-mono flex items-center justify-end gap-1">
                                <i data-lucide="download" class="w-3.5 h-3.5 text-blue-400"></i>
                                {{ number_format($game->download_count) }}
                            </span>
                            <span class="block text-[9px] font-mono text-gray-400 uppercase">
                                {{ $convRate }}% conv.
                            </span>
                        </div>

                        <!-- Quick Actions: Public View & Admin Edit -->
                        <div class="flex items-center gap-1 shrink-0">
                            <a href="{{ route('game.show', $game->slug) }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="p-2 rounded-xl bg-[#0A0C0F] hover:bg-blue-600 hover:text-white text-gray-400 border border-[#232936] transition-all" 
                               title="Abrir ficha pública en nueva pestaña">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>

                            <a href="{{ route('admin.games.edit', $game->id) }}" 
                               class="p-2 rounded-xl bg-[#0A0C0F] hover:bg-emerald-600 hover:text-white text-gray-400 border border-[#232936] transition-all" 
                               title="Editar videojuego en administración">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>

                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-gray-500 font-mono space-y-2">
                    <i data-lucide="inbox" class="w-8 h-8 mx-auto text-gray-600"></i>
                    <p>No hay títulos registrados con descargas aún.</p>
                </div>
            @endforelse
        </div>

        <!-- TAB 2: MÁS VISITADOS / POPULARES -->
        <div x-show="rankingTab === 'views'" class="divide-y divide-[#232936]/60">
            @forelse($topViewedGames as $idx => $game)
                @php
                    $pct = round(($game->views_count / $maxViews) * 100);
                @endphp
                <div x-show="{{ $idx }} < limit" 
                     class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 first:pt-0 last:pb-0 hover:bg-[#171B22]/40 rounded-xl px-2.5 transition-colors group">
                    
                    <!-- Left: Podio + Cover + Title & Metadata -->
                    <div class="flex items-center gap-3.5 min-w-0">
                        
                        <!-- Ranking Podio Medal -->
                        <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center font-mono font-black text-xs shadow-sm
                            {{ $idx === 0 ? 'bg-gradient-to-tr from-purple-500 to-indigo-400 text-white shadow-purple-500/25 ring-2 ring-purple-400/40' : 
                              ($idx === 1 ? 'bg-gradient-to-tr from-slate-300 to-gray-200 text-black shadow-slate-400/20' : 
                              ($idx === 2 ? 'bg-gradient-to-tr from-amber-700 to-amber-600 text-white shadow-amber-700/20' : 
                              'bg-[#0A0C0F] text-gray-400 border border-[#232936]')) }}">
                            @if($idx === 0)
                                <span class="flex items-center gap-0.5">👁️ 1</span>
                            @elseif($idx === 1)
                                <span>🥈 2</span>
                            @elseif($idx === 2)
                                <span>🥉 3</span>
                            @else
                                <span>#{{ $idx + 1 }}</span>
                            @endif
                        </div>

                        <!-- Game Artwork Thumbnail -->
                        <div class="relative shrink-0">
                            <img src="{{ $game->cover_thumb_url ?: ($game->cover_url ?: asset('images/placeholder-cover.svg')) }}" 
                                 alt="{{ $game->title }}" 
                                 onerror="this.onerror=null; this.src='{{ asset('images/placeholder-cover.svg') }}';"
                                 class="w-11 h-14 object-cover rounded-lg bg-[#0A0C0F] border border-[#232936] shadow-sm group-hover:scale-105 transition-transform duration-300">
                        </div>

                        <!-- Title, Console Pill & Specs -->
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('game.show', $game->slug) }}" 
                                   target="_blank" 
                                   class="text-xs font-bold text-white truncate font-sans hover:text-purple-400 transition-colors"
                                   title="Ver en la web pública: {{ $game->title }}">
                                    {{ $game->title }}
                                </a>
                                @if($game->status === 'PUBLISHED')
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0" title="Publicado"></span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0" title="Borrador"></span>
                                @endif
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2 mt-1 text-[10px] font-mono text-gray-400">
                                <span class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-400 border border-purple-500/20 font-semibold font-mono">
                                    {{ $game->console->short_name ?: ($game->console->name ?? 'ROM') }}
                                </span>
                                <span>•</span>
                                <span>{{ $game->formatted_size }}</span>
                                <span>•</span>
                                <span class="text-amber-400 font-bold flex items-center gap-0.5">
                                    ★ {{ number_format($game->rating_average ?: 5.0, 1) }}
                                </span>
                                <span>•</span>
                                <span class="text-emerald-400 font-mono">
                                    📥 {{ number_format($game->download_count) }} descargas
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Traffic Bar + Stats + Action Buttons -->
                    <div class="flex items-center gap-4 sm:w-80 shrink-0 justify-between sm:justify-end">
                        
                        <!-- Traffic Progress Bar against #1 -->
                        <div class="w-32 hidden md:block">
                            <div class="flex justify-between text-[10px] font-mono text-gray-400 mb-1">
                                <span>Popularidad</span>
                                <span class="text-purple-400 font-bold">{{ $pct }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-[#0A0C0F] rounded-full overflow-hidden border border-[#232936]">
                                <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        <!-- Numerical Stats -->
                        <div class="text-right min-w-[70px]">
                            <span class="text-sm font-black text-white font-mono flex items-center justify-end gap-1">
                                <i data-lucide="eye" class="w-3.5 h-3.5 text-purple-400"></i>
                                {{ number_format($game->views_count) }}
                            </span>
                            <span class="block text-[9px] font-mono text-gray-400 uppercase">
                                Visitas
                            </span>
                        </div>

                        <!-- Quick Actions: Public View & Admin Edit -->
                        <div class="flex items-center gap-1 shrink-0">
                            <a href="{{ route('game.show', $game->slug) }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="p-2 rounded-xl bg-[#0A0C0F] hover:bg-purple-600 hover:text-white text-gray-400 border border-[#232936] transition-all" 
                               title="Abrir ficha pública en nueva pestaña">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>

                            <a href="{{ route('admin.games.edit', $game->id) }}" 
                               class="p-2 rounded-xl bg-[#0A0C0F] hover:bg-emerald-600 hover:text-white text-gray-400 border border-[#232936] transition-all" 
                               title="Editar videojuego en administración">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>

                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-gray-500 font-mono space-y-2">
                    <i data-lucide="inbox" class="w-8 h-8 mx-auto text-gray-600"></i>
                    <p>No hay títulos registrados con visitas aún.</p>
                </div>
            @endforelse
        </div>

    </div>

    <!-- Recent Uploads and Moderation Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Games Added -->
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                <h3 class="text-sm font-bold text-white font-sans flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 text-blue-400"></i> Últimos Títulos Agregados
                </h3>
                <a href="{{ route('admin.games.index') }}" class="text-xs text-blue-400 hover:text-blue-300 font-mono">Ver Catálogo &rarr;</a>
            </div>

            <div class="divide-y divide-[#232936]/60">
                @foreach($recentGames as $game)
                <div class="py-3 flex items-center justify-between gap-3 first:pt-0 last:pb-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="{{ $game->cover_thumb_url ?: ($game->cover_url ?: asset('images/placeholder-cover.svg')) }}" onerror="this.onerror=null; this.src='{{ asset('images/placeholder-cover.svg') }}';" class="w-9 h-12 object-cover rounded bg-gray-900 border border-[#232936] shrink-0">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-white truncate font-sans">{{ $game->title }}</p>
                            <p class="text-[10px] font-mono text-gray-400">{{ $game->console->name }} • {{ $game->formatted_size }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <a href="{{ route('admin.games.edit', $game->id) }}" class="p-1.5 rounded-lg bg-[#171B22] hover:bg-[#232936] text-gray-300 transition-colors" title="Editar">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Reviews & Feedback -->
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                <h3 class="text-sm font-bold text-white font-sans flex items-center gap-2">
                    <i data-lucide="cpu" class="w-4 h-4 text-emerald-400"></i> Reportes de Emulación
                </h3>
                <a href="{{ route('admin.reviews.index') }}" class="text-xs text-blue-400 hover:text-blue-300 font-mono">Moderar &rarr;</a>
            </div>

            <div class="divide-y divide-[#232936]/60">
                @forelse($recentReviews as $rev)
                <div class="py-3 space-y-1 first:pt-0 last:pb-0">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-white truncate">{{ $rev->game->title ?? 'Juego' }}</span>
                        <span class="text-amber-400 font-mono font-bold">★ {{ $rev->score }}/5</span>
                    </div>
                    <p class="text-[11px] text-gray-400 line-clamp-1 font-sans">{{ $rev->comment }}</p>
                    <div class="text-[10px] font-mono text-gray-500 flex items-center gap-2">
                        <span>Por: {{ $rev->user->name ?? 'Usuario' }}</span>
                        <span>•</span>
                        <span>{{ $rev->tested_emulator ?: 'Emulador Genérico' }}</span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-500 font-mono py-4 text-center">No hay reportes recientes.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Chart 1: Downloads Trend Line/Bar Chart
        const downloadsCtx = document.getElementById('downloadsChart');
        if (downloadsCtx) {
            const ctx = downloadsCtx.getContext('2d');
            const gradientBlue = ctx.createLinearGradient(0, 0, 0, 250);
            gradientBlue.addColorStop(0, 'rgba(59, 130, 246, 0.35)');
            gradientBlue.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

            const gradientEmerald = ctx.createLinearGradient(0, 0, 0, 250);
            gradientEmerald.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
            gradientEmerald.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

            new Chart(downloadsCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($downloadTrend['labels'] ?? ['Hace 6d', 'Hace 5d', 'Hace 4d', 'Hace 3d', 'Hace 2d', 'Ayer', 'Hoy']) !!},
                    datasets: [
                        {
                            label: 'Descargas',
                            data: {!! json_encode($downloadTrend['downloads'] ?? [0, 0, 0, 0, 0, 0, (int) $totalDownloads]) !!},
                            borderColor: '#3B82F6',
                            backgroundColor: gradientBlue,
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#3B82F6',
                            pointBorderColor: '#0A0C0F',
                            pointHoverRadius: 6,
                        },
                        {
                            label: 'Visitas Únicas',
                            data: {!! json_encode($downloadTrend['views'] ?? [0, 0, 0, 0, 0, 0, 0]) !!},
                            borderColor: '#10B981',
                            backgroundColor: gradientEmerald,
                            borderWidth: 2,
                            borderDash: [4, 4],
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#10B981',
                            pointBorderColor: '#0A0C0F',
                            pointHoverRadius: 5,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#171B22',
                            borderColor: '#232936',
                            borderWidth: 1,
                            titleColor: '#FFFFFF',
                            bodyColor: '#9CA3AF',
                            padding: 10,
                            boxPadding: 4,
                            usePointStyle: true,
                            bodyFont: { family: 'JetBrains Mono', size: 11 },
                            titleFont: { family: 'Plus Jakarta Sans', weight: 'bold', size: 12 }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(35, 41, 54, 0.6)', drawBorder: false },
                            ticks: { color: '#6B7280', font: { family: 'JetBrains Mono', size: 10 } }
                        },
                        y: {
                            beginAtZero: true,
                            min: 0,
                            suggestedMax: 10,
                            grid: { color: 'rgba(35, 41, 54, 0.6)', drawBorder: false },
                            ticks: { 
                                color: '#6B7280', 
                                font: { family: 'JetBrains Mono', size: 10 },
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        // Chart 2: Ecosystem Doughnut Chart
        const ecosystemCtx = document.getElementById('ecosystemChart');
        if (ecosystemCtx) {
            new Chart(ecosystemCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Sony', 'Nintendo', 'Microsoft', 'Sega'],
                    datasets: [{
                        data: [
                            {{ $storageStats['sony_pct'] ?: 45 }},
                            {{ $storageStats['nintendo_pct'] ?: 35 }},
                            {{ $storageStats['xbox_pct'] ?: 12 }},
                            {{ $storageStats['sega_pct'] ?: 8 }}
                        ],
                        backgroundColor: [
                            '#3B82F6', // Sony Blue
                            '#EF4444', // Nintendo Red
                            '#10B981', // Xbox Emerald
                            '#38BDF8'  // Sega Sky
                        ],
                        borderColor: '#11141A',
                        borderWidth: 3,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#171B22',
                            borderColor: '#232936',
                            borderWidth: 1,
                            titleColor: '#FFFFFF',
                            bodyColor: '#9CA3AF',
                            padding: 10,
                            bodyFont: { family: 'JetBrains Mono', size: 11 },
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.label + ': ' + context.parsed + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
