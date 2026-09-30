@extends('layouts.admin')

@section('title', 'Demo Extractor Romspedia — Administración ROMHUB')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto" x-data="scraperDemoApp()">

    <!-- Top Breadcrumb & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#232936] pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-gray-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-300">Admin</a>
                <span>/</span>
                <span class="text-purple-400 font-semibold">Laboratorio</span>
                <span>/</span>
                <span class="text-gray-300">Extractor Multi-Proveedor</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight font-heading flex flex-wrap items-center gap-2.5">
                <span class="p-2 rounded-xl transition-all"
                      :class="activeProvider === 'romspedia' ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30' : (activeProvider === 'romsemu' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30')">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                </span>
                <span>Extractor Multi-Proveedor</span>
                <span class="text-xs px-2.5 py-1 rounded-full font-mono uppercase tracking-wider font-bold transition-all"
                      :class="activeProvider === 'romspedia' ? 'bg-purple-500/15 text-purple-300 border border-purple-500/30' : (activeProvider === 'romsemu' ? 'bg-red-500/15 text-red-300 border border-red-500/30' : 'bg-blue-500/15 text-blue-300 border border-blue-500/30')"
                      x-text="activeProvider === 'romspedia' ? 'Modo: Romspedia.com' : (activeProvider === 'romsemu' ? 'Modo: Romsemu.com' : 'Modo: CDRomance.org')">
                </span>
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <!-- Mode Navigation Tabs -->
            <div class="flex items-center gap-1.5 p-1 bg-[#11141A] border border-[#232936] rounded-xl shadow-md">
                <a href="{{ route('admin.scraper.catalog') }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-mono font-bold text-gray-400 hover:text-white hover:bg-[#171B22] flex items-center gap-1.5 transition-colors">
                    <i data-lucide="layers" class="w-3.5 h-3.5 text-purple-400"></i>
                    <span>Catálogo 1-Clic</span>
                </a>
                <a href="{{ route('admin.scraper.demo') }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-mono font-bold bg-purple-600 text-white shadow-md shadow-purple-600/30 flex items-center gap-1.5">
                    <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                    <span>Extractor por URL</span>
                </a>
            </div>
            <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-mono hidden sm:flex items-center gap-1.5 shadow-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Online
            </span>
        </div>
    </div>

    <!-- Provider Selector Cards (Click to Switch) -->
    <div>
        <label class="block text-xs font-mono uppercase tracking-wider text-gray-400 font-bold mb-2 flex items-center gap-1.5">
            <i data-lucide="layers" class="w-3.5 h-3.5 text-purple-400"></i>
            <span>Selecciona el Proveedor Web:</span>
        </label>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Romspedia Provider Card -->
            <button type="button" 
                    @click="setProvider('romspedia')" 
                    class="p-4 rounded-2xl border text-left transition-all cursor-pointer relative overflow-hidden group shadow-md"
                    :class="activeProvider === 'romspedia' 
                        ? 'bg-purple-950/30 border-purple-500/60 shadow-lg shadow-purple-900/20 ring-2 ring-purple-500/50' 
                        : 'bg-[#11141A] border-[#232936] hover:border-gray-600 opacity-60 hover:opacity-100'">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 rounded-xl transition-all"
                              :class="activeProvider === 'romspedia' ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30' : 'bg-[#171B22] text-gray-400 group-hover:text-purple-300'">
                            <i data-lucide="gamepad" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <div class="text-sm font-bold text-white flex items-center gap-2">
                                Romspedia.com
                                <span class="text-[9px] px-1.5 py-0.5 rounded font-mono font-bold"
                                      :class="activeProvider === 'romspedia' ? 'bg-purple-500/30 text-purple-200 border border-purple-400/40' : 'bg-[#171B22] text-gray-500'">
                                    Originales
                                </span>
                            </div>
                            <div class="text-[11px] text-gray-400 font-mono">Descargas directas en .ZIP</div>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-all"
                         :class="activeProvider === 'romspedia' ? 'border-purple-400 bg-purple-600 text-white' : 'border-gray-600 bg-transparent'">
                        <i data-lucide="check" class="w-3 h-3" x-show="activeProvider === 'romspedia'"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-400 leading-relaxed font-sans mt-2">
                    Extracción de títulos oficiales, portadas HD WebP y enlaces de descarga directa sin caducidad en formato <span class="text-purple-300 font-mono font-bold">.ZIP</span>.
                </p>
            </button>

            <!-- CDRomance Provider Card -->
            <button type="button" 
                    @click="setProvider('cdromance')" 
                    class="p-4 rounded-2xl border text-left transition-all cursor-pointer relative overflow-hidden group shadow-md"
                    :class="activeProvider === 'cdromance' 
                        ? 'bg-blue-950/30 border-blue-500/60 shadow-lg shadow-blue-900/20 ring-2 ring-blue-500/50' 
                        : 'bg-[#11141A] border-[#232936] hover:border-gray-600 opacity-60 hover:opacity-100'">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 rounded-xl transition-all"
                              :class="activeProvider === 'cdromance' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'bg-[#171B22] text-gray-400 group-hover:text-blue-300'">
                            <i data-lucide="disc" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <div class="text-sm font-bold text-white flex items-center gap-2">
                                CDRomance.org
                                <span class="text-[9px] px-1.5 py-0.5 rounded font-mono font-bold"
                                      :class="activeProvider === 'cdromance' ? 'bg-blue-500/30 text-blue-200 border border-blue-400/40' : 'bg-[#171B22] text-gray-500'">
                                    Español / ISOs
                                </span>
                            </div>
                            <div class="text-[11px] text-gray-400 font-mono">Descargas directas en .7Z / .ISO</div>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-all"
                         :class="activeProvider === 'cdromance' ? 'border-blue-400 bg-blue-600 text-white' : 'border-gray-600 bg-transparent'">
                        <i data-lucide="check" class="w-3 h-3" x-show="activeProvider === 'cdromance'"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-400 leading-relaxed font-sans mt-2">
                    Extracción de juegos traducidos al español, mods comunitarios y enlaces directos de servidores CDN en formato <span class="text-blue-300 font-mono font-bold">.7Z</span>.
                </p>
            </button>

            <!-- Romsemu Provider Card -->
            <button type="button" 
                    @click="setProvider('romsemu')" 
                    class="p-4 rounded-2xl border text-left transition-all cursor-pointer relative overflow-hidden group shadow-md"
                    :class="activeProvider === 'romsemu' 
                        ? 'bg-red-950/30 border-red-500/60 shadow-lg shadow-red-900/20 ring-2 ring-red-500/50' 
                        : 'bg-[#11141A] border-[#232936] hover:border-gray-600 opacity-60 hover:opacity-100'">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 rounded-xl transition-all"
                              :class="activeProvider === 'romsemu' ? 'bg-red-600 text-white shadow-md shadow-red-600/30' : 'bg-[#171B22] text-gray-400 group-hover:text-red-300'">
                            <i data-lucide="cpu" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <div class="text-sm font-bold text-white flex items-center gap-2">
                                Romsemu.com
                                <span class="text-[9px] px-1.5 py-0.5 rounded font-mono font-bold"
                                      :class="activeProvider === 'romsemu' ? 'bg-red-500/30 text-red-200 border border-red-400/40' : 'bg-[#171B22] text-gray-500'">
                                    Switch / 3DS
                                </span>
                            </div>
                            <div class="text-[11px] text-gray-400 font-mono">Descargas en .XCI / .NSP</div>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-all"
                         :class="activeProvider === 'romsemu' ? 'border-red-400 bg-red-600 text-white' : 'border-gray-600 bg-transparent'">
                        <i data-lucide="check" class="w-3 h-3" x-show="activeProvider === 'romsemu'"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-400 leading-relaxed font-sans mt-2">
                    Extracción de juegos modernos de Nintendo Switch y 3DS, actualizaciones (.NSP) y servidores 1Fichier en formato <span class="text-red-300 font-mono font-bold">.XCI</span>.
                </p>
            </button>
        </div>
    </div>

    <!-- Main Input Card -->
    <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 shadow-xl space-y-4 transition-colors"
         :class="activeProvider === 'romspedia' ? 'focus-within:border-purple-500/60' : 'focus-within:border-blue-500/60'">
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-xs font-mono uppercase tracking-wider text-gray-300 font-bold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full" :class="activeProvider === 'romspedia' ? 'bg-purple-400' : 'bg-blue-400'"></span>
                    <span x-text="activeProvider === 'romspedia' ? 'URL de Romspedia.com' : 'URL de CDRomance.org'"></span>
                </label>
                <span class="text-[11px] font-mono text-gray-500" x-text="activeProvider === 'romspedia' ? 'Soporta descargas directas ZIP' : 'Soporta descargas 7Z & Fan-Translations'"></span>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-500">
                        <i data-lucide="link-2" class="w-4 h-4"></i>
                    </div>
                    <input type="url" 
                           x-model="url" 
                           @input="detectProviderFromUrl()"
                           @paste="$nextTick(() => detectProviderFromUrl())"
                           @keydown.enter.prevent="extractData()"
                           :placeholder="activeProvider === 'romspedia' ? 'https://www.romspedia.com/roms/playstation-portable/god-of-war-ghost-of-sparta-original' : 'https://cdromance.org/psp/atv-offroad-fury-blazin-trails/'" 
                           class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl pl-10 pr-4 py-3 text-sm text-white focus:outline-none font-mono transition-colors"
                           :class="activeProvider === 'romspedia' ? 'focus:border-purple-500' : 'focus:border-blue-500'">
                </div>
                <button type="button" 
                        @click="extractData()" 
                        :disabled="loading || !url"
                        class="px-6 py-3 rounded-xl text-white font-bold text-sm transition-all flex items-center justify-center gap-2 shadow-lg cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed shrink-0"
                        :class="activeProvider === 'romspedia' ? 'bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 shadow-purple-600/20' : 'bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 shadow-blue-600/20'">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="loading"></i>
                    <i data-lucide="zap" class="w-4 h-4" x-show="!loading"></i>
                    <span x-text="loading ? 'Extrayendo Datos...' : 'Extraer Información'"></span>
                </button>
            </div>
        </div>

        <!-- Quick Demo Links for Romspedia -->
        <div x-show="activeProvider === 'romspedia'" class="pt-1 flex flex-wrap items-center gap-2">
            <span class="text-xs font-mono text-gray-500">Ejemplos Romspedia:</span>
            <button type="button" 
                    @click="setUrl('https://www.romspedia.com/roms/playstation-portable/god-of-war-ghost-of-sparta-original')"
                    class="px-2.5 py-1 rounded-lg bg-[#171B22] hover:bg-[#232936] border border-[#232936] text-[11px] font-mono text-purple-300 hover:text-white transition-colors cursor-pointer">
                ⚔️ God of War Ghost of Sparta (PSP)
            </button>
            <button type="button" 
                    @click="setUrl('https://www.romspedia.com/roms/playstation-portable/grand-theft-auto-vice-city-stories')"
                    class="px-2.5 py-1 rounded-lg bg-[#171B22] hover:bg-[#232936] border border-[#232936] text-[11px] font-mono text-purple-300 hover:text-white transition-colors cursor-pointer">
                🌴 GTA Vice City Stories (PSP)
            </button>
            <button type="button" 
                    @click="setUrl('https://www.romspedia.com/roms/playstation-portable/spider-man-3')"
                    class="px-2.5 py-1 rounded-lg bg-[#171B22] hover:bg-[#232936] border border-[#232936] text-[11px] font-mono text-purple-300 hover:text-white transition-colors cursor-pointer">
                🕷️ Spider-Man 3 (PSP)
            </button>
        </div>

        <!-- Quick Demo Links for CDRomance -->
        <div x-show="activeProvider === 'cdromance'" class="pt-1 flex flex-wrap items-center gap-2">
            <span class="text-xs font-mono text-gray-500">Ejemplos CDRomance:</span>
            <button type="button" 
                    @click="setUrl('https://cdromance.org/psp/atv-offroad-fury-blazin-trails/')"
                    class="px-2.5 py-1 rounded-lg bg-[#171B22] hover:bg-[#232936] border border-[#232936] text-[11px] font-mono text-blue-300 hover:text-white transition-colors cursor-pointer">
                🏍️ ATV Offroad Fury (PSP)
            </button>
            <button type="button" 
                    @click="setUrl('https://cdromance.org/psp/tales-of-world-radiant-mythology-2-english-patched/')"
                    class="px-2.5 py-1 rounded-lg bg-[#171B22] hover:bg-[#232936] border border-[#232936] text-[11px] font-mono text-blue-300 hover:text-white transition-colors cursor-pointer">
                🗡️ Tales of World 2 [Fan-Traducción] (PSP)
            </button>
            <button type="button" 
                    @click="setUrl('https://cdromance.org/psp/sonic-r-port-psp/')"
                    class="px-2.5 py-1 rounded-lg bg-[#171B22] hover:bg-[#232936] border border-[#232936] text-[11px] font-mono text-blue-300 hover:text-white transition-colors cursor-pointer">
                🦔 Sonic R Port (PSP)
            </button>
        </div>

        <!-- Error Feedback -->
        <div x-show="errorMessage" x-cloak class="p-4 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-400 shrink-0"></i>
            <span x-text="errorMessage"></span>
        </div>
    </div>

    <!-- Live Extracted Result Presentation Card -->
    <div x-show="result" x-cloak class="space-y-6">
        
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 shadow-2xl relative overflow-hidden">
            <!-- Background Glow -->
            <div class="absolute -top-24 -right-24 w-80 h-80 bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col md:flex-row gap-6 items-start relative z-10">
                
                <!-- Left: Cover Image -->
                <div class="w-full md:w-56 shrink-0 flex flex-col items-center gap-3">
                    <div class="relative w-full aspect-[3/4] bg-[#0A0C0F] rounded-xl overflow-hidden border border-[#232936] shadow-xl group">
                        <template x-if="result?.cover_url">
                            <img :src="result.cover_url" 
                                 :alt="result.title" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </template>
                        <template x-if="!result?.cover_url">
                            <div class="w-full h-full flex flex-col items-center justify-center text-gray-500 text-xs">
                                <i data-lucide="image" class="w-8 h-8 mb-2"></i>
                                Sin Portada
                            </div>
                        </template>
                        <div class="absolute top-2 right-2 px-2 py-0.5 rounded bg-black/80 border border-white/10 text-[10px] font-mono text-purple-300 font-bold uppercase">
                            HD WEBP
                        </div>
                    </div>

                    <a :href="result?.cover_url" target="_blank" class="text-[11px] font-mono text-purple-400 hover:text-purple-300 flex items-center gap-1">
                        <i data-lucide="external-link" class="w-3 h-3"></i> Abrir Portada Original
                    </a>
                </div>

                <!-- Right: Metadata & Direct Download Stream -->
                <div class="flex-1 space-y-5 w-full">
                    
                    <!-- Header Info -->
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-purple-500/20 border border-purple-500/40 text-purple-300 text-[11px] font-mono font-bold flex items-center gap-1.5 shadow-sm">
                                <i data-lucide="globe" class="w-3 h-3 text-purple-400"></i>
                                <span x-text="result?.source_provider || 'Web'"></span>
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full bg-blue-500/15 border border-blue-500/30 text-blue-400 text-[11px] font-mono font-bold" x-text="result?.platform"></span>
                            <template x-if="result?.raw_genre">
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-300 text-[11px] font-mono font-bold" x-text="'Género: ' + result.raw_genre"></span>
                            </template>
                            <template x-if="result?.release_year">
                                <span class="px-2.5 py-0.5 rounded-full bg-cyan-500/15 border border-cyan-500/30 text-cyan-300 text-[11px] font-mono font-bold" x-text="'Año: ' + result.release_year"></span>
                            </template>
                            <template x-if="result?.region">
                                <span class="px-2.5 py-0.5 rounded-full bg-gray-800 border border-gray-700 text-gray-300 text-[11px] font-mono font-bold" x-text="'Región: ' + result.region"></span>
                            </template>
                            <template x-if="result?.languages">
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-[11px] font-mono font-bold flex items-center gap-1" title="Idiomas detectados">
                                    <i data-lucide="languages" class="w-3 h-3 text-emerald-400"></i>
                                    <span x-text="'Idiomas: ' + result.languages"></span>
                                </span>
                            </template>
                            <template x-if="result?.publisher">
                                <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/15 border border-indigo-500/30 text-indigo-300 text-[11px] font-mono font-bold" x-text="'Publisher: ' + result.publisher"></span>
                            </template>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[11px] font-mono font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                HTTP <span x-text="result?.http_status || '200'"></span> OK
                            </span>
                            <span class="text-xs font-mono text-gray-500" x-text="'Tiempo: ' + result?.latency_ms + 'ms'"></span>
                        </div>
                        <h2 class="text-2xl font-black text-white font-heading tracking-tight" x-text="result?.title"></h2>
                    </div>

                    <!-- Direct Download Details Box -->
                    <div class="bg-[#0A0C0F] border border-[#232936] rounded-xl p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono text-gray-400 flex items-center gap-1.5 font-bold uppercase">
                                <i data-lucide="download-cloud" class="w-4 h-4 text-emerald-400"></i> Enlace Directo Extraído
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-mono text-xs font-bold" x-text="result?.file_size"></span>
                                <span class="px-2 py-0.5 rounded bg-gray-800 text-gray-300 font-mono text-xs font-bold" x-text="result?.file_format"></span>
                            </div>
                        </div>

                        <!-- URL Input with Copy & Open -->
                        <div class="flex items-center gap-2">
                            <input type="text" 
                                   readonly 
                                   :value="result?.direct_download_url" 
                                   class="w-full bg-[#11141A] border border-[#232936] rounded-lg px-3 py-2 text-xs font-mono text-gray-300 select-all focus:outline-none">
                            <button type="button" 
                                    @click="copyUrl(result?.direct_download_url)" 
                                    class="px-3 py-2 rounded-lg bg-[#171B22] hover:bg-[#232936] border border-[#232936] text-xs font-mono text-gray-200 hover:text-white transition-colors shrink-0 flex items-center gap-1"
                                    title="Copiar URL">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span x-text="copied ? '¡Copiado!' : 'Copiar'"></span>
                            </button>
                            <a :href="result?.direct_download_url" 
                               target="_blank" 
                               class="px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all shrink-0 flex items-center gap-1 shadow-md shadow-emerald-600/20">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Probar Descarga
                            </a>
                        </div>
                    </div>

                    <!-- Import Options & Actions Box -->
                    <div class="bg-[#0A0C0F] border border-[#232936] rounded-xl p-4 space-y-4">
                        <div class="flex items-center justify-between border-b border-[#232936] pb-2">
                            <span class="text-xs font-mono font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="sliders" class="w-3.5 h-3.5 text-purple-400"></i> Opciones de Guardado en Catálogo
                            </span>
                            <span class="text-[10px] font-mono text-purple-400 bg-purple-950/40 border border-purple-500/20 px-2 py-0.5 rounded" x-text="'Consola detectada: ' + (result?.platform || 'PSP')"></span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Console Selector -->
                            <div>
                                <label class="block text-[11px] font-mono text-gray-400 mb-1 font-bold">Consola / Ecosistema *</label>
                                <select x-model="selectedConsoleId" 
                                        class="w-full bg-[#11141A] border border-[#232936] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500 font-sans">
                                    @foreach($consoles as $con)
                                    <option value="{{ $con->id }}" style="background-color: #11141A; color: #fff;">
                                        {{ $con->name }} ({{ $con->manufacturer }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Publication Status -->
                            <div>
                                <label class="block text-[11px] font-mono text-gray-400 mb-1 font-bold">Estado Inicial *</label>
                                <select x-model="saveStatus" 
                                        class="w-full bg-[#11141A] border border-[#232936] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500 font-sans">
                                    <option value="DRAFT" style="background-color: #11141A; color: #fff;">Borrador (Para redactar descripción con IA)</option>
                                    <option value="PUBLISHED" style="background-color: #11141A; color: #fff;">Publicado (Visible inmediatamente en la web)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Categories & Genres Selector Grid (Auto-checked from scraper) -->
                        <div>
                            <label class="block text-[11px] font-mono text-gray-400 mb-1 font-bold flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="tag" class="w-3.5 h-3.5 text-purple-400"></i> Categorías & Géneros (Autoseleccionados)
                                </span>
                                <span class="text-purple-400 text-[10px]" x-show="result?.raw_genre" x-text="'Detectado: ' + result?.raw_genre"></span>
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-2.5 rounded-xl bg-[#11141A] border border-[#232936] max-h-36 overflow-y-auto">
                                <template x-for="cat in categoriesList" :key="cat.id">
                                    <label class="flex items-center gap-2 p-1.5 rounded-lg bg-[#0A0C0F] border border-[#232936] text-[11px] font-mono text-gray-300 cursor-pointer hover:border-purple-500/40">
                                        <input type="checkbox" :value="String(cat.id)" x-model="selectedCategoryIds" class="rounded bg-[#171B22] border-[#232936] text-purple-600 focus:ring-purple-500">
                                        <span class="truncate" x-text="cat.name"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Local Optimization Switch -->
                        <div class="pt-1">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" x-model="optimizeCover" class="mt-0.5 rounded bg-[#11141A] border-[#232936] text-purple-600 focus:ring-purple-500">
                                <div>
                                    <span class="text-xs font-semibold text-gray-200 block">⚡ Descargar y convertir portada a WebP local</span>
                                    <span class="text-[10px] text-gray-500 block leading-tight">Aloja la imagen en tu propio servidor (evita que la carátula se rompa si el sitio externo la borra).</span>
                                </div>
                            </label>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <button type="button" 
                                    @click="saveToCatalog()" 
                                    :disabled="saving"
                                    class="flex-1 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs transition-all flex items-center justify-center gap-2 shadow-lg shadow-purple-600/20 cursor-pointer disabled:opacity-50">
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="saving"></i>
                                <i data-lucide="save" class="w-4 h-4" x-show="!saving"></i>
                                <span x-text="saving ? 'Guardando en Catálogo...' : 'Guardar en Catálogo'"></span>
                            </button>

                            <!-- Open in Full Create Form (New Tab) -->
                            <a :href="getCreateUrl()" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="px-4 py-2.5 rounded-xl bg-[#171B22] hover:bg-[#232936] border border-[#232936] text-gray-300 hover:text-white text-xs font-semibold flex items-center justify-center gap-2 transition-colors shrink-0 group shadow-md"
                               title="Abre el formulario completo en una nueva pestaña con toda la información precargada">
                                <i data-lucide="external-link" class="w-3.5 h-3.5 text-purple-400 group-hover:scale-110 transition-transform"></i>
                                <span>Rellenar en Formulario Completo</span>
                                <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-[#0A0C0F] border border-white/10 text-gray-400">Nueva Pestaña ↗</span>
                            </a>
                        </div>
                    </div>

                    <!-- Save Success Banner -->
                    <div x-show="saveSuccess" x-cloak class="p-3.5 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between gap-2">
                        <span class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                            <span>¡Juego guardado correctamente en tu catálogo!</span>
                        </span>
                        <a :href="editGameUrl" target="_blank" rel="noopener noreferrer" class="font-bold underline text-white hover:text-emerald-200 flex items-center gap-1.5">
                            <span>Editar y completar con IA</span>
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>

                </div>

            </div>
        </div>

        <!-- Game Screenshots Gallery (Extracted from CDRomance) -->
        <div x-show="result?.screenshots && result.screenshots.length > 0" x-cloak class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#232936] pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-blue-500/20 text-blue-400 border border-blue-500/30">
                        <i data-lucide="images" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider font-sans flex items-center gap-2">
                            <span>Capturas de Pantalla (Game Screenshots)</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 font-mono" x-text="(result?.screenshots?.length || 0) + ' detectadas'"></span>
                        </h3>
                        <span class="text-[11px] font-mono text-gray-400">
                            Extraídas directamente en alta resolución desde CDRomance
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-lg bg-[#0A0C0F] border border-[#232936] text-blue-300 text-xs font-mono font-bold"
                          x-text="(selectedScreenshots.length) + ' seleccionadas'">
                    </span>
                    <button type="button" 
                            @click="toggleAllScreenshots()" 
                            class="px-3 py-1 rounded-lg bg-[#171B22] hover:bg-[#232936] border border-[#232936] text-xs font-mono text-gray-300 hover:text-white transition-colors cursor-pointer">
                        <span x-text="selectedScreenshots.length === (result?.screenshots?.length || 0) ? 'Deseleccionar todas' : 'Seleccionar todas'"></span>
                    </button>
                </div>
            </div>

            <!-- Screenshots Grid (6 columns) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <template x-for="(sUrl, idx) in (result?.screenshots || [])" :key="idx">
                    <div class="relative group rounded-xl overflow-hidden border transition-all cursor-pointer bg-[#0A0C0F]"
                         :class="selectedScreenshots.includes(sUrl) ? 'border-blue-500/80 ring-2 ring-blue-500/40' : 'border-[#232936] opacity-50 hover:opacity-100'"
                         @click="toggleScreenshot(sUrl)">
                        <div class="aspect-video w-full overflow-hidden bg-black flex items-center justify-center">
                            <img :src="sUrl" 
                                 :alt="'Screenshot ' + (idx + 1)" 
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                        
                        <!-- Checkbox Overlay -->
                        <div class="absolute top-2 left-2">
                            <div class="w-5 h-5 rounded-md border flex items-center justify-center transition-all shadow-md"
                                 :class="selectedScreenshots.includes(sUrl) ? 'border-blue-400 bg-blue-600 text-white' : 'border-gray-500 bg-black/70'">
                                <i data-lucide="check" class="w-3.5 h-3.5" x-show="selectedScreenshots.includes(sUrl)"></i>
                            </div>
                        </div>

                        <!-- Full Image Link Overlay -->
                        <div class="absolute bottom-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a :href="sUrl" target="_blank" @click.stop class="p-1.5 rounded-lg bg-black/80 hover:bg-blue-600 text-white text-[10px] font-mono flex items-center gap-1 shadow">
                                <i data-lucide="external-link" class="w-3 h-3"></i> HD
                            </a>
                        </div>
                    </div>
                </template>
            </div>
            
            <p class="text-[11px] font-mono text-gray-500 flex items-center gap-1.5 pt-1">
                <i data-lucide="info" class="w-3.5 h-3.5 text-blue-400"></i>
                <span>Las capturas seleccionadas se guardarán en la galería de la base de datos (<code class="text-gray-400 font-mono">game_screenshots</code>) y se mostrarán en la ficha pública del juego.</span>
            </p>
        </div>

        <!-- Raw JSON Inspector Accordion -->
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-4">
            <details class="cursor-pointer text-xs font-mono">
                <summary class="text-gray-400 hover:text-white font-bold select-none flex items-center gap-2">
                    <i data-lucide="code" class="w-4 h-4 text-purple-400"></i>
                    <span>Ver Respuesta JSON Completa (Inspector Técnico)</span>
                </summary>
                <div class="mt-4 p-4 rounded-xl bg-[#0A0C0F] border border-[#232936] overflow-x-auto">
                    <pre class="text-emerald-400 text-xs" x-text="JSON.stringify(result, null, 2)"></pre>
                </div>
            </details>
        </div>

    </div>

</div>

<script>
function scraperDemoApp() {
    return {
        activeProvider: 'romspedia',
        url: 'https://www.romspedia.com/roms/playstation-portable/god-of-war-ghost-of-sparta-original',
        loading: false,
        saving: false,
        copied: false,
        saveSuccess: false,
        editGameUrl: '',
        errorMessage: '',
        result: null,
        consolesList: @json($consoles),
        categoriesList: @json($categories),
        saveStatus: 'DRAFT',
        optimizeCover: true,
        selectedCategoryIds: [],
        selectedScreenshots: [],
        selectedConsoleId: '{{ $consoles->firstWhere("slug", "psp")?->id ?? $consoles->first()?->id }}',

        setProvider(provider) {
            this.activeProvider = provider;
            this.errorMessage = '';
            this.result = null;
            this.saveSuccess = false;
            if (provider === 'romspedia') {
                this.url = 'https://www.romspedia.com/roms/playstation-portable/god-of-war-ghost-of-sparta-original';
            } else if (provider === 'cdromance') {
                this.url = 'https://cdromance.org/psp/atv-offroad-fury-blazin-trails/';
            } else if (provider === 'romsemu') {
                this.url = 'https://romsemu.com/nintendo-switch/super-mario-odyssey/';
            }
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        detectProviderFromUrl() {
            if (!this.url) return;
            const u = this.url.toLowerCase();
            if (u.includes('cdromance.org')) {
                this.activeProvider = 'cdromance';
            } else if (u.includes('romspedia.com')) {
                this.activeProvider = 'romspedia';
            } else if (u.includes('romsemu.com')) {
                this.activeProvider = 'romsemu';
            }
        },

        setUrl(newUrl) {
            this.url = newUrl;
            this.detectProviderFromUrl();
            this.extractData();
        },

        toggleScreenshot(url) {
            if (!url) return;
            if (this.selectedScreenshots.includes(url)) {
                this.selectedScreenshots = this.selectedScreenshots.filter(u => u !== url);
            } else {
                this.selectedScreenshots.push(url);
            }
        },

        toggleAllScreenshots() {
            if (!this.result?.screenshots) return;
            if (this.selectedScreenshots.length === this.result.screenshots.length) {
                this.selectedScreenshots = [];
            } else {
                this.selectedScreenshots = [...this.result.screenshots];
            }
        },

        async extractData() {
            if (!this.url) return;
            this.detectProviderFromUrl();
            this.loading = true;
            this.errorMessage = '';
            this.result = null;
            this.saveSuccess = false;

            try {
                const response = await fetch('{{ route("admin.scraper.extract") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ url: this.url })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    this.errorMessage = data.message || 'Error al extraer la información.';
                } else {
                    this.result = data;

                    if (data.source_provider) {
                        const sp = data.source_provider.toLowerCase();
                        if (sp.includes('cdromance')) {
                            this.activeProvider = 'cdromance';
                        } else if (sp.includes('romspedia')) {
                            this.activeProvider = 'romspedia';
                        } else if (sp.includes('romsemu')) {
                            this.activeProvider = 'romsemu';
                        }
                    }

                    // Autoseleccionar la consola detectada si coincide en la BD
                    if (data.platform_slug && this.consolesList.length) {
                        const matched = this.consolesList.find(c => c.slug === data.platform_slug);
                        if (matched) {
                            this.selectedConsoleId = matched.id;
                        }
                    }

                    // Actualizar lista de categorías reactiva si se crearon nuevas en el backend
                    if (data.all_categories && Array.isArray(data.all_categories)) {
                        this.categoriesList = data.all_categories;
                    }

                    // Autoseleccionar las categorías detectadas
                    if (data.category_ids && Array.isArray(data.category_ids)) {
                        this.selectedCategoryIds = data.category_ids.map(id => String(id));
                    }

                    // Autoseleccionar capturas de pantalla si están presentes
                    if (data.screenshots && Array.isArray(data.screenshots)) {
                        this.selectedScreenshots = [...data.screenshots];
                    } else {
                        this.selectedScreenshots = [];
                    }

                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                }
            } catch (err) {
                this.errorMessage = 'Ocurrió un error en la conexión con el servidor.';
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        copyUrl(text) {
            if (!text) return;
            navigator.clipboard.writeText(text);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        },

        getCreateUrl() {
            if (!this.result) return '{{ route("admin.games.create") }}';
            const params = new URLSearchParams();
            if (this.result.title) params.set('title', this.result.title);
            if (this.result.cover_url) params.set('cover_url', this.result.cover_url);
            if (this.result.direct_download_url) params.set('download_url', this.result.direct_download_url);
            if (this.result.file_size) params.set('file_size', this.result.file_size);
            if (this.result.file_format) params.set('file_format', this.result.file_format);
            if (this.result.release_year) params.set('release_year', this.result.release_year);
            if (this.result.region) params.set('region', this.result.region);
            if (this.result.languages) params.set('languages', this.result.languages);
            if (this.result.publisher) params.set('publisher', this.result.publisher);
            if (this.selectedConsoleId) params.set('console_id', this.selectedConsoleId);
            if (this.selectedCategoryIds && this.selectedCategoryIds.length) {
                params.set('categories', this.selectedCategoryIds.join(','));
            }
            if (this.selectedScreenshots && this.selectedScreenshots.length) {
                params.set('screenshots', JSON.stringify(this.selectedScreenshots));
            }
            return '{{ route("admin.games.create") }}?' + params.toString();
        },

        async saveToCatalog() {
            if (!this.result) return;
            this.saving = true;
            this.saveSuccess = false;

            try {
                const response = await fetch('{{ route("admin.scraper.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title: this.result.title,
                        cover_url: this.result.cover_url,
                        download_url: this.result.direct_download_url,
                        file_size: this.result.file_size,
                        file_format: this.result.file_format || 'ZIP',
                        console_id: this.selectedConsoleId,
                        category_ids: this.selectedCategoryIds,
                        screenshots: this.selectedScreenshots,
                        release_year: this.result.release_year,
                        region: this.result.region,
                        languages: this.result.languages,
                        publisher: this.result.publisher,
                        status: this.saveStatus,
                        optimize_cover: this.optimizeCover,
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.saveSuccess = true;
                    this.editGameUrl = data.edit_url;
                } else {
                    alert(data.message || 'No se pudo guardar');
                }
            } catch (e) {
                alert('Error al guardar el juego en el catálogo.');
            } finally {
                this.saving = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        }
    };
}
</script>
@endsection
