@extends('layouts.admin')

@section('title', 'Configuración del Sistema — Administración ROMHUB')

@section('content')
<div class="space-y-6" x-data="settingsForm()">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans">Configuración Global del Sistema</h1>
            <p class="text-xs text-gray-400 font-mono mt-0.5">Identidad de marca, logo, favicon, SEO, IA Google Gemini y almacenamiento Cloudflare R2</p>
        </div>
        <form action="{{ route('admin.settings.clearCache') }}" method="POST" onsubmit="return confirm('¿Deseas refrescar y limpiar toda la caché del sistema (vistas, configuración y ajustes)?')">
            @csrf
            <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-mono font-bold flex items-center gap-2 transition-all cursor-pointer shadow-sm">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                <span>🧹 Limpiar Toda la Caché</span>
            </button>
        </form>
    </div>

    <!-- Navigation Tabs in Requested Order -->
    <div class="flex flex-wrap border-b border-[#232936] gap-2 font-mono text-xs">
        
        <!-- Tab 1: General -->
        <button @click="activeTab = 'general'" 
                :class="activeTab === 'general' ? 'border-blue-500 text-blue-400 bg-[#11141A]' : 'border-transparent text-gray-400 hover:text-white'"
                class="px-5 py-3 border-b-2 font-bold uppercase tracking-wider transition-colors rounded-t-xl flex items-center gap-2">
            <i data-lucide="sliders" class="w-4 h-4"></i> General & Marca
        </button>

        <!-- Tab 2: SEO & Metadatos -->
        <button @click="activeTab = 'seo'" 
                :class="activeTab === 'seo' ? 'border-blue-500 text-blue-400 bg-[#11141A]' : 'border-transparent text-gray-400 hover:text-white'"
                class="px-5 py-3 border-b-2 font-bold uppercase tracking-wider transition-colors rounded-t-xl flex items-center gap-2">
            <i data-lucide="globe" class="w-4 h-4"></i> SEO & Metadatos
        </button>

        <!-- Tab 3: Inteligencia Artificial (AI) -->
        <button @click="activeTab = 'ai'" 
                :class="activeTab === 'ai' ? 'border-blue-500 text-blue-400 bg-[#11141A]' : 'border-transparent text-gray-400 hover:text-white'"
                class="px-5 py-3 border-b-2 font-bold uppercase tracking-wider transition-colors rounded-t-xl flex items-center gap-2">
            <i data-lucide="sparkles" class="w-4 h-4 text-purple-400"></i> Inteligencia Artificial (AI)
        </button>

        <!-- Tab 4: Cloudflare R2 / S3 -->
        <button type="button" @click="activeTab = 'storage'" 
                :class="activeTab === 'storage' ? 'border-blue-500 text-blue-400 bg-[#11141A]' : 'border-transparent text-gray-400 hover:text-white'"
                class="px-5 py-3 border-b-2 font-bold uppercase tracking-wider transition-colors rounded-t-xl flex items-center gap-2 cursor-pointer">
            <i data-lucide="cloud" class="w-4 h-4"></i> Cloudflare R2 / S3
        </button>

        <!-- Tab 5: Scripts & Analytics -->
        <button type="button" @click="activeTab = 'custom_code'" 
                :class="activeTab === 'custom_code' ? 'border-emerald-500 text-emerald-400 bg-[#11141A]' : 'border-transparent text-gray-400 hover:text-white'"
                class="px-5 py-3 border-b-2 font-bold uppercase tracking-wider transition-colors rounded-t-xl flex items-center gap-2 cursor-pointer">
            <i data-lucide="code-2" class="w-4 h-4 text-emerald-400"></i> Scripts & Analytics
        </button>

        <!-- Tab 6: Bot de Telegram -->
        <button type="button" @click="activeTab = 'telegram'" 
                :class="activeTab === 'telegram' ? 'border-sky-500 text-sky-400 bg-[#11141A]' : 'border-transparent text-gray-400 hover:text-white'"
                class="px-5 py-3 border-b-2 font-bold uppercase tracking-wider transition-colors rounded-t-xl flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-sky-400 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.135-.954l11.566-4.458c.538-.196 1.006.128.832.946z"/></svg>
            <span>Bot de Telegram</span>
        </button>

        <!-- Tab 7: Publicidad & Monetización -->
        <button type="button" @click="activeTab = 'ads'" 
                :class="activeTab === 'ads' ? 'border-amber-500 text-amber-400 bg-[#11141A]' : 'border-transparent text-gray-400 hover:text-white'"
                class="px-5 py-3 border-b-2 font-bold uppercase tracking-wider transition-colors rounded-t-xl flex items-center gap-2 cursor-pointer">
            <i data-lucide="megaphone" class="w-4 h-4 text-amber-400"></i>
            <span>Publicidad & Ads</span>
            @if(($settings['ads_enabled'] ?? '0') === '1')
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse ml-1" title="Sistema Activo"></span>
            @endif
        </button>
    </div>

    <!-- Main Settings Form -->
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- TAB 1: General Site Settings & Brand Identity -->
        <div x-show="activeTab === 'general'" class="space-y-6">
            
            <!-- LIVE BRANDING PREVIEW CARD -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="eye" class="w-4 h-4 text-blue-400"></i>
                        <h3 class="text-xs font-mono font-bold uppercase text-white tracking-wider">Vista Previa en Tiempo Real de la Marca</h3>
                    </div>
                    <span class="text-[11px] font-mono text-emerald-400 bg-emerald-950/40 border border-emerald-500/30 px-2.5 py-0.5 rounded-full flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Header Preview
                    </span>
                </div>

                <!-- Realistic Mockup Header Bar -->
                <div class="bg-[#0A0C0F] border border-[#232936] rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <!-- Brand Element -->
                    <div class="flex items-center gap-3">
                        <template x-if="logoType === 'image' && logoUrl">
                            <img :src="logoUrl" alt="Logo" class="h-9 w-auto object-contain">
                        </template>
                        <template x-if="logoType !== 'image' || !logoUrl">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-blue-500 flex items-center justify-center text-white font-black text-sm shadow-md shrink-0">
                                    <i :data-lucide="selectedIcon" class="w-5 h-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="font-extrabold tracking-tight text-white text-lg leading-tight font-sans">
                                        <span x-text="logoPrefix || 'ROM'"></span><span class="text-blue-500" x-text="logoSuffix || 'HUB'"></span>
                                    </div>
                                    <div class="text-[9px] font-mono tracking-widest text-gray-500 uppercase -mt-0.5" x-text="tagline || 'Preservation Vault'"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Mock Nav Items -->
                    <div class="hidden md:flex items-center gap-2 text-xs font-medium text-gray-400">
                        <span class="px-3 py-1 rounded bg-[#242F44] text-white">HOME</span>
                        <span class="px-3 py-1 text-gray-400">Consolas (20)</span>
                    </div>

                    <!-- Favicon Preview Badge -->
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#11141A] border border-[#232936] text-[11px] font-mono text-gray-300">
                        <span>Favicon Activo:</span>
                        <template x-if="faviconUrl">
                            <img :src="faviconUrl" class="w-4 h-4 object-contain rounded" alt="Favicon">
                        </template>
                        <template x-if="!faviconUrl">
                            <div class="w-4 h-4 rounded bg-blue-600 flex items-center justify-center text-[9px] text-white font-bold">R</div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- BRANDING CONFIGURATION FORM -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
                <div class="border-b border-[#232936] pb-4">
                    <h2 class="text-base font-bold text-white font-sans flex items-center gap-2">
                        <i data-lucide="palette" class="w-5 h-5 text-blue-400"></i> Identidad Visual (Logo, Ícono, Favicon y Tipografía)
                    </h2>
                    <p class="text-xs text-gray-400 font-sans mt-0.5">Personaliza el formato del logo, las letras de la marca, el ícono representativo y el favicon del navegador.</p>
                </div>

                <!-- 1. Tipo de Logo (Selector) -->
                <div class="space-y-2">
                    <label class="block text-xs font-mono font-bold uppercase text-gray-300">Tipo de Logo a Mostrar</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label @click="logoType = 'icon_text'" 
                               :class="logoType === 'icon_text' ? 'bg-blue-600/15 border-blue-500 text-white' : 'bg-[#0A0C0F] border-[#232936] text-gray-400 hover:border-gray-600'" 
                               class="p-4 rounded-xl border cursor-pointer flex items-start gap-3 transition-all">
                            <input type="radio" name="site_logo_type" value="icon_text" x-model="logoType" class="mt-1 text-blue-600 focus:ring-0">
                            <div>
                                <div class="font-bold text-xs font-sans text-white flex items-center gap-1.5">
                                    <i data-lucide="component" class="w-4 h-4 text-blue-400"></i> Ícono + Tipografía Digital (Recomendado)
                                </div>
                                <p class="text-[11px] text-gray-400 font-sans mt-0.5">Combina un ícono moderno vectorizado con texto de dos tonos (ej: ROM + HUB) y eslogan.</p>
                            </div>
                        </label>

                        <label @click="logoType = 'image'" 
                               :class="logoType === 'image' ? 'bg-blue-600/15 border-blue-500 text-white' : 'bg-[#0A0C0F] border-[#232936] text-gray-400 hover:border-gray-600'" 
                               class="p-4 rounded-xl border cursor-pointer flex items-start gap-3 transition-all">
                            <input type="radio" name="site_logo_type" value="image" x-model="logoType" class="mt-1 text-blue-600 focus:ring-0">
                            <div>
                                <div class="font-bold text-xs font-sans text-white flex items-center gap-1.5">
                                    <i data-lucide="image" class="w-4 h-4 text-purple-400"></i> Imagen / Archivo de Logo Personalizado
                                </div>
                                <p class="text-[11px] text-gray-400 font-sans mt-0.5">Sube o enlaza un logotipo gráfico en formato PNG transparente, SVG o WebP.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 2. Logo Letra & Textos de Marca -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono text-xs pt-2">
                    <div>
                        <label class="block text-gray-400 mb-1">Nombre Global de la Plataforma</label>
                        <input type="text" name="site_name" value="{{ $settings['site_name'] ?? 'PichoDownload' }}" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white" placeholder="PichoDownload">
                    </div>

                    <div>
                        <label class="block text-purple-400 font-bold mb-1 flex items-center gap-1">
                            <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                            <span>Dominio Web (Producción)</span>
                        </label>
                        <input type="text" name="site_domain" value="{{ $settings['site_domain'] ?? '' }}" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-purple-300 font-mono" placeholder="ej. pichodownload.com (o auto)">
                        <p class="text-[10px] text-gray-500 mt-1">Si lo dejas vacío, detecta automáticamente el dominio en vivo.</p>
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-1">Logo Texto Principal (Blanco)</label>
                        <input type="text" name="site_logo_prefix" x-model="logoPrefix" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white" placeholder="ROM">
                    </div>

                    <div>
                        <label class="block text-blue-400 font-bold mb-1">Logo Texto Acento (Color Azul)</label>
                        <input type="text" name="site_logo_suffix" x-model="logoSuffix" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-blue-400 font-bold" placeholder="HUB">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-gray-400 mb-1">Eslogan / Subtexto Inferior</label>
                        <input type="text" name="site_tagline" x-model="tagline" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-sans" placeholder="Preservation Vault">
                    </div>
                </div>

                <!-- 3. SELECTOR VISUAL DE ÍCONOS (LUCIDE ICON PICKER) -->
                <div class="pt-4 border-t border-[#232936] space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <label class="block text-xs font-mono font-bold uppercase text-white flex items-center gap-2">
                                <i data-lucide="sparkles" class="w-4 h-4 text-blue-400"></i> Selector de Ícono para el Logo
                            </label>
                            <p class="text-[11px] text-gray-400 font-sans mt-0.5">Haz clic en cualquier ícono para seleccionarlo como símbolo oficial del portal.</p>
                        </div>
                        
                        <!-- Search Icon Filter -->
                        <div class="relative w-full sm:w-64">
                            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                            <input type="text" 
                                   x-model="iconSearch" 
                                   @input="$nextTick(() => { if (window.lucide) window.lucide.createIcons(); })"
                                   placeholder="Filtrar íconos (ej: gamepad, zap)..." 
                                   class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg pl-8 pr-3 py-1.5 text-xs text-white font-mono focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Hidden Input for Form Submission -->
                    <input type="hidden" name="site_logo_icon" :value="selectedIcon">

                    <!-- Icon Grid Container -->
                    <div class="p-4 bg-[#0A0C0F] border border-[#232936] rounded-xl max-h-64 overflow-y-auto">
                        <div class="grid grid-cols-4 sm:grid-cols-8 md:grid-cols-12 gap-2.5">
                            <template x-for="icon in filteredIcons()" :key="icon">
                                <button type="button" 
                                        @click="selectIcon(icon)"
                                        :class="selectedIcon === icon ? 'bg-blue-600 text-white border-blue-400 shadow-lg shadow-blue-600/30 scale-105' : 'bg-[#11141A] text-gray-400 hover:text-white hover:bg-[#171B22] border-[#232936]'"
                                        class="p-2.5 rounded-xl border flex flex-col items-center justify-center gap-1.5 transition-all group"
                                        :title="icon">
                                    <i :data-lucide="icon" class="w-5 h-5"></i>
                                    <span class="text-[9px] font-mono truncate w-full text-center group-hover:text-white" x-text="icon"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs font-mono text-gray-400">
                        <span>Ícono Actualmente Seleccionado:</span>
                        <span class="px-2.5 py-1 rounded bg-blue-500/20 text-blue-400 border border-blue-500/30 font-bold flex items-center gap-1.5">
                            <i :data-lucide="selectedIcon" class="w-3.5 h-3.5"></i>
                            <span x-text="selectedIcon"></span>
                        </span>
                    </div>
                </div>

                <!-- 4. IMAGEN DE LOGO & FAVICON UPLOADS -->
                <div class="pt-4 border-t border-[#232936] grid grid-cols-1 sm:grid-cols-2 gap-6 font-mono text-xs">
                    <!-- Logo Image URL / File Upload -->
                    <div class="space-y-3 bg-[#0A0C0F] border border-[#232936] p-4 rounded-xl">
                        <div class="flex items-center justify-between">
                            <label class="block font-bold text-gray-200 flex items-center gap-1.5">
                                <i data-lucide="image" class="w-4 h-4 text-purple-400"></i> Archivo / URL de Logo Gráfico
                            </label>
                            <span class="text-[10px] text-gray-500 font-sans">PNG, SVG, WebP</span>
                        </div>
                        <div>
                            <input type="text" name="site_logo_url" x-model="logoUrl" value="{{ $settings['site_logo_url'] ?? '' }}" placeholder="https://mi-dominio.com/logo.png" class="w-full bg-[#11141A] border border-[#232936] rounded-lg p-2.5 text-white mb-2">
                            <label class="block text-[11px] text-gray-400 mb-1">O sube una imagen desde tu equipo:</label>
                            <input type="file" name="site_logo_file" accept="image/*" class="w-full text-xs text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer">
                        </div>
                    </div>

                    <!-- Favicon URL / File Upload -->
                    <div class="space-y-3 bg-[#0A0C0F] border border-[#232936] p-4 rounded-xl">
                        <div class="flex items-center justify-between">
                            <label class="block font-bold text-gray-200 flex items-center gap-1.5">
                                <i data-lucide="bookmark" class="w-4 h-4 text-amber-400"></i> Favicon del Sitio Web
                            </label>
                            <span class="text-[10px] text-gray-500 font-sans">ICO, PNG (32x32)</span>
                        </div>
                        <div>
                            <input type="text" name="site_favicon_url" x-model="faviconUrl" value="{{ $settings['site_favicon_url'] ?? '' }}" placeholder="https://mi-dominio.com/favicon.ico" class="w-full bg-[#11141A] border border-[#232936] rounded-lg p-2.5 text-white mb-2">
                            <label class="block text-[11px] text-gray-400 mb-1">O sube un favicon (.ico o .png):</label>
                            <input type="file" name="site_favicon_file" accept="image/x-icon,image/png,image/svg+xml" class="w-full text-xs text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-600 file:text-white hover:file:bg-purple-500 cursor-pointer">
                        </div>
                    </div>
                </div>

                <!-- 5. ANCHO MÁXIMO DEL LAYOUT -->
                <div class="pt-4 border-t border-[#232936]">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-blue-400 font-bold text-xs font-mono">Ancho Máximo del Sitio (Layout Container Width)</label>
                        <span class="text-[11px] text-emerald-400 font-bold font-mono">Activo: {{ $settings['container_max_width'] ?? 'max-w-[1200px]' }}</span>
                    </div>
                    <p class="text-[11px] text-gray-400 font-sans mb-3">Controla qué tan ancha o compacta se visualiza la tienda y todas las páginas públicas en pantallas de escritorio.</p>
                    
                    <select name="container_max_width" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-3 text-xs text-white font-mono focus:border-blue-500">
                        <option value="max-w-5xl" {{ ($settings['container_max_width'] ?? '') === 'max-w-5xl' ? 'selected' : '' }}>
                            Compacto Estrecho (1024px — max-w-5xl)
                        </option>
                        <option value="max-w-6xl" {{ ($settings['container_max_width'] ?? '') === 'max-w-6xl' ? 'selected' : '' }}>
                            Compacto Clásico (1152px — max-w-6xl)
                        </option>
                        <option value="max-w-[1200px]" {{ ($settings['container_max_width'] ?? 'max-w-[1200px]') === 'max-w-[1200px]' ? 'selected' : '' }}>
                            ★ Estándar ROMHUB (1200px — max-w-[1200px]) [Recomendado]
                        </option>
                        <option value="max-w-7xl" {{ ($settings['container_max_width'] ?? '') === 'max-w-7xl' ? 'selected' : '' }}>
                            Equilibrado (1280px — max-w-7xl)
                        </option>
                        <option value="max-w-[1400px]" {{ ($settings['container_max_width'] ?? '') === 'max-w-[1400px]' ? 'selected' : '' }}>
                            Panorámico Amplio (1400px — max-w-[1400px])
                        </option>
                        <option value="max-w-[1500px]" {{ ($settings['container_max_width'] ?? '') === 'max-w-[1500px]' ? 'selected' : '' }}>
                            Ancho Completo (1500px — max-w-[1500px])
                        </option>
                    </select>
                </div>

            </div>

            <!-- 6. CONFIGURACIÓN DEL HERO & ENCABEZADO DE PORTADA -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
                <div class="border-b border-[#232936] pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base font-bold text-white font-sans flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-5 h-5 text-red-400"></i> Sección Hero & Buscador Principal (Textos de Portada)
                        </h2>
                        <p class="text-xs text-gray-400 font-sans mt-0.5">Personaliza los títulos, insignias, descripciones y el botón del buscador de la portada.</p>
                    </div>
                    <span class="text-[11px] font-mono text-red-400 bg-red-950/40 border border-red-500/30 px-2.5 py-1 rounded-full w-fit">
                        Hero Banner
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 font-mono text-xs">
                    <!-- Selector de Diseño / Estilo del Hero -->
                    <div class="sm:col-span-2">
                        <label class="block text-gray-300 mb-1 font-bold flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="palette" class="w-4 h-4 text-red-400"></i>
                                <span>Estilo de Diseño del Hero (Portada)</span>
                            </span>
                            <span class="text-[10px] text-emerald-400 font-mono">5 Variantes</span>
                        </label>
                        <select name="home_hero_style" 
                                class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono focus:border-blue-500 cursor-pointer">
                            <option value="1" {{ ($settings['home_hero_style'] ?? '1') === '1' ? 'selected' : '' }}>
                                1. Cyber-Minimal (Limpio, Alta Precisión & Mobile-First — Recomendado)
                            </option>
                            <option value="2" {{ ($settings['home_hero_style'] ?? '1') === '2' ? 'selected' : '' }}>
                                2. Split Studio & Hardware (Asimétrico con Dock de Consolas)
                            </option>
                            <option value="3" {{ ($settings['home_hero_style'] ?? '1') === '3' ? 'selected' : '' }}>
                                3. Swiss Catalog Archive (Monospace y Filtro de Consola Integrado)
                            </option>
                            <option value="4" {{ ($settings['home_hero_style'] ?? '1') === '4' ? 'selected' : '' }}>
                                4. Neo-Terminal Command Deck (Retro Tech HUD)
                            </option>
                            <option value="5" {{ ($settings['home_hero_style'] ?? '1') === '5' ? 'selected' : '' }}>
                                5. Spotlight Showcase (Inmersivo con Carátulas Flotantes)
                            </option>
                        </select>
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Selecciona cuál de los 5 diseños será el predeterminado para todos los visitantes de la web.</span>
                    </div>

                    <!-- Insignia / Píldora Superior -->
                    <div class="sm:col-span-2">
                        <label class="block text-gray-300 mb-1 font-bold flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            <span>Píldora / Insignia Superior</span>
                        </label>
                        <input type="text" 
                               name="home_hero_badge" 
                               value="{{ $settings['home_hero_badge'] ?? 'ROM VAULT • 20 RETRO SYSTEMS • 100% CLEAN DUMPS' }}" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono focus:border-blue-500"
                               placeholder="ROM VAULT • 20 RETRO SYSTEMS • 100% CLEAN DUMPS">
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Texto en la cápsula roja superior sobre el título principal.</span>
                    </div>

                    <!-- Título Principal (Texto Normal / Prefijo) -->
                    <div>
                        <label class="block text-gray-300 mb-1 font-bold">Título Principal (Texto Inicial)</label>
                        <input type="text" 
                               name="home_hero_title_prefix" 
                               value="{{ $settings['home_hero_title_prefix'] ?? 'Retro ROMs & Emulators for' }}" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-sans font-bold focus:border-blue-500"
                               placeholder="Retro ROMs & Emulators for">
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Primera parte del título en color oscuro.</span>
                    </div>

                    <!-- Título Principal (Texto Resaltado en Rojo) -->
                    <div>
                        <label class="block text-red-400 mb-1 font-bold">Título Principal (Texto Resaltado en Rojo)</label>
                        <input type="text" 
                               name="home_hero_title_highlight" 
                               value="{{ $settings['home_hero_title_highlight'] ?? 'every classic console.' }}" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-red-400 font-sans font-bold focus:border-blue-500"
                               placeholder="every classic console.">
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Palabras destacadas con el color de acento rojo retro.</span>
                    </div>

                    <!-- Descripción / Subtítulo -->
                    <div class="sm:col-span-2">
                        <label class="block text-gray-300 mb-1 font-bold">Descripción / Subtítulo del Hero</label>
                        <textarea name="home_hero_description" 
                                  rows="3" 
                                  class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-sans focus:border-blue-500 leading-relaxed"
                                  placeholder="Descarga videojuegos retro verificados (No-Intro / Redump), archivos BIOS y emuladores...">{{ $settings['home_hero_description'] ?? 'Descarga videojuegos retro verificados (No-Intro / Redump), archivos BIOS y emuladores para PlayStation, Nintendo, Sega, Xbox y más de 15 sistemas clásicos.' }}</textarea>
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Párrafo explicativo bajo el titular en la portada.</span>
                    </div>

                    <!-- Placeholder del Buscador -->
                    <div>
                        <label class="block text-gray-300 mb-1 font-bold">Placeholder del Buscador</label>
                        <input type="text" 
                               name="home_hero_search_placeholder" 
                               value="{{ $settings['home_hero_search_placeholder'] ?? 'Search a game, console or BIOS...' }}" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-sans focus:border-blue-500"
                               placeholder="Search a game, console or BIOS...">
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Texto sugerido dentro del input de búsqueda.</span>
                    </div>

                    <!-- Texto del Botón de Búsqueda -->
                    <div>
                        <label class="block text-gray-300 mb-1 font-bold">Texto del Botón de Búsqueda</label>
                        <input type="text" 
                               name="home_hero_search_button" 
                               value="{{ $settings['home_hero_search_button'] ?? 'Search' }}" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-sans font-bold focus:border-blue-500"
                               placeholder="Search">
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Texto en el botón rojo de la barra de búsqueda (ej. Search o Buscar).</span>
                    </div>
                </div>
            </div>

            <!-- 7. CONFIGURACIÓN DE LA PORTADA: ROMS AÑADIDOS RECIENTES -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
                <div class="border-b border-[#232936] pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base font-bold text-white font-sans flex items-center gap-2">
                            <i data-lucide="layout-grid" class="w-5 h-5 text-blue-400"></i> Sección: ROMs Añadidos Recientes (Portada)
                        </h2>
                        <p class="text-xs text-gray-400 font-sans mt-0.5">Controla cuántos videojuegos mostrar, la cantidad de columnas del grid y el orden.</p>
                    </div>
                    <span class="text-[11px] font-mono text-blue-400 bg-blue-950/40 border border-blue-500/30 px-2.5 py-1 rounded-full w-fit">
                        Home Grid Feed
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 font-mono text-xs">
                    <!-- Título de la Sección -->
                    <div>
                        <label class="block text-gray-400 mb-1 font-bold">Título de la Sección</label>
                        <input type="text" name="home_recent_title" value="{{ $settings['home_recent_title'] ?? 'ROMs Añadidos Recientes' }}" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-sans focus:border-blue-500">
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Título que encabeza la cuadrícula en la página principal.</span>
                    </div>

                    <!-- Cantidad de ROMs a mostrar -->
                    <div>
                        <label class="block text-gray-400 mb-1 font-bold">Cantidad de ROMs / Posts a Mostrar</label>
                        <input type="number" min="1" max="60" name="home_recent_count" value="{{ $settings['home_recent_count'] ?? '12' }}" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white focus:border-blue-500">
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Ejemplos recomendados: 6, 12, 18, 24, 30, 36.</span>
                    </div>

                    <!-- Columnas en Pantallas Grandes (Desktop Grid) -->
                    <div>
                        <label class="block text-gray-400 mb-1 font-bold">Columnas en Pantallas Grandes (Grid)</label>
                        <select name="home_recent_columns" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white focus:border-blue-500">
                            <option value="4" {{ ($settings['home_recent_columns'] ?? '') === '4' ? 'selected' : '' }}>
                                4 Columnas (Carátulas Grandes)
                            </option>
                            <option value="5" {{ ($settings['home_recent_columns'] ?? '') === '5' ? 'selected' : '' }}>
                                5 Columnas (Medianas Equilibradas)
                            </option>
                            <option value="6" {{ ($settings['home_recent_columns'] ?? '6') === '6' ? 'selected' : '' }}>
                                ★ 6 Columnas (Estándar ROMHUB) [Recomendado]
                            </option>
                            <option value="7" {{ ($settings['home_recent_columns'] ?? '') === '7' ? 'selected' : '' }}>
                                7 Columnas (Panorámico Denso)
                            </option>
                            <option value="8" {{ ($settings['home_recent_columns'] ?? '') === '8' ? 'selected' : '' }}>
                                8 Columnas (Máxima Densidad)
                            </option>
                        </select>
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Se adapta automáticamente en móviles y tablets.</span>
                    </div>

                    <!-- Criterio de Ordenación -->
                    <div class="sm:col-span-3 pt-3 border-t border-[#232936]">
                        <label class="block text-gray-400 mb-2 font-bold">Criterio de Ordenación de los ROMs</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="p-3 rounded-xl bg-[#0A0C0F] border border-[#232936] flex items-center gap-2.5 cursor-pointer hover:border-blue-500/50 transition-colors">
                                <input type="radio" name="home_recent_order" value="created_at" {{ ($settings['home_recent_order'] ?? 'created_at') === 'created_at' ? 'checked' : '' }} class="text-blue-600 focus:ring-0">
                                <div>
                                    <div class="font-bold text-white text-xs font-sans">Por Fecha de Creación</div>
                                    <div class="text-[10px] text-gray-400 font-sans">Últimos videojuegos dados de alta</div>
                                </div>
                            </label>

                            <label class="p-3 rounded-xl bg-[#0A0C0F] border border-[#232936] flex items-center gap-2.5 cursor-pointer hover:border-blue-500/50 transition-colors">
                                <input type="radio" name="home_recent_order" value="updated_at" {{ ($settings['home_recent_order'] ?? '') === 'updated_at' ? 'checked' : '' }} class="text-blue-600 focus:ring-0">
                                <div>
                                    <div class="font-bold text-white text-xs font-sans">Por Última Modificación</div>
                                    <div class="text-[10px] text-gray-400 font-sans">ROMs con cambios recientes</div>
                                </div>
                            </label>

                            <label class="p-3 rounded-xl bg-[#0A0C0F] border border-[#232936] flex items-center gap-2.5 cursor-pointer hover:border-blue-500/50 transition-colors">
                                <input type="radio" name="home_recent_order" value="random" {{ ($settings['home_recent_order'] ?? '') === 'random' ? 'checked' : '' }} class="text-blue-600 focus:ring-0">
                                <div>
                                    <div class="font-bold text-white text-xs font-sans">Aleatorio / Shuffle</div>
                                    <div class="text-[10px] text-gray-400 font-sans">Variedad rotativa en cada visita</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7. CONFIGURACIÓN DEL PIE DE PÁGINA (FOOTER) -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
                <div class="border-b border-[#232936] pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base font-bold text-white font-sans flex items-center gap-2">
                            <i data-lucide="panel-bottom" class="w-5 h-5 text-emerald-400"></i> Sección: Pie de Página (Footer) & Textos de Preservación
                        </h2>
                        <p class="text-xs text-gray-400 font-sans mt-0.5">Edita el título del archivo digital, la leyenda de preservación No-Intro/Redump y los derechos reservados.</p>
                    </div>
                    <span class="text-[11px] font-mono text-emerald-400 bg-emerald-950/40 border border-emerald-500/30 px-2.5 py-1 rounded-full w-fit">
                        Footer Customizer
                    </span>
                </div>

                <!-- Live Footer Preview Snippet -->
                <div class="space-y-2">
                    <label class="block text-[11px] font-mono uppercase text-gray-400 font-bold">Vista Previa en Tiempo Real del Footer:</label>
                    <div class="bg-[#0A0C0F] border border-[#232936] rounded-xl p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <template x-if="logoType === 'image' && logoUrl">
                                <img :src="logoUrl" alt="Logo" class="h-7 w-auto object-contain">
                            </template>
                            <template x-if="logoType !== 'image' || !logoUrl">
                                <div class="w-7 h-7 rounded bg-blue-600 flex items-center justify-center text-white font-black text-xs shrink-0">
                                    <i :data-lucide="selectedIcon" class="w-4 h-4 text-white"></i>
                                </div>
                            </template>
                            <div>
                                <strong class="text-white font-sans text-sm block" x-text="footerTitle || ((logoPrefix || 'ROM') + (logoSuffix || 'HUB') + ' DIGITAL ARCHIVE')"></strong>
                                <p class="text-[11px] text-gray-400 font-sans mt-0.5" x-text="footerDescription || 'Preservación digital conforme a estándares No-Intro & Redump.'"></p>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-500 font-mono" x-text="footerCopyright || ('© {{ date('Y') }} ' + (logoPrefix || 'ROM') + (logoSuffix || 'HUB') + ' Inc. Todos los derechos reservados.')"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 font-mono text-xs">
                    <!-- Título del Footer -->
                    <div>
                        <label class="block text-gray-300 mb-1 font-bold">Título / Encabezado Principal del Footer</label>
                        <input type="text" name="footer_title" x-model="footerTitle" value="{{ $settings['footer_title'] ?? '' }}" placeholder="ej: PICHORoms DIGITAL ARCHIVE" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-sans focus:border-emerald-500">
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Por defecto: <span class="text-gray-400 font-mono">{{ $settings['site_logo_prefix'] ?? 'ROM' }}{{ $settings['site_logo_suffix'] ?? 'HUB' }} DIGITAL ARCHIVE</span>. Déjalo vacío para usar el valor por defecto o escribe tu título personalizado.</span>
                    </div>

                    <!-- Leyenda de Preservación -->
                    <div>
                        <label class="block text-gray-300 mb-1 font-bold">Leyenda / Subtítulo de Preservación</label>
                        <input type="text" name="footer_description" x-model="footerDescription" value="{{ $settings['footer_description'] ?? 'Preservación digital conforme a estándares No-Intro & Redump.' }}" placeholder="ej: Preservación digital conforme a estándares No-Intro & Redump." class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-sans focus:border-emerald-500">
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Texto informativo debajo del título (puedes cambiarlo por tu propio eslogan o aviso).</span>
                    </div>

                    <!-- Copyright & Derechos -->
                    <div class="sm:col-span-2">
                        <label class="block text-gray-300 mb-1 font-bold">Texto de Copyright / Derechos Reservados (Extremo Derecho)</label>
                        <input type="text" name="footer_copyright" x-model="footerCopyright" value="{{ $settings['footer_copyright'] ?? '' }}" placeholder="ej: © {{ date('Y') }} PichoRoms Inc. Todos los derechos reservados." class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-sans focus:border-emerald-500">
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Si se deja vacío, tomará automáticamente el año actual y el nombre del portal.</span>
                    </div>
                </div>
            </div>

            <!-- 8. CANAL OFICIAL DE TELEGRAM & COMUNIDAD -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
                <div class="border-b border-[#232936] pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base font-bold text-white font-sans flex items-center gap-2">
                            <i data-lucide="send" class="w-5 h-5 text-sky-400"></i> Comunidad & Canal Oficial de Telegram
                        </h2>
                        <p class="text-xs text-gray-400 font-sans mt-0.5">Configura el enlace de Telegram que aparecerá animado en la ficha de cada videojuego sobre el botón de descarga.</p>
                    </div>
                    <span class="text-[11px] font-mono text-sky-400 bg-sky-950/40 border border-sky-500/30 px-2.5 py-1 rounded-full w-fit flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span>
                        Telegram Integration
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 font-mono text-xs">
                    <div>
                        <label class="block text-gray-300 mb-1 font-bold">Enlace o Usuario del Canal / Grupo de Telegram</label>
                        <div class="relative">
                            <i data-lucide="send" class="w-4 h-4 text-sky-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                            <input type="text" 
                                   name="telegram_channel_url" 
                                   value="{{ $settings['telegram_channel_url'] ?? '' }}" 
                                   placeholder="https://t.me/mi_canal_roms o @mi_canal_roms" 
                                   class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg pl-9 pr-3 py-2.5 text-white font-sans focus:border-sky-500">
                        </div>
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Ingresa el enlace oficial (ej: <code>https://t.me/tu_canal</code>) o tu usuario con <code>@tu_canal</code>.</span>
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-1 font-bold">Texto / Llamado a la Acción en el Botón</label>
                        <input type="text" 
                               name="telegram_button_text" 
                               value="{{ $settings['telegram_button_text'] ?? '¿Problemas con la descarga? Únete a nuestro Canal de Telegram' }}" 
                               placeholder="¿Problemas con la descarga? Únete a nuestro Canal de Telegram" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-sans focus:border-sky-500">
                        <span class="text-[10px] text-gray-500 font-sans mt-1 block">Texto visible en el botón animado que invita al usuario a unirse al canal.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: Global SEO & Meta -->
        <div x-show="activeTab === 'seo'" class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="border-b border-[#232936] pb-4">
                <h2 class="text-base font-bold text-white font-sans flex items-center gap-2">
                    <i data-lucide="globe" class="w-5 h-5 text-blue-400"></i> Metadatos Globales & Posicionamiento en Buscadores
                </h2>
                <p class="text-xs text-gray-400 font-sans mt-0.5">Etiquetas OpenGraph, Meta Títulos y Palabras Clave por defecto para indexación en Google.</p>
            </div>

            <div class="space-y-4 font-mono text-xs">
                <div>
                    <label class="block text-gray-400 mb-1">Título Global del Sitio (Title Tag)</label>
                    <input type="text" name="seo_meta_title" value="{{ $settings['seo_meta_title'] ?? 'ROMHUB — Biblioteca Premium de Videojuegos' }}" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-sans">
                </div>

                <div>
                    <label class="block text-gray-400 mb-1">Descripción Global (Meta Description)</label>
                    <textarea name="seo_meta_description" rows="3" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-sans">{{ $settings['seo_meta_description'] ?? 'Biblioteca de preservación de videojuegos para 20 consolas.' }}</textarea>
                </div>

                <div>
                    <label class="block text-gray-400 mb-1">Palabras Clave (Keywords)</label>
                    <input type="text" name="seo_keywords" value="{{ $settings['seo_keywords'] ?? 'roms, emuladores, ps2, switch, gamecube, iso' }}" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-sans">
                </div>

                <!-- Google Search Console & Bing Verification -->
                <div class="pt-4 border-t border-[#232936] grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-[#0A0C0F] border border-[#232936] rounded-xl space-y-2">
                        <label class="block text-white font-bold flex items-center gap-1.5 font-sans">
                            <i data-lucide="search" class="w-4 h-4 text-blue-400"></i> Google Search Console Verification
                        </label>
                        <p class="text-[11px] text-gray-400 leading-normal font-sans">Ingresa tu código o meta-tag de verificación de Google (se insertará automáticamente en el &lt;head&gt; de todas las páginas).</p>
                        <input type="text" name="google_site_verification" value="{{ $settings['google_site_verification'] ?? '' }}" placeholder="Ej: dK8F9s7a6g5h4j3k2l1..." class="w-full bg-[#11141A] border border-[#232936] rounded-lg p-2.5 text-white font-mono text-xs">
                    </div>

                    <div class="p-4 bg-[#0A0C0F] border border-[#232936] rounded-xl space-y-2">
                        <label class="block text-white font-bold flex items-center gap-1.5 font-sans">
                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i> Bing Webmaster Verification
                        </label>
                        <p class="text-[11px] text-gray-400 leading-normal font-sans">Código de verificación para Microsoft Bing y Yahoo Search (etiqueta msvalidate.01).</p>
                        <input type="text" name="bing_site_verification" value="{{ $settings['bing_site_verification'] ?? '' }}" placeholder="Ej: 9C8B7A6D5E4F3G2H1..." class="w-full bg-[#11141A] border border-[#232936] rounded-lg p-2.5 text-white font-mono text-xs">
                    </div>
                </div>

                <!-- Live Dynamic Sitemaps & Search Console Links -->
                <div class="pt-4 border-t border-[#232936] p-4 bg-[#0A0C0F] border border-blue-500/20 rounded-xl space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-white font-sans flex items-center gap-2">
                                <i data-lucide="file-code" class="w-4 h-4 text-blue-400"></i> Sitemaps XML Dinámicos & Robots.txt
                            </h3>
                            <p class="text-[11px] text-gray-400 font-sans">Generados en tiempo real con todas las ROMs, consolas, sagas, géneros y páginas.</p>
                        </div>
                        <a href="https://search.google.com/search-console" target="_blank" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-sans font-bold flex items-center gap-1.5 transition-colors w-fit">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Abrir Google Search Console
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 font-mono text-xs">
                        <a href="{{ url('/sitemap.xml') }}" target="_blank" class="p-2.5 rounded-lg bg-[#11141A] border border-[#232936] hover:border-blue-500/50 flex items-center justify-between group transition-colors">
                            <span class="text-blue-400 group-hover:text-blue-300 font-bold">/sitemap.xml</span>
                            <span class="text-[10px] text-emerald-400 bg-emerald-950/60 px-1.5 py-0.5 rounded">Índice Principal</span>
                        </a>
                        <a href="{{ url('/sitemap-games.xml') }}" target="_blank" class="p-2.5 rounded-lg bg-[#11141A] border border-[#232936] hover:border-blue-500/50 flex items-center justify-between group transition-colors">
                            <span class="text-gray-300 group-hover:text-white">/sitemap-games.xml</span>
                            <span class="text-[10px] text-gray-500">ROMs & Posts</span>
                        </a>
                        <a href="{{ url('/sitemap-consoles.xml') }}" target="_blank" class="p-2.5 rounded-lg bg-[#11141A] border border-[#232936] hover:border-blue-500/50 flex items-center justify-between group transition-colors">
                            <span class="text-gray-300 group-hover:text-white">/sitemap-consoles.xml</span>
                            <span class="text-[10px] text-gray-500">Consolas</span>
                        </a>
                        <a href="{{ url('/sitemap-genres.xml') }}" target="_blank" class="p-2.5 rounded-lg bg-[#11141A] border border-[#232936] hover:border-blue-500/50 flex items-center justify-between group transition-colors">
                            <span class="text-gray-300 group-hover:text-white">/sitemap-genres.xml</span>
                            <span class="text-[10px] text-gray-500">Categorías</span>
                        </a>
                        <a href="{{ url('/sitemap-collections.xml') }}" target="_blank" class="p-2.5 rounded-lg bg-[#11141A] border border-[#232936] hover:border-blue-500/50 flex items-center justify-between group transition-colors">
                            <span class="text-gray-300 group-hover:text-white">/sitemap-collections.xml</span>
                            <span class="text-[10px] text-gray-500">Sagas</span>
                        </a>
                        <a href="{{ url('/robots.txt') }}" target="_blank" class="p-2.5 rounded-lg bg-[#11141A] border border-[#232936] hover:border-blue-500/50 flex items-center justify-between group transition-colors">
                            <span class="text-gray-300 group-hover:text-white">/robots.txt</span>
                            <span class="text-[10px] text-gray-500">Rastreadores</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: Google Gemini AI Automation (2.5 / 3.0+) -->
        <div x-show="activeTab === 'ai'" class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#232936] pb-4">
                <div>
                    <h2 class="text-base font-bold text-white font-sans flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-5 h-5 text-purple-400"></i> Google Gemini AI (Versiones 2.5 y 3.0+)
                    </h2>
                    <p class="text-xs text-gray-400 font-sans mt-0.5">Integración oficial con la API de Google Gemini para autocompletar descripciones, metatags SEO y fichas técnicas con 1 clic.</p>
                </div>

                <!-- Live Test Gemini Connection Button -->
                <button type="button" @click="testGeminiConnection()" :disabled="testingGemini" class="px-4 py-2 rounded-xl bg-purple-600/20 hover:bg-purple-600 text-purple-300 hover:text-white border border-purple-500/30 text-xs font-mono font-bold transition-all flex items-center gap-2">
                    <i data-lucide="activity" class="w-4 h-4" :class="testingGemini ? 'animate-spin' : ''"></i>
                    <span x-text="testingGemini ? 'Probando Gemini...' : 'Probar Gemini en Vivo'"></span>
                </button>
            </div>

            <!-- Gemini Test Result Banner -->
            <template x-if="geminiTestResult">
                <div :class="geminiTestResult.success ? 'bg-purple-950/80 border-purple-500/30 text-purple-300' : 'bg-rose-950/80 border-rose-500/30 text-rose-300'" class="p-4 rounded-xl border text-xs font-mono flex items-center gap-2">
                    <i :data-lucide="geminiTestResult.success ? 'check-circle' : 'alert-triangle'" class="w-4 h-4 shrink-0"></i>
                    <span x-text="geminiTestResult.message"></span>
                </div>
            </template>

            <input type="hidden" name="ai_provider" value="gemini">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 font-mono text-xs">
                <div>
                    <label class="block text-gray-400 mb-1">Modelo de Google Gemini</label>
                    <select name="ai_model" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                        <option value="gemini-2.5-flash" {{ ($settings['ai_model'] ?? 'gemini-2.5-flash') === 'gemini-2.5-flash' ? 'selected' : '' }}>Google Gemini 2.5 Flash (Recomendado — Ultra Rápido)</option>
                        <option value="gemini-2.5-pro" {{ ($settings['ai_model'] ?? '') === 'gemini-2.5-pro' ? 'selected' : '' }}>Google Gemini 2.5 Pro (Máximo Razonamiento)</option>
                        <option value="gemini-3.0-flash" {{ ($settings['ai_model'] ?? '') === 'gemini-3.0-flash' ? 'selected' : '' }}>Google Gemini 3.0 Flash (Nueva Generación 3.0)</option>
                        <option value="gemini-3.0-pro" {{ ($settings['ai_model'] ?? '') === 'gemini-3.0-pro' ? 'selected' : '' }}>Google Gemini 3.0 Pro (Máxima Potencia 3.0)</option>
                        <option value="gemini-2.0-flash" {{ ($settings['ai_model'] ?? '') === 'gemini-2.0-flash' ? 'selected' : '' }}>Google Gemini 2.0 Flash</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-400 mb-1">Temperatura (Creatividad 0.0 a 1.0)</label>
                    <input type="text" name="ai_temperature" value="{{ $settings['ai_temperature'] ?? '0.7' }}" placeholder="0.7" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                </div>

                <div class="sm:col-span-2 space-y-1">
                    <div class="flex items-center justify-between">
                        <label class="block text-purple-400 font-bold">Google AI Studio API Key</label>
                        <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" class="text-xs text-blue-400 hover:underline flex items-center gap-1 font-sans">
                            Obtener clave en Google AI Studio <i data-lucide="external-link" class="w-3 h-3"></i>
                        </a>
                    </div>
                    <input type="password" name="ai_api_key" value="{{ $settings['ai_api_key'] ?? '' }}" placeholder="AIzaSy..." class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                    <p class="text-[11px] text-gray-500 font-sans">La clave se almacena de forma segura y se utiliza exclusivamente para llamadas server-side autenticadas hacia los endpoints oficiales de Google Gemini.</p>
                </div>
            </div>
        </div>

        <!-- TAB 4: Storage (Cloudflare R2 / AWS S3) -->
        <div x-show="activeTab === 'storage'" class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#232936] pb-4">
                <div>
                    <h2 class="text-base font-bold text-white font-sans flex items-center gap-2">
                        <i data-lucide="hard-drive" class="w-5 h-5 text-blue-400"></i> Almacenamiento Compatible con Cloudflare R2 / S3
                    </h2>
                    <p class="text-xs text-gray-400 font-sans mt-0.5">Configuración de credenciales de bucket de alta velocidad para ROMs y archivos pesados.</p>
                </div>

                <!-- Live Test Connection Button -->
                <button type="button" @click="testStorageConnection()" :disabled="testingStorage" class="px-4 py-2 rounded-xl bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white border border-emerald-500/30 text-xs font-mono font-bold transition-all flex items-center gap-2">
                    <i data-lucide="activity" class="w-4 h-4" :class="testingStorage ? 'animate-spin' : ''"></i>
                    <span x-text="testingStorage ? 'Probando Conexión...' : 'Probar Conexión en Vivo'"></span>
                </button>
            </div>

            <!-- Test Result Banner -->
            <template x-if="testResult">
                <div :class="testResult.success ? 'bg-emerald-950/80 border-emerald-500/30 text-emerald-300' : 'bg-rose-950/80 border-rose-500/30 text-rose-300'" class="p-4 rounded-xl border text-xs font-mono flex items-center gap-2">
                    <i :data-lucide="testResult.success ? 'check-circle' : 'alert-triangle'" class="w-4 h-4 shrink-0"></i>
                    <span x-text="testResult.message"></span>
                </div>
            </template>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 font-mono text-xs">
                <div>
                    <label class="block text-gray-400 mb-1">R2 / S3 Access Key ID</label>
                    <input type="text" name="r2_access_key_id" value="{{ $settings['r2_access_key_id'] ?? '' }}" placeholder="f9302ba14a..." class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                </div>

                <div>
                    <label class="block text-gray-400 mb-1">R2 / S3 Secret Access Key</label>
                    <input type="password" name="r2_secret_access_key" value="{{ $settings['r2_secret_access_key'] ?? '' }}" placeholder="••••••••••••••••" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                </div>

                <div>
                    <label class="block text-gray-400 mb-1">Nombre del Bucket</label>
                    <input type="text" name="r2_bucket" value="{{ $settings['r2_bucket'] ?? 'romhub-production-vault' }}" placeholder="romhub-production-vault" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                </div>

                <div>
                    <label class="block text-gray-400 mb-1">Región (Para R2 use 'auto')</label>
                    <input type="text" name="r2_region" value="{{ $settings['r2_region'] ?? 'auto' }}" placeholder="auto / us-east-1" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-gray-400 mb-1">Endpoint Personalizado (Cloudflare R2 Endpoint)</label>
                    <input type="text" name="r2_endpoint" value="{{ $settings['r2_endpoint'] ?? '' }}" placeholder="https://<account-id>.r2.cloudflarestorage.com" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-gray-400 mb-1">URL Pública del CDN / Dominio Personalizado</label>
                    <input type="text" name="r2_public_url" value="{{ $settings['r2_public_url'] ?? '' }}" placeholder="https://pub-vault.romhub.io" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                </div>
            </div>
        </div>

        <!-- TAB 5: Scripts, Analytics & Custom Code Injection -->
        <div x-show="activeTab === 'custom_code'" class="space-y-6">

            <!-- Banner Informativo -->
            <div class="bg-gradient-to-r from-emerald-950/40 via-[#11141A] to-[#11141A] border border-emerald-500/30 rounded-2xl p-5 shadow-xl flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center shrink-0">
                    <i data-lucide="code-2" class="w-5 h-5 text-emerald-400"></i>
                </div>
                <div class="space-y-1">
                    <h2 class="text-sm font-bold text-white font-sans flex items-center gap-2">
                        Inyección de Código Personalizado & Analítica Web
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Google Analytics / Pixel / Ads</span>
                    </h2>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Inserta tus etiquetas de seguimiento de forma segura sin tener que tocar archivos PHP. El código se inyecta directamente en las páginas públicas respetando el estándar técnico de Google, Meta y redes de publicidad.
                    </p>
                </div>
            </div>

            <!-- Field 1: Head Code -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                    <div>
                        <h3 class="text-xs font-mono font-bold uppercase text-white tracking-wider flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20 text-[10px]">&lt;head&gt;</span>
                            Código en la Cabecera (&lt;head&gt;)
                        </h3>
                        <p class="text-[11px] text-gray-400 font-mono mt-0.5">Se inserta justo antes de <code>&lt;/head&gt;</code>. Ideal para Google Analytics (gtag.js), Google Tag Manager, Meta Pixel, verificación de Google AdSense o scripts de tracking principales.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-mono text-gray-300 font-bold mb-1.5 flex items-center justify-between">
                        <span>Fragmento HTML / JavaScript para &lt;head&gt;:</span>
                        <span class="text-[10px] font-mono text-gray-500">Ejemplo: &lt;script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXX"&gt;&lt;/script&gt;</span>
                    </label>
                    <textarea name="custom_head_code" 
                              rows="8" 
                              placeholder="<!-- Google tag (gtag.js) -->&#10;<script async src=&quot;https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX&quot;></script>&#10;<script>&#10;  window.dataLayer = window.dataLayer || [];&#10;  function gtag(){dataLayer.push(arguments);}&#10;  gtag('js', new Date());&#10;  gtag('config', 'G-XXXXXXXXXX');&#10;</script>" 
                              class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-3.5 text-xs text-emerald-400 font-mono focus:outline-none focus:border-emerald-500 leading-relaxed selection:bg-emerald-900 selection:text-white">{{ $settings['custom_head_code'] ?? '' }}</textarea>
                </div>
            </div>

            <!-- Field 2: Body Open Code -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                    <div>
                        <h3 class="text-xs font-mono font-bold uppercase text-white tracking-wider flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-purple-500/10 text-purple-400 border border-purple-500/20 text-[10px]">&lt;body&gt;</span>
                            Código de Apertura (&lt;body&gt;)
                        </h3>
                        <p class="text-[11px] text-gray-400 font-mono mt-0.5">Se inserta inmediatamente después de la apertura de la etiqueta <code>&lt;body&gt;</code>. Utilizado habitualmente por el fragmento <code>&lt;noscript&gt;</code> de Google Tag Manager.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-mono text-gray-300 font-bold mb-1.5 flex items-center justify-between">
                        <span>Fragmento HTML / Noscript para inicio de &lt;body&gt;:</span>
                        <span class="text-[10px] font-mono text-gray-500">Ejemplo: &lt;noscript&gt;&lt;iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXX" ...&gt;</span>
                    </label>
                    <textarea name="custom_body_open_code" 
                              rows="5" 
                              placeholder="<!-- Google Tag Manager (noscript) -->&#10;<noscript><iframe src=&quot;https://www.googletagmanager.com/ns.html?id=GTM-XXXXXXX&quot;&#10;height=&quot;0&quot; width=&quot;0&quot; style=&quot;display:none;visibility:hidden&quot;></iframe></noscript>" 
                              class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-3.5 text-xs text-purple-400 font-mono focus:outline-none focus:border-purple-500 leading-relaxed selection:bg-purple-900 selection:text-white">{{ $settings['custom_body_open_code'] ?? '' }}</textarea>
                </div>
            </div>

            <!-- Field 3: Footer Code -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                    <div>
                        <h3 class="text-xs font-mono font-bold uppercase text-white tracking-wider flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px]">&lt;/body&gt;</span>
                            Código de Pie de Página (Antes de &lt;/body&gt;)
                        </h3>
                        <p class="text-[11px] text-gray-400 font-mono mt-0.5">Se inserta justo antes de cerrar <code>&lt;/body&gt;</code>. Ideal para widgets de soporte (Crisp, Tawk.to, WhatsApp), scripts de anuncios diferidos, popunders o JavaScript de analítica secundaria.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-mono text-gray-300 font-bold mb-1.5 flex items-center justify-between">
                        <span>Fragmento HTML / JavaScript para pie de página:</span>
                        <span class="text-[10px] font-mono text-gray-500">Ejemplo: Scripts de Chat, Anuncios o Analytics diferido</span>
                    </label>
                    <textarea name="custom_footer_code" 
                              rows="6" 
                              placeholder="<!-- Widget de Chat o Scripts diferidos -->&#10;<script>&#10;  // Tu script personalizado&#10;</script>" 
                              class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-3.5 text-xs text-amber-400 font-mono focus:outline-none focus:border-amber-500 leading-relaxed selection:bg-amber-900 selection:text-white">{{ $settings['custom_footer_code'] ?? '' }}</textarea>
                </div>
            </div>

        </div>

        <!-- TAB 6: Telegram Bot & Auto-Publishing -->
        <div x-show="activeTab === 'telegram'" class="space-y-6">

            <!-- Card 1: Bot Credentials & Channel -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 shadow-xl space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#232936] pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/20">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.135-.954l11.566-4.458c.538-.196 1.006.128.832.946z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-mono font-bold uppercase text-white tracking-wider flex items-center gap-2">
                                Credenciales del Bot & Canal Oficial
                            </h3>
                            <p class="text-xs text-gray-400 font-sans mt-0.5">Conecta tu Bot de Telegram para publicar automáticamente novedades y alimentar la comunidad.</p>
                        </div>
                    </div>

                    <!-- Botón de Prueba en Vivo -->
                    <button type="button" 
                            @click="testTelegramConnection()"
                            :disabled="testingTelegram"
                            class="px-4 py-2 rounded-xl bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 border border-sky-500/30 font-mono text-xs font-bold flex items-center gap-2 transition-all cursor-pointer disabled:opacity-50 shadow-sm shrink-0">
                        <template x-if="!testingTelegram">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.135-.954l11.566-4.458c.538-.196 1.006.128.832.946z"/></svg>
                                <span>Probar Conexión con Telegram</span>
                            </span>
                        </template>
                        <template x-if="testingTelegram">
                            <span class="flex items-center gap-2">
                                <i data-lucide="loader" class="w-4 h-4 animate-spin text-sky-400"></i>
                                <span>Conectando con Telegram...</span>
                            </span>
                        </template>
                    </button>
                </div>

                <!-- Test Connection Live Result Banner -->
                <div x-show="telegramTestResult" class="space-y-2">
                    <template x-if="telegramTestResult?.success">
                        <div class="p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/40 text-emerald-300 text-xs font-mono flex items-start gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5"></i>
                            <div>
                                <strong class="font-bold text-emerald-200">¡Conexión Exitosa!</strong>
                                <p class="mt-0.5 text-emerald-300/90" x-text="telegramTestResult?.message"></p>
                            </div>
                        </div>
                    </template>
                    <template x-if="telegramTestResult && !telegramTestResult.success">
                        <div class="p-4 rounded-xl bg-red-950/40 border border-red-500/40 text-red-300 text-xs font-mono flex items-start gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-red-400 shrink-0 mt-0.5"></i>
                            <div>
                                <strong class="font-bold text-red-200">Error de Configuración</strong>
                                <p class="mt-0.5 text-red-300/90" x-text="telegramTestResult?.message"></p>
                                <p class="text-[11px] text-red-400 mt-1 font-sans" x-text="telegramTestResult?.error"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 font-mono text-xs">
                    <!-- Telegram Bot Token -->
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block font-bold text-gray-200 flex items-center justify-between">
                            <span class="flex items-center gap-2 text-sky-400">
                                <i data-lucide="key" class="w-3.5 h-3.5"></i> Token del Bot de Telegram (BotFather)
                            </span>
                            <a href="https://t.me/BotFather" target="_blank" class="text-[11px] text-sky-400 hover:underline font-sans flex items-center gap-1">
                                Crear bot con @BotFather <i data-lucide="external-link" class="w-3 h-3"></i>
                            </a>
                        </label>
                        <input type="password" 
                               name="telegram_bot_token" 
                               value="{{ $settings['telegram_bot_token'] ?? '' }}"
                               placeholder="ej: 7123456789:AAH7XyZ..." 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-3 text-xs text-white font-mono focus:border-sky-500">
                        <p class="text-[10px] text-gray-500 font-sans">Token privado que te entrega @BotFather tras crear el bot. Nunca lo compartas.</p>
                    </div>

                    <!-- Channel ID or Username -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-gray-200 flex items-center gap-2">
                            <i data-lucide="hash" class="w-3.5 h-3.5 text-sky-400"></i> ID o Username del Canal de Publicación
                        </label>
                        <input type="text" 
                               name="telegram_channel_id" 
                               value="{{ $settings['telegram_channel_id'] ?? '' }}"
                               placeholder="ej: @cdromsdown o -100192837465" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-3 text-xs text-sky-300 font-mono focus:border-sky-500">
                        <p class="text-[10px] text-gray-500 font-sans">Username público del canal (ej: <code>@micanal</code>) o su ID numérico. <strong>El bot debe ser Administrador del canal</strong>.</p>
                    </div>

                    <!-- Public Channel URL (para visitantes) -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-gray-200 flex items-center gap-2">
                            <i data-lucide="link" class="w-3.5 h-3.5 text-sky-400"></i> Enlace Público del Canal (Para Botones en la Web)
                        </label>
                        <input type="text" 
                               name="telegram_channel_url" 
                               value="{{ $settings['telegram_channel_url'] ?? 'https://t.me/cdromsdown' }}"
                               placeholder="https://t.me/cdromsdown" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-3 text-xs text-white font-mono focus:border-sky-500">
                        <p class="text-[10px] text-gray-500 font-sans">Enlace al que se redirige a los visitantes al hacer clic en el botón de Telegram en la web.</p>
                    </div>

                    <!-- Button Text on Game Detail -->
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block font-bold text-gray-200 flex items-center gap-2">
                            <i data-lucide="type" class="w-3.5 h-3.5 text-gray-400"></i> Texto / Tooltip del Botón en Ficha de Juego
                        </label>
                        <input type="text" 
                               name="telegram_button_text" 
                               value="{{ $settings['telegram_button_text'] ?? '¿Problemas con la descarga o buscas otra ROM? Únete a nuestro Telegram' }}"
                               placeholder="¿Problemas con la descarga o buscas otra ROM? Únete a nuestro Telegram" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-3 text-xs text-white font-sans focus:border-sky-500">
                    </div>
                </div>
            </div>

            <!-- Card 2: Auto-Publishing & Message Template -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 shadow-xl space-y-6">
                <div class="flex items-center justify-between border-b border-[#232936] pb-4">
                    <div>
                        <h3 class="text-sm font-mono font-bold uppercase text-white tracking-wider flex items-center gap-2">
                            <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i> Publicación Automática & Plantilla
                        </h3>
                        <p class="text-xs text-gray-400 font-sans mt-0.5">Controla cuándo se envía a Telegram y personaliza el formato del mensaje.</p>
                    </div>

                    <!-- Toggle Switch -->
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="telegram_auto_publish" value="1" {{ ($settings['telegram_auto_publish'] ?? '0') === '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-500"></div>
                        <span class="ml-3 text-xs font-mono font-bold text-gray-300">Publicar al crear ROM</span>
                    </label>
                </div>

                <div class="space-y-4 font-mono text-xs">
                    <div>
                        <label class="block font-bold text-gray-200 mb-2 flex items-center justify-between">
                            <span>Plantilla del Mensaje de Telegram:</span>
                            <span class="text-[10px] text-gray-500 font-sans">Soporta formato HTML (<code>&lt;b&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;code&gt;</code>)</span>
                        </label>
                        <textarea name="telegram_message_template" 
                                  rows="7" 
                                  class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-3.5 text-xs text-sky-200 font-mono focus:border-sky-500 leading-relaxed">{{ $settings['telegram_message_template'] ?? "🕹️ <b>¡NUEVA ROM PUBLICADA EN EL VAULT!</b>\n\n🎮 <b>Título:</b> {title}\n💾 <b>Consola:</b> {console}\n🌍 <b>Región:</b> {region}\n📦 <b>Tamaño:</b> {size}\n📅 <b>Año:</b> {year}\n\n⚡ <i>Archivo verificado, limpio y con descarga directa de alta velocidad.</i>\n\n🔗 <b>Ficha & Enlaces:</b> {url}" }}</textarea>
                    </div>

                    <!-- Variables Chips / Pills -->
                    <div class="p-3.5 rounded-xl bg-[#0A0C0F] border border-[#232936] space-y-2">
                        <div class="text-[11px] font-bold text-gray-400 font-sans">Variables dinámicas disponibles para usar en la plantilla:</div>
                        <div class="flex flex-wrap gap-2 text-[11px]">
                            <span class="px-2 py-0.5 rounded bg-sky-950/60 border border-sky-500/30 text-sky-300 font-mono">{title}</span>
                            <span class="px-2 py-0.5 rounded bg-sky-950/60 border border-sky-500/30 text-sky-300 font-mono">{console}</span>
                            <span class="px-2 py-0.5 rounded bg-sky-950/60 border border-sky-500/30 text-sky-300 font-mono">{region}</span>
                            <span class="px-2 py-0.5 rounded bg-sky-950/60 border border-sky-500/30 text-sky-300 font-mono">{size}</span>
                            <span class="px-2 py-0.5 rounded bg-sky-950/60 border border-sky-500/30 text-sky-300 font-mono">{year}</span>
                            <span class="px-2 py-0.5 rounded bg-sky-950/60 border border-sky-500/30 text-sky-300 font-mono">{developer}</span>
                            <span class="px-2 py-0.5 rounded bg-sky-950/60 border border-sky-500/30 text-sky-300 font-mono">{url}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- TAB 7: Publicidad & Monetización (Ads) -->
        <div x-show="activeTab === 'ads'" class="space-y-6">

            <!-- Card 1: Master Control & Head Script -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 shadow-xl space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#232936] pb-4">
                    <div>
                        <h3 class="text-sm font-mono font-bold uppercase text-white tracking-wider flex items-center gap-2">
                            <i data-lucide="power" class="w-4 h-4 text-amber-400"></i> Interruptor Maestro de Publicidad
                        </h3>
                        <p class="text-xs text-gray-400 font-sans mt-0.5">Controla la activación global de anuncios. Si está apagado, ningún anuncio se mostrará en el portal.</p>
                    </div>

                    <!-- Master Switch -->
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="ads_enabled" value="0">
                        <input type="checkbox" name="ads_enabled" value="1" {{ ($settings['ads_enabled'] ?? '0') === '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                        <span class="ml-3 text-xs font-mono font-bold text-gray-300">Publicidad Activa</span>
                    </label>
                </div>

                <!-- Global Head Script -->
                <div class="space-y-2">
                    <label class="block font-bold text-gray-200 text-xs flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i data-lucide="code" class="w-3.5 h-3.5 text-amber-400"></i> Script Global en &lt;head&gt; (Google AdSense / Redes Publicitarias)
                        </span>
                        <span class="text-[10px] text-gray-500 font-sans font-normal">Se inserta automáticamente en la cabecera de todas las páginas</span>
                    </label>
                    <textarea name="ads_global_header" 
                              rows="3" 
                              placeholder='<!-- Ejemplo: &lt;script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-XXXXXXXXXXXXXXXX" crossorigin="anonymous"&gt;&lt;/script&gt; -->'
                              class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-3 text-xs text-amber-200 font-mono focus:border-amber-500 leading-relaxed">{{ $settings['ads_global_header'] ?? '' }}</textarea>
                    <p class="text-[10px] text-gray-500 font-sans">Pega aquí el script principal o etiqueta de verificación de Google AdSense u otras redes publicitarias.</p>
                </div>
            </div>

            <!-- Card 2: Slot 1 - Página de Descarga (Debajo del Botón) -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#232936] pb-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-amber-500/20 text-amber-400 border border-amber-500/30 font-mono font-black text-xs flex items-center justify-center">1</span>
                            <h3 class="text-xs font-mono font-bold uppercase text-white tracking-wider">Publicidad en Botón de Descarga</h3>
                            <span class="px-2 py-0.5 rounded-full bg-red-950/50 text-red-400 border border-red-500/30 text-[10px] font-mono font-bold">Máximo CTR</span>
                        </div>
                        <p class="text-[11px] text-gray-400 font-sans">Se ubica en la pantalla de descarga (<code>web.download</code>), exactamente debajo de la lista de servidores y del botón "Descargar Ahora".</p>
                    </div>

                    <!-- Slot 1 Switch -->
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="hidden" name="ad_download_enabled" value="0">
                        <input type="checkbox" name="ad_download_enabled" value="1" {{ ($settings['ad_download_enabled'] ?? '0') === '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                        <span class="ml-2.5 text-xs font-mono font-bold text-gray-300">Mostrar</span>
                    </label>
                </div>

                <!-- Guidance & Formats -->
                <div class="flex flex-wrap items-center gap-2 text-[10px] font-mono text-gray-400">
                    <span class="text-gray-500">Formatos recomendados:</span>
                    <span class="px-2 py-0.5 rounded bg-[#0A0C0F] border border-[#232936] text-amber-300">728x90 Leaderboard</span>
                    <span class="px-2 py-0.5 rounded bg-[#0A0C0F] border border-[#232936] text-amber-300">300x250 Rectángulo</span>
                    <span class="px-2 py-0.5 rounded bg-[#0A0C0F] border border-[#232936] text-blue-300">Display Responsivo AdSense</span>
                </div>

                <div class="space-y-1.5">
                    <label class="block font-bold text-gray-200 text-xs">Código HTML / JavaScript / AdSense:</label>
                    <textarea name="ad_download_code" 
                              rows="4" 
                              placeholder="<!-- Pega aquí el bloque de anuncios &lt;ins class='adsbygoogle'...&gt; o tu banner HTML -->"
                              class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-3 text-xs text-amber-200 font-mono focus:border-amber-500 leading-relaxed">{{ $settings['ad_download_code'] ?? '' }}</textarea>
                </div>
            </div>

            <!-- Card 3: Slot 2 - Portada / Home -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#232936] pb-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-amber-500/20 text-amber-400 border border-amber-500/30 font-mono font-black text-xs flex items-center justify-center">2</span>
                            <h3 class="text-xs font-mono font-bold uppercase text-white tracking-wider">Publicidad en Portada (Home)</h3>
                            <span class="px-2 py-0.5 rounded-full bg-blue-950/50 text-blue-400 border border-blue-500/30 text-[10px] font-mono font-bold">Alta Visibilidad</span>
                        </div>
                        <p class="text-[11px] text-gray-400 font-sans">Se ubica en la página principal (<code>web.home</code>), entre el escaparate Hero y el carrusel de las 20 consolas.</p>
                    </div>

                    <!-- Slot 2 Switch -->
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="hidden" name="ad_home_enabled" value="0">
                        <input type="checkbox" name="ad_home_enabled" value="1" {{ ($settings['ad_home_enabled'] ?? '0') === '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                        <span class="ml-2.5 text-xs font-mono font-bold text-gray-300">Mostrar</span>
                    </label>
                </div>

                <!-- Guidance & Formats -->
                <div class="flex flex-wrap items-center gap-2 text-[10px] font-mono text-gray-400">
                    <span class="text-gray-500">Formatos recomendados:</span>
                    <span class="px-2 py-0.5 rounded bg-[#0A0C0F] border border-[#232936] text-amber-300">970x90 Super Leaderboard</span>
                    <span class="px-2 py-0.5 rounded bg-[#0A0C0F] border border-[#232936] text-amber-300">728x90 Leaderboard</span>
                    <span class="px-2 py-0.5 rounded bg-[#0A0C0F] border border-[#232936] text-blue-300">Display Responsivo AdSense</span>
                </div>

                <div class="space-y-1.5">
                    <label class="block font-bold text-gray-200 text-xs">Código HTML / JavaScript / AdSense:</label>
                    <textarea name="ad_home_code" 
                              rows="4" 
                              placeholder="<!-- Pega aquí el bloque publicitario de la Home -->"
                              class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-3 text-xs text-amber-200 font-mono focus:border-amber-500 leading-relaxed">{{ $settings['ad_home_code'] ?? '' }}</textarea>
                </div>
            </div>

            <!-- Card 4: Slot 3 - Ficha del Videojuego (Game Detail) -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#232936] pb-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-amber-500/20 text-amber-400 border border-amber-500/30 font-mono font-black text-xs flex items-center justify-center">3</span>
                            <h3 class="text-xs font-mono font-bold uppercase text-white tracking-wider">Publicidad en Ficha del Videojuego (Game Detail)</h3>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-950/50 text-emerald-400 border border-emerald-500/30 text-[10px] font-mono font-bold">Tráfico Orgánico</span>
                        </div>
                        <p class="text-[11px] text-gray-400 font-sans">Se ubica en la ficha de cada título (<code>web.game-detail</code>), directamente debajo de los botones de descarga y acceso a emuladores.</p>
                    </div>

                    <!-- Slot 3 Switch -->
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="hidden" name="ad_game_detail_enabled" value="0">
                        <input type="checkbox" name="ad_game_detail_enabled" value="1" {{ ($settings['ad_game_detail_enabled'] ?? '0') === '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                        <span class="ml-2.5 text-xs font-mono font-bold text-gray-300">Mostrar</span>
                    </label>
                </div>

                <!-- Guidance & Formats -->
                <div class="flex flex-wrap items-center gap-2 text-[10px] font-mono text-gray-400">
                    <span class="text-gray-500">Formatos recomendados:</span>
                    <span class="px-2 py-0.5 rounded bg-[#0A0C0F] border border-[#232936] text-amber-300">336x280 Rectángulo Grande</span>
                    <span class="px-2 py-0.5 rounded bg-[#0A0C0F] border border-[#232936] text-amber-300">300x250 Rectángulo</span>
                    <span class="px-2 py-0.5 rounded bg-[#0A0C0F] border border-[#232936] text-blue-300">Display In-Article / Responsivo</span>
                </div>

                <div class="space-y-1.5">
                    <label class="block font-bold text-gray-200 text-xs">Código HTML / JavaScript / AdSense:</label>
                    <textarea name="ad_game_detail_code" 
                              rows="4" 
                              placeholder="<!-- Pega aquí el bloque publicitario de la ficha técnica -->"
                              class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-3 text-xs text-amber-200 font-mono focus:border-amber-500 leading-relaxed">{{ $settings['ad_game_detail_code'] ?? '' }}</textarea>
                </div>
            </div>

        </div>

        <!-- Save Button -->
        <div class="flex justify-end pt-2">
            <button type="submit" class="px-8 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-black text-xs uppercase tracking-wider transition-all shadow-xl shadow-blue-600/30 flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Guardar Todas las Configuraciones
            </button>
        </div>

    </form>

</div>

<script>
function settingsForm() {
    return {
        activeTab: (new URLSearchParams(window.location.search).get('tab')) || 'general',
        testingStorage: false,
        testResult: null,
        testingGemini: false,
        geminiTestResult: null,
        testingTelegram: false,
        telegramTestResult: null,
        
        // Logo & Branding interactive state
        logoType: {!! json_encode($settings['site_logo_type'] ?? 'icon_text') !!},
        selectedIcon: {!! json_encode($settings['site_logo_icon'] ?? 'disc') !!},
        logoPrefix: {!! json_encode($settings['site_logo_prefix'] ?? 'ROM') !!},
        logoSuffix: {!! json_encode($settings['site_logo_suffix'] ?? 'HUB') !!},
        tagline: {!! json_encode($settings['site_tagline'] ?? 'Preservation Vault') !!},
        logoUrl: {!! json_encode($settings['site_logo_url'] ?? '') !!},
        faviconUrl: {!! json_encode($settings['site_favicon_url'] ?? '') !!},
        footerTitle: {!! json_encode($settings['footer_title'] ?? '') !!},
        footerDescription: {!! json_encode($settings['footer_description'] ?? 'Preservación digital conforme a estándares No-Intro & Redump.') !!},
        footerCopyright: {!! json_encode($settings['footer_copyright'] ?? '') !!},
        iconSearch: '',
        
        availableIcons: [
            'disc', 'gamepad-2', 'gamepad', 'sparkles', 'zap', 'cpu', 'layers', 'shield', 
            'shield-check', 'terminal', 'globe', 'box', 'flame', 'crosshair', 'ghost', 'joystick', 
            'swords', 'database', 'hard-drive', 'tv', 'server', 'code', 'compass', 'radio', 
            'rocket', 'play', 'infinity', 'award', 'crown', 'star', 'heart', 'trophy', 
            'target', 'gem', 'atom', 'activity', 'package', 'monitor', 'power', 'circle-dot', 
            'radar', 'cast', 'wifi', 'headphones', 'volume-2', 'wrench', 'key', 'lock'
        ],
        
        selectIcon(icon) {
            this.selectedIcon = icon;
            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },
        
        filteredIcons() {
            if (!this.iconSearch.trim()) return this.availableIcons;
            return this.availableIcons.filter(i => i.toLowerCase().includes(this.iconSearch.toLowerCase().trim()));
        },
        
        testStorageConnection() {
            this.testingStorage = true;
            this.testResult = null;
            fetch('{{ route('admin.settings.testStorage') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(r => r.json())
            .then(d => {
                this.testingStorage = false;
                this.testResult = d;
            })
            .catch(err => {
                this.testingStorage = false;
                this.testResult = { success: false, message: 'Error de red al intentar conectar con el servidor.' };
            });
        },

        testGeminiConnection() {
            this.testingGemini = true;
            this.geminiTestResult = null;
            const apiKey = document.querySelector('input[name="ai_api_key"]')?.value || '';
            const model = document.querySelector('select[name="ai_model"]')?.value || 'gemini-1.5-flash';

            fetch('{{ route('admin.settings.testGemini') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ ai_api_key: apiKey, ai_model: model })
            })
            .then(r => r.json())
            .then(d => {
                this.testingGemini = false;
                this.geminiTestResult = d;
            })
            .catch(err => {
                this.testingGemini = false;
                this.geminiTestResult = { success: false, message: 'Error de red al conectar con Google Gemini.' };
            });
        },

        testTelegramConnection() {
            this.testingTelegram = true;
            this.telegramTestResult = null;
            const token = document.querySelector('input[name="telegram_bot_token"]')?.value || '';
            const channelId = document.querySelector('input[name="telegram_channel_id"]')?.value || '';

            fetch('{{ route('admin.settings.testTelegram') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ telegram_bot_token: token, telegram_channel_id: channelId })
            })
            .then(r => r.json())
            .then(d => {
                this.testingTelegram = false;
                this.telegramTestResult = d;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            })
            .catch(err => {
                this.testingTelegram = false;
                this.telegramTestResult = { success: false, message: 'Error de red al conectar con el servidor para probar Telegram.', error: err.message };
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            });
        }
    };
}
</script>
@endsection
