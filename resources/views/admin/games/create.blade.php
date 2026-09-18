@extends('layouts.admin')

@section('title', 'Nuevo Videojuego — Administración ROMHUB')

@section('content')
<div class="space-y-6" x-data="gameCreateForm()">

    <!-- Top Breadcrumb & Actions -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs font-mono text-gray-500">
                <a href="{{ route('admin.games.index') }}" class="hover:text-gray-300">Catálogo</a>
                <span>/</span>
                <span class="text-gray-300">Crear Nuevo Título</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans">Indexar Nuevo Videojuego</h1>
        </div>
        
        <!-- Magic AI Suite Quick Action -->
        <div class="flex items-center gap-2">
            <button type="button" 
                    @click="autocompleteAllWithAi()" 
                    :disabled="allAiLoading"
                    class="px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white border border-purple-400/30 text-xs font-bold transition-all flex items-center gap-1.5 shadow-lg shadow-purple-600/20 cursor-pointer disabled:opacity-50">
                <i data-lucide="sparkles" class="w-4 h-4" x-show="!allAiLoading"></i>
                <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="allAiLoading"></i>
                <span x-text="allAiLoading ? 'Completando Todo con Gemini IA...' : '✨ Autocompletar TODO con IA'"></span>
            </button>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
    <div class="p-4 bg-rose-950/80 border border-rose-500/30 rounded-xl text-xs text-rose-300 space-y-1 font-sans">
        <p class="font-bold">Por favor corrige los siguientes errores:</p>
        @foreach($errors->all() as $err)
        <p>• {{ $err }}</p>
        @endforeach
    </div>
    @endif

    <form action="{{ route('admin.games.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: Primary Details & Rich Content (8 cols) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Main Game Info Box -->
                <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 space-y-4">
                    <h2 class="text-sm font-bold text-white font-sans uppercase tracking-wider border-b border-[#232936] pb-3 flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-blue-400"></i> Información Principal
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-mono text-gray-400 mb-1">Título del Videojuego *</label>
                            <input type="text" name="title" x-model="title" required placeholder="Ej. God of War II" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-3 text-sm text-white focus:outline-none focus:border-blue-500 font-sans">
                        </div>

                        <div>
                            <label class="block text-xs font-mono text-gray-400 mb-1">Slug URL (Opcional, se genera auto)</label>
                            <input type="text" name="slug" value="{{ old('slug') }}" placeholder="ej. god-of-war-ii" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-2.5 text-xs text-gray-300 focus:outline-none focus:border-blue-500 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-mono text-gray-400 mb-1">Ecosistema / Consola *</label>
                            <select id="consoleSelect" name="console_id" required class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl p-2.5 text-xs text-gray-200 focus:outline-none focus:border-blue-500 font-sans">
                                @foreach($consoles as $con)
                                <option value="{{ $con->id }}" {{ old('console_id') == $con->id ? 'selected' : '' }}>{{ $con->name }} ({{ $con->manufacturer }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Rich Interactive Description Editor with AI Writer -->
                    <div class="space-y-2 pt-2">
                        
                        <!-- Header: Label + Live Word Count + AI Generator Button -->
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#232936] pb-2">
                            <div class="flex items-center gap-2">
                                <label class="text-xs font-mono font-bold text-white flex items-center gap-1.5">
                                    <i data-lucide="book-open" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    <span>Sinopsis / Descripción Enriquecida</span>
                                </label>
                                
                                <!-- Word Counter Pill (~200 words goal) -->
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-mono font-bold transition-colors"
                                      :class="wordCount >= 180 ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : (wordCount > 0 ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'bg-[#0A0C0F] text-gray-500 border border-[#232936]')">
                                    <span x-text="wordCount"></span> / ~200 palabras
                                </span>
                            </div>

                            <!-- Magic AI Button for 200+ word synopsis -->
                            <button type="button" 
                                    @click="generateDescriptionWithAi()" 
                                    :disabled="descLoading"
                                    class="px-3 py-1.5 rounded-xl bg-purple-600/20 hover:bg-purple-600 text-purple-300 hover:text-white border border-purple-500/30 text-xs font-mono font-bold transition-all flex items-center gap-1.5 shadow-sm cursor-pointer disabled:opacity-50">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-400" x-show="!descLoading"></i>
                                <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="descLoading"></i>
                                <span x-text="descLoading ? 'Generando ~200 palabras...' : '✨ Generar Sinopsis IA (~200 palabras)'"></span>
                            </button>
                        </div>

                        <!-- Editor Container with Formatting Toolbar -->
                        <div class="rounded-2xl border border-[#232936] bg-[#0A0D0E] overflow-hidden focus-within:border-emerald-500/60 transition-colors shadow-inner">
                            
                            <!-- Toolbar Bar -->
                            <div class="bg-[#11141A] border-b border-[#232936] px-3 py-2 flex flex-wrap items-center justify-between gap-2">
                                
                                <!-- Mode Switcher: Write vs Preview -->
                                <div class="flex items-center gap-1 bg-[#0A0C0F] p-0.5 rounded-lg border border-[#232936] font-mono text-xs">
                                    <button type="button" 
                                            @click="editorTab = 'write'" 
                                            :class="editorTab === 'write' ? 'bg-[#1A2426] text-white font-bold' : 'text-gray-400 hover:text-white'"
                                            class="px-2.5 py-1 rounded-md transition-colors flex items-center gap-1">
                                        <i data-lucide="edit-3" class="w-3 h-3"></i>
                                        <span>Editor</span>
                                    </button>
                                    <button type="button" 
                                            @click="editorTab = 'preview'" 
                                            :class="editorTab === 'preview' ? 'bg-[#1A2426] text-emerald-400 font-bold' : 'text-gray-400 hover:text-white'"
                                            class="px-2.5 py-1 rounded-md transition-colors flex items-center gap-1">
                                        <i data-lucide="eye" class="w-3 h-3"></i>
                                        <span>Vista Previa</span>
                                    </button>
                                </div>

                                <!-- Quick Markdown Formatting Buttons (Enabled in Write Mode) -->
                                <div class="flex items-center gap-1 font-mono text-xs overflow-x-auto" x-show="editorTab === 'write'">
                                    <!-- Bold -->
                                    <button type="button" @click="insertFormatting('**', '**', 'texto en negrita')" class="p-1.5 rounded-lg hover:bg-[#1A2426] text-gray-300 hover:text-white transition-colors" title="Negrita (**texto**)">
                                        <i data-lucide="bold" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <!-- Italic -->
                                    <button type="button" @click="insertFormatting('*', '*', 'texto en cursiva')" class="p-1.5 rounded-lg hover:bg-[#1A2426] text-gray-300 hover:text-white transition-colors" title="Cursiva (*texto*)">
                                        <i data-lucide="italic" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <div class="w-px h-4 bg-[#232936] mx-0.5"></div>
                                    <!-- Heading 2 -->
                                    <button type="button" @click="insertFormatting('\n## ', '\n', 'Subtítulo')" class="px-2 py-1 rounded-lg hover:bg-[#1A2426] text-gray-300 hover:text-white transition-colors font-bold text-[11px]" title="Subtítulo H2">
                                        H2
                                    </button>
                                    <!-- Heading 3 -->
                                    <button type="button" @click="insertFormatting('\n### ', '\n', 'Sección')" class="px-2 py-1 rounded-lg hover:bg-[#1A2426] text-gray-300 hover:text-white transition-colors font-bold text-[11px]" title="Subtítulo H3">
                                        H3
                                    </button>
                                    <div class="w-px h-4 bg-[#232936] mx-0.5"></div>
                                    <!-- Bullet List -->
                                    <button type="button" @click="insertFormatting('\n- ', '', 'Elemento de lista')" class="p-1.5 rounded-lg hover:bg-[#1A2426] text-gray-300 hover:text-white transition-colors" title="Lista con viñetas">
                                        <i data-lucide="list" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <!-- Numbered List -->
                                    <button type="button" @click="insertFormatting('\n1. ', '', 'Primer elemento')" class="p-1.5 rounded-lg hover:bg-[#1A2426] text-gray-300 hover:text-white transition-colors" title="Lista numerada">
                                        <i data-lucide="list-ordered" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <!-- Quote -->
                                    <button type="button" @click="insertFormatting('\n> ', '', 'Cita o nota destacada')" class="p-1.5 rounded-lg hover:bg-[#1A2426] text-gray-300 hover:text-white transition-colors" title="Bloque de cita">
                                        <i data-lucide="quote" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <!-- Code -->
                                    <button type="button" @click="insertFormatting('`', '`', 'código')" class="p-1.5 rounded-lg hover:bg-[#1A2426] text-gray-300 hover:text-white transition-colors" title="Código en línea">
                                        <i data-lucide="code" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <!-- Divider -->
                                    <button type="button" @click="insertFormatting('\n\n---\n\n', '', '')" class="p-1.5 rounded-lg hover:bg-[#1A2426] text-gray-300 hover:text-white transition-colors" title="Separador horizontal">
                                        <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Write Tab: Rich Textarea -->
                            <div x-show="editorTab === 'write'" class="p-2">
                                <textarea id="descriptionEditor" 
                                          name="description" 
                                          x-model="description" 
                                          rows="9" 
                                          placeholder="Escribe aquí la historia, ambientación, mecánicas y legado del videojuego en ~200 palabras (o pulsa 'Generar con IA' para que Gemini la redacte por ti)..." 
                                          class="w-full bg-transparent p-3 text-xs text-gray-200 focus:outline-none font-sans leading-relaxed resize-y scrollbar-thin"></textarea>
                            </div>

                            <!-- Preview Tab: Rendered Live Markdown View -->
                            <div x-show="editorTab === 'preview'" style="display: none;" class="p-5 min-h-[220px] bg-[#0A0D0E] text-xs leading-relaxed overflow-y-auto max-h-[400px]">
                                <div class="prose-game-content" x-html="renderMarkdown(description)"></div>
                            </div>

                            <!-- Bottom Status Bar -->
                            <div class="bg-[#11141A] border-t border-[#232936] px-4 py-2 flex flex-wrap items-center justify-between text-[11px] font-mono text-gray-400">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center gap-1.5">
                                        <i data-lucide="file-text" class="w-3 h-3 text-emerald-400"></i>
                                        <strong class="text-white" x-text="wordCount"></strong> palabras
                                    </span>
                                    <span class="text-gray-600">•</span>
                                    <span x-text="description ? description.length + ' caracteres' : '0 caracteres'"></span>
                                </div>
                                <div class="flex items-center gap-1 text-gray-500">
                                    <span>Objetivo:</span>
                                    <span class="text-emerald-400 font-bold">~200 palabras</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Technical Specs Box -->
                <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                        <h2 class="text-sm font-bold text-white font-sans uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="cpu" class="w-4 h-4 text-emerald-400"></i> Ficha Técnica
                        </h2>
                        <button type="button" @click="autocompleteSpecsWithAi()" class="text-xs text-emerald-400 hover:text-emerald-300 font-mono flex items-center gap-1">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Autocompletar con Gemini IA
                        </button>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs font-mono">
                        <div>
                            <label class="block text-gray-400 mb-1">Desarrollador</label>
                            <input type="text" name="developer" x-model="developer" placeholder="Ej. Santa Monica / Capcom" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2 text-gray-200">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Publisher</label>
                            <input type="text" name="publisher" x-model="publisher" placeholder="Ej. Sony / Nintendo" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2 text-gray-200">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Año de Lanzamiento</label>
                            <input type="number" name="release_year" x-model="releaseYear" placeholder="2007" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2 text-gray-200">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Región</label>
                            <input type="text" name="region" x-model="region" placeholder="USA / NTSC-U" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2 text-gray-200">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Idiomas</label>
                            <input type="text" name="languages" value="{{ old('languages', 'Español, Inglés') }}" placeholder="Español, English" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2 text-gray-200">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Formato Archivo</label>
                            <input type="text" name="file_format" x-model="fileFormat" placeholder="ISO / CHD / NSP" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2 text-gray-200">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-400 mb-1">Tamaño del Archivo</label>
                            <input type="text" name="file_size" x-model="fileSize" placeholder="Ej. 1.45 GB, 750 MB, 4.2 GB..." class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-xs text-white font-mono">
                            <p class="text-[10px] text-gray-500 mt-1">Escribe libremente el tamaño que deseas mostrar a los usuarios (Ej. 1.2 GB, 800 MB, 14.5 GB).</p>
                        </div>
                    </div>
                </div>

                <!-- Multi-Server Download URLs & Cloudflare R2 Box -->
                <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                        <h2 class="text-sm font-bold text-white font-sans uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="download-cloud" class="w-4 h-4 text-blue-400"></i> Enlaces & Servidores de Descarga
                        </h2>
                        <button type="button" @click="addMirror()" class="px-2.5 py-1 rounded-lg bg-blue-600/20 hover:bg-blue-600 text-blue-300 hover:text-white border border-blue-500/30 text-xs font-mono transition-colors flex items-center gap-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> + Añadir Servidor / Mirror
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Server 1: Cloudflare R2 / Direct Upload or Link -->
                        <div class="p-4 bg-[#0A0C0F] border border-[#232936] rounded-xl space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-mono text-blue-400 font-bold flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-400"></span> Servidor Principal (Cloudflare R2 / S3 / Directo)
                                </label>
                                <span class="text-[10px] font-mono text-emerald-400 bg-emerald-950/40 border border-emerald-500/20 px-2 py-0.5 rounded font-bold">Subida Directa o URL</span>
                            </div>

                            <!-- Direct ROM File Upload to R2 / Local with Real-time Progress -->
                            <div class="p-3.5 bg-[#11141A] border border-dashed border-[#232936] hover:border-blue-500/40 rounded-xl space-y-3 transition-colors">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="upload-cloud" class="w-4 h-4 text-blue-400 shrink-0"></i>
                                        <span class="font-bold text-gray-200">Subir ROM / ISO / ZIP a Cloudflare R2:</span>
                                    </div>
                                    <label class="cursor-pointer px-3 py-1.5 bg-blue-600/20 hover:bg-blue-600 text-blue-300 hover:text-white border border-blue-500/30 rounded-lg text-xs font-mono font-bold transition-all flex items-center gap-1.5 w-fit">
                                        <i data-lucide="file-up" class="w-3.5 h-3.5"></i> 
                                        <span x-text="uploadedRomInfo ? 'Cambiar Archivo' : 'Seleccionar Archivo'"></span>
                                        <input type="file" id="romFileInput" name="rom_file" @change="handleRomSelect($event)" class="hidden">
                                    </label>
                                </div>

                                <!-- Selected File Info & Live Upload Progress -->
                                <div x-show="uploadedRomInfo" x-cloak class="p-3 bg-[#0A0C0F] border border-blue-500/30 rounded-xl space-y-2.5 text-xs font-mono">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 text-gray-300 truncate">
                                            <i data-lucide="file-archive" class="w-4 h-4 text-blue-400 shrink-0"></i>
                                            <span class="truncate font-bold text-white" x-text="uploadedRomInfo?.name"></span>
                                            <span class="text-gray-400 text-[11px]" x-text="'(' + uploadedRomInfo?.size + ')'"></span>
                                        </div>
                                        
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" x-show="!uploadingRom && romUploadStatus !== 'success'" @click="uploadRomDirectly()" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs font-mono flex items-center gap-1.5 shadow-lg shadow-emerald-950/50 transition-all">
                                                <i data-lucide="cloud-upload" class="w-3.5 h-3.5"></i>
                                                <span>Subir ahora a R2</span>
                                            </button>
                                            <button type="button" x-show="uploadingRom" @click="cancelRomUpload()" class="px-2.5 py-1 rounded-lg bg-rose-600/20 text-rose-300 hover:bg-rose-600 hover:text-white border border-rose-500/30 text-[11px] font-mono transition-colors">
                                                Cancelar
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Live Progress Bar when Uploading -->
                                    <div x-show="uploadingRom" x-cloak class="space-y-1.5 pt-2 border-t border-[#232936]">
                                        <div class="flex items-center justify-between text-[11px] font-mono">
                                            <span class="text-blue-400 font-semibold flex items-center gap-1.5">
                                                <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                                                <span x-text="romUploadStatusMessage || 'Subiendo a Cloudflare R2...'"></span>
                                            </span>
                                            <span class="text-emerald-400 font-bold" x-text="romUploadProgress + '%'"></span>
                                        </div>

                                        <!-- Progress Bar Container -->
                                        <div class="w-full h-2.5 bg-[#11141A] rounded-full overflow-hidden border border-[#232936] p-0.5">
                                            <div class="h-full bg-gradient-to-r from-blue-500 via-indigo-500 to-emerald-400 rounded-full transition-all duration-150 relative overflow-hidden" :style="'width: ' + romUploadProgress + '%'">
                                                <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between text-[10px] text-gray-500 font-mono">
                                            <span x-text="romUploadedBytesFormatted + ' / ' + romTotalBytesFormatted"></span>
                                            <span x-text="romUploadSpeedFormatted"></span>
                                        </div>
                                    </div>

                                    <!-- Upload Success Banner -->
                                    <div x-show="romUploadStatus === 'success'" x-cloak class="flex items-center gap-2 p-2 bg-emerald-950/40 border border-emerald-500/30 rounded-lg text-emerald-300 text-[11px]">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span class="truncate">¡Archivo subido exitosamente a Cloudflare R2! URL y metadatos actualizados.</span>
                                    </div>

                                    <!-- Upload Error Banner -->
                                    <div x-show="romUploadStatus === 'error'" x-cloak class="flex items-center justify-between p-2 bg-rose-950/40 border border-rose-500/30 rounded-lg text-rose-300 text-[11px]">
                                        <div class="flex items-center gap-2 truncate">
                                            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-400 shrink-0"></i>
                                            <span class="truncate" x-text="romUploadErrorMessage || 'Ocurrió un error en la subida.'"></span>
                                        </div>
                                        <button type="button" @click="uploadRomDirectly()" class="underline font-bold hover:text-white shrink-0 ml-2">Reintentar</button>
                                    </div>
                                </div>
                            </div>

                            <!-- URL Input Field -->
                            <div class="space-y-1">
                                <label class="block text-[11px] font-mono text-gray-400">O confirma / ingresa la URL Directa generada:</label>
                                <input type="text" name="download_url" x-model="downloadUrl" placeholder="https://pub-vault.r2.dev/roms/ps2/juego.iso o link directo..." class="w-full bg-[#11141A] border border-[#232936] rounded-lg p-2.5 text-xs text-blue-400 font-mono focus:border-blue-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Server 2: Mirror 1 -->
                        <div class="p-3.5 bg-[#0A0C0F] border border-[#232936] rounded-xl space-y-1.5">
                            <label class="text-xs font-mono text-emerald-400 font-bold flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Servidor Espejo (Mirror 1 / Alternativo)
                            </label>
                            <input type="text" name="mirror_url" value="{{ old('mirror_url') }}" placeholder="https://mirror.romhub.io/roms/juego.iso (Opcional)" class="w-full bg-[#11141A] border border-[#232936] rounded-lg p-2.5 text-xs text-gray-300 font-mono focus:border-emerald-500 focus:outline-none">
                        </div>

                        <!-- Dynamic Extra Mirrors List (Mega, Mediafire, Google Drive, 1Fichier, Torrent, etc.) -->
                        <template x-for="(item, index) in mirrors" :key="index">
                            <div class="p-3.5 bg-[#0A0C0F] border border-[#232936] rounded-xl space-y-2 relative">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                                        <select x-model="item.server" :name="'mirrors['+index+'][server]'" class="bg-[#11141A] border border-[#232936] rounded-lg px-2.5 py-1 text-xs text-purple-300 font-mono font-bold">
                                            <option value="Mega">Mega.nz</option>
                                            <option value="MediaFire">MediaFire</option>
                                            <option value="Google Drive">Google Drive</option>
                                            <option value="1Fichier">1Fichier</option>
                                            <option value="Torrent">Torrent / Magnet P2P</option>
                                            <option value="PixelDrain">PixelDrain</option>
                                            <option value="Servidor Secundario">Servidor Secundario</option>
                                            <option value="Descarga Alternativa">Descarga Alternativa</option>
                                        </select>
                                    </div>
                                    <button type="button" @click="removeMirror(index)" class="text-rose-400 hover:text-rose-300 text-xs font-mono flex items-center gap-1">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Eliminar
                                    </button>
                                </div>
                                <input type="text" x-model="item.url" :name="'mirrors['+index+'][url]'" placeholder="https://mega.nz/... o magnet:?xt=..." class="w-full bg-[#11141A] border border-[#232936] rounded-lg p-2.5 text-xs text-gray-200 font-mono focus:border-purple-500 focus:outline-none">
                            </div>
                        </template>

                        <button type="button" @click="addMirror()" class="w-full py-2.5 border border-dashed border-[#232936] rounded-xl text-xs font-mono text-gray-400 hover:text-white hover:border-blue-500/50 transition-colors flex items-center justify-center gap-2">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-blue-400"></i> + Añadir Servidor Adicional (Mega, Mediafire, Drive, Torrent...)
                        </button>
                    </div>
                </div>

                <!-- SEO Optimization Box with AI -->
                <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                        <h2 class="text-sm font-bold text-white font-sans uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="globe" class="w-4 h-4 text-purple-400"></i> Optimización SEO & Metatags
                        </h2>
                        <button type="button" @click="generateSeoWithAi()" :disabled="seoLoading" class="px-3 py-1.5 rounded-lg bg-purple-600/20 hover:bg-purple-600 text-purple-300 hover:text-white border border-purple-500/30 text-xs font-mono transition-colors flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5" :class="seoLoading ? 'animate-spin' : ''"></i> 
                            <span x-text="seoLoading ? 'Generando SEO...' : '✨ Generar SEO con Gemini IA'"></span>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-mono text-gray-400 mb-1">Meta Título (Google Search)</label>
                            <input type="text" name="meta_title" x-model="metaTitle" placeholder="God of War II ROM PS2 — Descargar ISO Español" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-xs text-gray-200 font-sans focus:border-purple-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-mono text-gray-400 mb-1">Meta Descripción (Snippet)</label>
                            <textarea name="meta_description" x-model="metaDescription" rows="2" placeholder="Descarga la ROM de God of War II para PS2 totalmente verificada con SHA-256..." class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-xs text-gray-200 font-sans focus:border-purple-500 focus:outline-none"></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Media Uploaders & Taxonomies (4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Publishing Status & Visibility -->
                <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-4">
                    <h3 class="text-xs font-bold text-white font-sans uppercase tracking-wider border-b border-[#232936] pb-2">
                        Estado de Publicación
                    </h3>

                    <div class="space-y-3 font-sans text-xs">
                        <div>
                            <label class="block text-gray-400 mb-1 font-mono">Estado</label>
                            <select name="status" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-gray-200 font-mono">
                                <option value="PUBLISHED" selected>PUBLICADO</option>
                                <option value="DRAFT">BORRADOR</option>
                                <option value="ARCHIVED">ARCHIVADO</option>
                            </select>
                        </div>

                        <div class="space-y-2.5 pt-2 border-t border-[#232936]">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="is_spotlight" value="1" class="mt-0.5 rounded bg-[#0A0C0F] border-[#232936] text-amber-500 focus:ring-amber-500">
                                <div>
                                    <span class="text-amber-300 font-semibold block text-[12px]">★ Destacar en Hero Principal (Spotlight)</span>
                                    <span class="text-gray-500 text-[10px] block leading-tight">Será el juego gigante protagonista en el banner superior de la portada.</span>
                                </div>
                            </label>
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" checked class="mt-0.5 rounded bg-[#0A0C0F] border-[#232936] text-blue-600 focus:ring-blue-600">
                                <div>
                                    <span class="text-gray-300 font-medium block text-[12px]">Mostrar en Riel Lateral del Hero</span>
                                    <span class="text-gray-500 text-[10px] block leading-tight">Aparece en la columna derecha de 3 destacados junto al banner.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- WebP Cover Art Upload (3:4.1 Aspect Ratio) with Live Preview -->
                <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-3">
                    <div class="flex items-center justify-between border-b border-[#232936] pb-2">
                        <h3 class="text-xs font-bold text-white font-sans uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="image" class="w-3.5 h-3.5 text-blue-400"></i> Carátula Frontal (3:4.1)
                        </h3>
                        <span class="text-[10px] font-mono text-emerald-400 bg-emerald-950/40 border border-emerald-500/20 px-2 py-0.5 rounded">Auto WebP</span>
                    </div>

                    <!-- Live Image Preview (When selected) -->
                    <div x-show="coverPreview" x-cloak class="relative group mx-auto w-36 aspect-[3/4.1] rounded-xl overflow-hidden border-2 border-blue-500/50 shadow-xl bg-[#0A0C0F] transition-all">
                        <img :src="coverPreview" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2 p-2">
                            <label class="cursor-pointer px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-mono text-[11px] font-bold transition-transform transform hover:scale-105">
                                <span>Cambiar</span>
                                <input type="file" id="coverFileInput" name="cover_image" accept="image/*" @change="handleCoverSelect($event)" class="hidden">
                            </label>
                            <button type="button" @click="clearCoverPreview()" class="px-2.5 py-1 rounded-lg bg-rose-600/80 hover:bg-rose-600 text-white font-mono text-[10px] transition-colors">
                                Quitar
                            </button>
                        </div>
                        <div class="absolute bottom-1.5 left-1.5 right-1.5 bg-black/85 backdrop-blur-sm px-1.5 py-0.5 rounded text-[9px] font-mono text-center text-emerald-400 truncate" x-text="coverFileName || 'Vista Previa en Vivo'"></div>
                    </div>

                    <!-- Dropzone (When no image is selected) -->
                    <div x-show="!coverPreview" class="p-4 border-2 border-dashed border-[#232936] rounded-xl text-center space-y-2 hover:border-blue-500/50 transition-colors bg-[#0A0C0F]">
                        <i data-lucide="image-up" class="w-8 h-8 text-gray-500 mx-auto"></i>
                        <div class="text-xs font-sans text-gray-300">
                            <label class="cursor-pointer text-blue-400 hover:underline">
                                <span class="font-bold">Seleccionar carátula frontal</span>
                                <input type="file" id="coverFileInput" name="cover_image" accept="image/*" @change="handleCoverSelect($event)" class="hidden">
                            </label>
                        </div>
                        <p class="text-[10px] font-mono text-gray-500">PNG, JPG hasta 20MB. Se convertirá automáticamente a WebP optimizado.</p>
                    </div>
                </div>

                <!-- WebP Banner Image Upload (16:9 Aspect Ratio) with Live Preview -->
                <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-3">
                    <div class="flex items-center justify-between border-b border-[#232936] pb-2">
                        <h3 class="text-xs font-bold text-white font-sans uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="panorama" class="w-3.5 h-3.5 text-blue-400"></i> Banner Panorámico (16:9)
                        </h3>
                        <span class="text-[10px] font-mono text-emerald-400 bg-emerald-950/40 border border-emerald-500/20 px-2 py-0.5 rounded">Auto WebP</span>
                    </div>

                    <!-- Live Banner Preview (When selected) -->
                    <div x-show="bannerPreview" x-cloak class="relative group w-full aspect-video rounded-xl overflow-hidden border-2 border-blue-500/50 shadow-xl bg-[#0A0C0F] transition-all">
                        <img :src="bannerPreview" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2 p-2">
                            <label class="cursor-pointer px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-mono text-[11px] font-bold transition-transform transform hover:scale-105">
                                <span>Cambiar Banner</span>
                                <input type="file" id="bannerFileInput" name="banner_image" accept="image/*" @change="handleBannerSelect($event)" class="hidden">
                            </label>
                            <button type="button" @click="clearBannerPreview()" class="px-2.5 py-1 rounded-lg bg-rose-600/80 hover:bg-rose-600 text-white font-mono text-[10px] transition-colors">
                                Quitar
                            </button>
                        </div>
                        <div class="absolute bottom-1.5 left-1.5 right-1.5 bg-black/85 backdrop-blur-sm px-1.5 py-0.5 rounded text-[9px] font-mono text-center text-emerald-400 truncate" x-text="bannerFileName || 'Vista Previa Panorámica (16:9)'"></div>
                    </div>

                    <!-- Dropzone (When no banner is selected) -->
                    <div x-show="!bannerPreview" class="p-4 border-2 border-dashed border-[#232936] rounded-xl text-center space-y-2 hover:border-blue-500/50 transition-colors bg-[#0A0C0F]">
                        <i data-lucide="panorama" class="w-8 h-8 text-gray-500 mx-auto"></i>
                        <div class="text-xs font-sans text-gray-300">
                            <label class="cursor-pointer text-blue-400 hover:underline">
                                <span class="font-bold">Seleccionar banner panorámico</span>
                                <input type="file" id="bannerFileInput" name="banner_image" accept="image/*" @change="handleBannerSelect($event)" class="hidden">
                            </label>
                        </div>
                        <p class="text-[10px] font-mono text-gray-500">1920x1080 recomendado. Se convertirá automáticamente a WebP.</p>
                    </div>
                </div>

                <!-- WebP In-Game Screenshots Multi-Upload Box -->
                <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-3">
                    <div class="flex items-center justify-between border-b border-[#232936] pb-2">
                        <h3 class="text-xs font-bold text-white font-sans uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="camera" class="w-3.5 h-3.5 text-blue-400"></i> Capturas In-Game (Screenshots HD)
                        </h3>
                        <span class="text-[10px] font-mono text-emerald-400 bg-emerald-950/40 border border-emerald-500/20 px-2 py-0.5 rounded">Auto WebP</span>
                    </div>

                    <!-- Multi-files Uploader Dropzone -->
                    <div class="p-3.5 border-2 border-dashed border-[#232936] rounded-xl text-center space-y-1.5 hover:border-blue-500/50 transition-colors bg-[#0A0C0F]">
                        <i data-lucide="images" class="w-6 h-6 text-gray-500 mx-auto"></i>
                        <div class="text-xs font-sans text-gray-300">
                            <label class="cursor-pointer text-blue-400 hover:underline">
                                <span class="font-bold">+ Seleccionar Capturas In-Game</span>
                                <input type="file" id="screenshotFilesInput" name="screenshot_files[]" accept="image/*" multiple @change="handleScreenshotsSelect($event)" class="hidden">
                            </label>
                        </div>
                        <p class="text-[10px] font-mono text-gray-500">Puedes seleccionar varias capturas a la vez (PNG, JPG).</p>
                    </div>

                    <!-- Thumbnails Preview Grid -->
                    <div x-show="screenshotPreviews.length > 0" x-cloak class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-1 max-h-52 overflow-y-auto pr-1">
                        <template x-for="(item, idx) in screenshotPreviews" :key="idx">
                            <div class="relative group/ss aspect-video rounded-lg overflow-hidden border border-[#232936] bg-[#0A0C0F]">
                                <img :src="item.url" class="w-full h-full object-cover">
                                <button type="button" @click="removeScreenshotPreview(idx)" class="absolute top-1 right-1 p-1 rounded-md bg-rose-600 hover:bg-rose-500 text-white opacity-0 group-hover/ss:opacity-100 transition-opacity shadow-md">
                                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                                </button>
                                <span class="absolute bottom-1 left-1 px-1 rounded bg-black/80 text-[8px] font-mono text-emerald-400 truncate max-w-[80%]" x-text="'#' + (idx + 1)"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Badges Selector -->
                <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-3">
                    <h3 class="text-xs font-bold text-white font-sans uppercase tracking-wider border-b border-[#232936] pb-2">
                        Insignias & Badges Especiales
                    </h3>
                    <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                        @foreach($badges as $badge)
                        <label class="flex items-center justify-between p-2 rounded-lg bg-[#0A0C0F] border border-[#232936] cursor-pointer hover:border-blue-500/40">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-mono font-semibold" style="background-color: {{ $badge->bg_color }}; color: {{ $badge->text_color }}; border: 1px solid {{ $badge->border_color }}">
                                @if($badge->icon) <i data-lucide="{{ $badge->icon }}" class="w-3 h-3"></i> @endif
                                <span>{{ $badge->name }}</span>
                            </span>
                            <input type="checkbox" name="badge_ids[]" value="{{ $badge->id }}" class="rounded bg-[#11141A] border-[#232936] text-blue-600">
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Categories / Genres Selector -->
                <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-3">
                    <h3 class="text-xs font-bold text-white font-sans uppercase tracking-wider flex items-center justify-between border-b border-[#232936] pb-2">
                        <span>Categorías & Géneros</span>
                        <span class="text-[10px] text-gray-500 font-mono">{{ $categories->count() }} disponibles</span>
                    </h3>
                    <div class="grid grid-cols-2 gap-1.5 max-h-48 overflow-y-auto pr-1">
                        @foreach($categories as $category)
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-[#0A0C0F] border border-[#232936] text-xs font-mono text-gray-300 cursor-pointer hover:border-blue-500/40">
                            <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" {{ in_array($category->id, old('category_ids', [])) ? 'checked' : '' }} class="rounded bg-[#11141A] border-[#232936] text-blue-600">
                            <span class="truncate">{{ $category->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <!-- Franchises Box -->
                @if(isset($franchises) && $franchises->isNotEmpty())
                <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 space-y-3">
                    <h3 class="text-xs font-bold text-white font-sans uppercase tracking-wider flex items-center justify-between border-b border-[#232936] pb-2">
                        <span class="flex items-center gap-1.5"><i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i> Saga / Franquicia</span>
                        <span class="text-[10px] text-gray-500 font-mono">{{ $franchises->count() }} sagas</span>
                    </h3>

                    <div class="grid grid-cols-2 gap-1.5 max-h-48 overflow-y-auto pr-1">
                        @foreach($franchises as $franchise)
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-[#0A0C0F] border border-[#232936] text-xs font-mono text-gray-300 cursor-pointer hover:border-amber-500/40">
                            <input type="checkbox" name="franchise_ids[]" value="{{ $franchise->id }}" class="rounded bg-[#11141A] border-[#232936] text-amber-500">
                            <span class="truncate">{{ $franchise->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Submit Button -->
                <button type="submit" class="w-full py-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-black text-xs uppercase tracking-wider transition-all shadow-xl shadow-blue-600/30 flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i> Guardar y Publicar Título
                </button>

            </div>

        </div>

    </form>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
function gameCreateForm() {
    return {
        title: @json(old('title', '')),
        consoleName: '',
        description: @json(old('description', '')),
        metaTitle: @json(old('meta_title', '')),
        metaDescription: @json(old('meta_description', '')),
        developer: @json(old('developer', '')),
        publisher: @json(old('publisher', '')),
        releaseYear: @json(old('release_year', '')),
        region: @json(old('region', 'USA / NTSC-U')),
        fileSize: @json(old('file_size', '')),
        fileFormat: @json(old('file_format', 'ISO')),
        downloadUrl: @json(old('download_url', '')),
        mirrors: @json(old('mirrors', [])),
        uploadedRomInfo: null,
        uploadingRom: false,
        romUploadProgress: 0,
        romUploadStatus: 'idle',
        romUploadStatusMessage: '',
        romUploadErrorMessage: '',
        romUploadedBytesFormatted: '',
        romTotalBytesFormatted: '',
        romUploadSpeedFormatted: '',
        activeRomXhr: null,
        coverPreview: null,
        coverFileName: '',
        bannerPreview: null,
        bannerFileName: '',
        screenshotPreviews: [],
        aiLoading: false,
        seoLoading: false,
        descLoading: false,
        specsLoading: false,
        allAiLoading: false,
        editorTab: 'write',
        
        get wordCount() {
            if (!this.description) return 0;
            return this.description.trim().split(/\s+/).filter(Boolean).length;
        },

        handleCoverSelect(e) {
            let file = e.target.files[0];
            if (!file) return;
            this.coverFileName = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
            let reader = new FileReader();
            reader.onload = (evt) => {
                this.coverPreview = evt.target.result;
                this.$nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
            };
            reader.readAsDataURL(file);
        },

        clearCoverPreview() {
            this.coverPreview = null;
            this.coverFileName = '';
            let input = document.getElementById('coverFileInput');
            if (input) input.value = '';
            this.$nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
        },

        handleBannerSelect(e) {
            let file = e.target.files[0];
            if (!file) return;
            this.bannerFileName = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
            let reader = new FileReader();
            reader.onload = (evt) => {
                this.bannerPreview = evt.target.result;
                this.$nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
            };
            reader.readAsDataURL(file);
        },

        clearBannerPreview() {
            this.bannerPreview = null;
            this.bannerFileName = '';
            let input = document.getElementById('bannerFileInput');
            if (input) input.value = '';
            this.$nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
        },

        handleScreenshotsSelect(e) {
            let files = Array.from(e.target.files);
            files.forEach(file => {
                let reader = new FileReader();
                reader.onload = (evt) => {
                    this.screenshotPreviews.push({ name: file.name, url: evt.target.result });
                    this.$nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
                };
                reader.readAsDataURL(file);
            });
        },

        removeScreenshotPreview(idx) {
            this.screenshotPreviews.splice(idx, 1);
            this.$nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
        },

        insertFormatting(prefix, suffix = '', defaultText = '') {
            const textarea = document.getElementById('descriptionEditor');
            if (!textarea) return;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const selectedText = text.substring(start, end) || defaultText;
            const replacement = prefix + selectedText + suffix;
            textarea.value = text.substring(0, start) + replacement + text.substring(end);
            this.description = textarea.value;
            textarea.focus();
            textarea.setSelectionRange(start + prefix.length, start + prefix.length + selectedText.length);
        },

        renderMarkdown(text) {
            if (!text || !text.trim()) {
                return '<p class="text-gray-500 italic p-4 text-center">Sinopsis vacía. Escribe o pulsa "Generar con IA" para crear ~200 palabras...</p>';
            }
            if (window.marked) {
                try { return marked.parse(text); } catch(e) {}
            }
            let html = text
                .replace(/^### (.*$)/gim, '<h3 class="text-sm font-bold text-emerald-300 mt-3 mb-1 font-heading">$1</h3>')
                .replace(/^## (.*$)/gim, '<h2 class="text-base font-bold text-white border-b border-[#232936] pb-1 mt-4 mb-2 font-heading">$1</h2>')
                .replace(/^# (.*$)/gim, '<h1 class="text-lg font-black text-emerald-400 mt-4 mb-2 font-heading">$1</h1>')
                .replace(/\*\*(.*?)\*\*/gim, '<strong class="text-white font-bold">$1</strong>')
                .replace(/\*(.*?)\*/gim, '<em class="text-gray-300 italic">$1</em>')
                .replace(/`(.*?)`/gim, '<code class="bg-black/50 px-1 py-0.5 rounded text-emerald-400 font-mono text-xs">$1</code>')
                .replace(/^\> (.*$)/gim, '<blockquote class="border-l-2 border-emerald-500 pl-3 italic text-gray-400 my-2">$1</blockquote>')
                .replace(/^\- (.*$)/gim, '<li class="ml-4 list-disc text-gray-300">$1</li>')
                .replace(/\n\n/gim, '</p><p class="mb-2.5 text-gray-300 leading-relaxed">')
                .replace(/\n/gim, '<br>');
            return '<p class="mb-2.5 text-gray-300 leading-relaxed">' + html + '</p>';
        },

        addMirror() {
            this.mirrors.push({ server: 'Mega', url: '' });
        },

        removeMirror(idx) {
            this.mirrors.splice(idx, 1);
        },

        handleRomSelect(e) {
            let file = e.target.files[0];
            if (!file) return;

            this.uploadedRomInfo = {
                name: file.name,
                size: (file.size / (1024*1024)).toFixed(1) + ' MB'
            };
            this.romUploadStatus = 'idle';
            this.romUploadStatusMessage = '';
            this.romUploadProgress = 0;

            if (!this.fileSize) {
                if (file.size >= 1073741824) {
                    this.fileSize = (file.size / 1073741824).toFixed(2) + ' GB';
                } else if (file.size >= 1048576) {
                    this.fileSize = (file.size / 1048576).toFixed(1) + ' MB';
                }
            }

            let parts = file.name.split('.');
            let ext = parts.length > 1 ? parts.pop().toUpperCase() : '';
            if (ext && ext.length <= 5) {
                this.fileFormat = ext;
            }
        },

        uploadRomDirectly() {
            let fileInput = document.getElementById('romFileInput');
            let file = fileInput ? fileInput.files[0] : null;
            if (!file) {
                alert('Por favor selecciona un archivo ROM / ISO primero.');
                return;
            }

            this.uploadingRom = true;
            this.romUploadProgress = 0;
            this.romUploadStatus = 'uploading';
            this.romUploadStatusMessage = 'Iniciando subida a Cloudflare R2...';
            this.romUploadErrorMessage = '';
            this.romUploadedBytesFormatted = '0 MB';
            this.romTotalBytesFormatted = (file.size / (1024 * 1024)).toFixed(1) + ' MB';
            this.romUploadSpeedFormatted = '';

            let formData = new FormData();
            formData.append('rom_file', file);
            let consoleSel = document.getElementById('consoleSelect');
            if (consoleSel) formData.append('console_id', consoleSel.value);

            let xhr = new XMLHttpRequest();
            this.activeRomXhr = xhr;
            let startTime = Date.now();

            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    let percent = Math.round((e.loaded / e.total) * 100);
                    this.romUploadProgress = percent;
                    this.romUploadedBytesFormatted = (e.loaded / (1024 * 1024)).toFixed(1) + ' MB';
                    this.romTotalBytesFormatted = (e.total / (1024 * 1024)).toFixed(1) + ' MB';
                    
                    let elapsedSec = (Date.now() - startTime) / 1000;
                    if (elapsedSec > 0.5) {
                        let bytesPerSec = e.loaded / elapsedSec;
                        let mbPerSec = (bytesPerSec / (1024 * 1024)).toFixed(1);
                        this.romUploadSpeedFormatted = mbPerSec + ' MB/s';
                    }

                    if (percent >= 100) {
                        this.romUploadStatusMessage = 'Procesando archivo y generando URL segura en R2...';
                    } else {
                        this.romUploadStatusMessage = 'Subiendo a Cloudflare R2 (' + percent + '%)...';
                    }
                }
            };

            xhr.onload = () => {
                this.uploadingRom = false;
                this.activeRomXhr = null;
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        let d = JSON.parse(xhr.responseText);
                        if (d.success && d.url) {
                            this.downloadUrl = d.url;
                            if (d.file_size) this.fileSize = d.file_size;
                            if (d.file_format) this.fileFormat = d.file_format;
                            this.romUploadStatus = 'success';
                            this.romUploadStatusMessage = '¡Completado!';
                            window.dispatchEvent(new CustomEvent('toast-notify', { 
                                detail: { message: '🚀 ¡Archivo subido exitosamente a ' + (d.provider || 'R2') + '!' } 
                            }));
                        } else {
                            this.romUploadStatus = 'error';
                            this.romUploadErrorMessage = d.message || 'Error al procesar en servidor';
                        }
                    } catch (err) {
                        this.romUploadStatus = 'error';
                        this.romUploadErrorMessage = 'Respuesta no válida del servidor';
                    }
                } else {
                    this.romUploadStatus = 'error';
                    this.romUploadErrorMessage = 'Error HTTP ' + xhr.status + ': ' + xhr.statusText;
                }
                this.$nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
            };

            xhr.onerror = () => {
                this.uploadingRom = false;
                this.activeRomXhr = null;
                this.romUploadStatus = 'error';
                this.romUploadErrorMessage = 'Error de red o conexión interrumpida con el servidor';
                this.$nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
            };

            xhr.open('POST', '{{ route('admin.games.uploadRom') }}', true);
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.send(formData);
        },

        cancelRomUpload() {
            if (this.activeRomXhr) {
                this.activeRomXhr.abort();
                this.activeRomXhr = null;
            }
            this.uploadingRom = false;
            this.romUploadStatus = 'idle';
            this.romUploadStatusMessage = '';
            this.romUploadProgress = 0;
            this.$nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
        },

        getConsoleName() {
            let select = document.getElementById('consoleSelect');
            if (!select || select.selectedIndex < 0) return 'Consola';
            let txt = select.options[select.selectedIndex]?.text || '';
            return txt.trim();
        },

        generateSeoWithAi() {
            if (!this.title || !this.title.trim()) {
                alert('Por favor escribe el título del juego primero.');
                return Promise.resolve();
            }
            this.seoLoading = true;
            return fetch('{{ route('admin.ai.seo') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ title: this.title.trim(), console: this.getConsoleName() })
            })
            .then(r => r.json())
            .then(d => {
                this.seoLoading = false;
                if (d.meta_title) this.metaTitle = d.meta_title;
                if (d.meta_description) this.metaDescription = d.meta_description;
                window.dispatchEvent(new CustomEvent('toast-notify', { detail: { message: '✨ ¡SEO generado con Gemini IA!' } }));
                this.$nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
            })
            .catch(err => {
                this.seoLoading = false;
                alert('Error al generar SEO: ' + err.message);
            });
        },

        generateDescriptionWithAi() {
            if (!this.title || !this.title.trim()) {
                alert('Por favor escribe el título del juego primero.');
                return Promise.resolve();
            }
            this.descLoading = true;
            return fetch('{{ route('admin.ai.description') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ title: this.title.trim(), console: this.getConsoleName() })
            })
            .then(r => r.json())
            .then(d => {
                this.descLoading = false;
                if (d.description) this.description = d.description;
                window.dispatchEvent(new CustomEvent('toast-notify', { detail: { message: '✨ ¡Sinopsis de ~200 palabras creada con Gemini IA!' } }));
                this.$nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
            })
            .catch(err => {
                this.descLoading = false;
                alert('Error al generar sinopsis: ' + err.message);
            });
        },

        autocompleteSpecsWithAi() {
            if (!this.title || !this.title.trim()) {
                alert('Por favor escribe el título del juego primero.');
                return Promise.resolve();
            }
            this.specsLoading = true;
            return fetch('{{ route('admin.ai.specs') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ title: this.title.trim(), console: this.getConsoleName() })
            })
            .then(r => r.json())
            .then(d => {
                this.specsLoading = false;
                if (d.developer) this.developer = d.developer;
                if (d.publisher) this.publisher = d.publisher;
                if (d.release_year) this.releaseYear = d.release_year;
                if (d.region) this.region = d.region;
                if (d.file_format) this.fileFormat = d.file_format;
                window.dispatchEvent(new CustomEvent('toast-notify', { detail: { message: '✨ ¡Ficha técnica autocompletada con IA!' } }));
                this.$nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
            })
            .catch(err => {
                this.specsLoading = false;
                alert('Error al autocompletar ficha técnica: ' + err.message);
            });
        },

        async autocompleteAllWithAi() {
            if (!this.title || !this.title.trim()) {
                alert('Por favor escribe el título del juego primero.');
                return;
            }
            this.allAiLoading = true;
            try {
                await Promise.all([
                    this.autocompleteSpecsWithAi(),
                    this.generateSeoWithAi(),
                    this.generateDescriptionWithAi()
                ]);
                window.dispatchEvent(new CustomEvent('toast-notify', { detail: { message: '🎉 ¡Todo completado con IA (Ficha + SEO + Sinopsis)!' } }));
            } finally {
                this.allAiLoading = false;
                this.$nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
            }
        }
    };
}
</script>
@endsection
