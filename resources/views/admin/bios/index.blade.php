@extends('layouts.admin')

@section('title', 'Bóveda de BIOS & Firmwares — Panel de Administración')

@section('content')
<div class="space-y-6" x-data="{ 
    openModal: false, 
    editMode: false, 
    currentBio: {
        id: null,
        system: '',
        console_id: '',
        files: '',
        version: '',
        size: '',
        format: '.BIN',
        md5: '',
        sha1: '',
        emulator: '',
        download_url: '',
        description: '',
        is_active: true,
        order: 0
    } 
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans flex items-center gap-2.5">
                <i data-lucide="binary" class="w-6 h-6 text-blue-400"></i> Bóveda de BIOS & Firmwares
            </h1>
            <p class="text-xs text-gray-400 font-mono mt-0.5">Administra enlaces de descarga directa, versiones y hashes criptográficos de BIOS</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('bios') }}" target="_blank" class="px-3 py-2 rounded-xl bg-[#11141A] hover:bg-[#171B22] border border-[#232936] text-gray-300 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="external-link" class="w-4 h-4 text-gray-400"></i> Ver en Web Pública
            </a>
            <button @click="editMode = false; currentBio = { id: null, system: '', console_id: '', files: '', version: '', size: '', format: '.BIN', md5: '', sha1: '', emulator: '', download_url: '', description: '', is_active: true, order: {{ count($biosList) + 1 }} }; openModal = true" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-wide flex items-center gap-2 transition-all shadow-lg shadow-blue-600/30">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Nueva BIOS
            </button>
        </div>
    </div>

    <!-- BIOS List Table / Cards -->
    <div class="bg-[#11141A] border border-[#232936] rounded-2xl overflow-hidden shadow-xl">
        <div class="p-4 border-b border-[#232936] flex items-center justify-between">
            <span class="text-xs font-mono font-bold uppercase text-gray-400">Archivos Registrados ({{ count($biosList) }})</span>
            <span class="text-[11px] font-mono text-emerald-400 flex items-center gap-1">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Hashes MD5 / SHA-1 Activos
            </span>
        </div>

        <div class="divide-y divide-[#232936]">
            @forelse($biosList as $bio)
            <div class="p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 hover:bg-[#171B22]/50 transition-colors">
                
                <div class="flex items-start gap-4 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400 flex items-center justify-center font-mono font-bold shrink-0">
                        <i data-lucide="binary" class="w-5 h-5"></i>
                    </div>

                    <div class="space-y-1.5 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-bold text-white font-sans">{{ $bio->system }}</h2>
                            @if($bio->console)
                                <span class="px-2 py-0.5 rounded bg-[#0A0C0F] border border-[#232936] text-[10px] font-mono text-blue-300">{{ $bio->console->name }}</span>
                            @endif
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ $bio->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                {{ $bio->is_active ? 'ACTIVO' : 'OCULTO' }}
                            </span>
                            @if($bio->size)
                                <span class="text-[10px] font-mono text-gray-400">• {{ $bio->size }} ({{ $bio->format }})</span>
                            @endif
                        </div>

                        <p class="text-xs text-gray-300 font-mono">
                            <strong class="text-gray-400">Archivos:</strong> {{ $bio->files }} 
                            @if($bio->version) <span class="text-gray-500">({{ $bio->version }})</span> @endif
                        </p>

                        @if($bio->emulator)
                            <p class="text-[11px] text-gray-400 font-sans">
                                <strong>Emuladores compatibles:</strong> <span class="text-gray-300">{{ $bio->emulator }}</span>
                            </p>
                        @endif

                        @if($bio->md5 || $bio->sha1)
                            <div class="flex flex-wrap items-center gap-3 pt-1 text-[10px] font-mono text-gray-500">
                                @if($bio->md5) <span>MD5: <code class="text-gray-300 bg-[#0A0C0F] px-1.5 py-0.5 rounded">{{ $bio->md5 }}</code></span> @endif
                                @if($bio->sha1) <span>SHA-1: <code class="text-gray-300 bg-[#0A0C0F] px-1.5 py-0.5 rounded">{{ $bio->sha1 }}</code></span> @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 lg:self-center shrink-0">
                    <a href="{{ $bio->download_url }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-[#0A0C0F] hover:bg-[#171B22] border border-[#232936] text-blue-400 hover:text-blue-300 font-mono text-xs flex items-center gap-1.5 transition-colors" title="Probar Descarga">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i> Probar Link
                    </a>

                    <button @click="editMode = true; currentBio = {{ json_encode($bio) }}; openModal = true" class="p-2 rounded-lg bg-[#0A0C0F] hover:bg-blue-600 text-gray-300 hover:text-white border border-[#232936] transition-colors" title="Editar">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    </button>

                    <form action="{{ route('admin.bios.destroy', $bio->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este registro de BIOS?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg bg-rose-500/10 hover:bg-rose-600 text-rose-400 hover:text-white border border-rose-500/20 transition-colors" title="Eliminar">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                </div>

            </div>
            @empty
            <div class="p-8 text-center text-gray-500 font-mono text-xs">
                No hay archivos de BIOS registrados actualmente. Haz clic en "Nueva BIOS" para agregar uno.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl max-w-2xl w-full p-6 space-y-4 shadow-2xl my-8" @click.outside="openModal = false">
            
            <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                <h3 class="text-base font-bold text-white font-sans flex items-center gap-2" x-text="editMode ? 'Editar BIOS & Firmware' : 'Nueva BIOS'"></h3>
                <button @click="openModal = false" class="text-gray-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form :action="editMode ? '{{ url('admin/bios') }}/' + currentBio.id : '{{ route('admin.bios.store') }}'" method="POST" class="space-y-4 font-sans text-xs">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Nombre del Sistema / Consola *</label>
                        <input type="text" name="system" x-model="currentBio.system" required placeholder="Ej. PlayStation 2 (PS2)" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Consola Asociada (Opcional)</label>
                        <select name="console_id" x-model="currentBio.console_id" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono">
                            <option value="">-- Sin Vincular --</option>
                            @foreach($consoles as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->manufacturer }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-gray-400 mb-1 font-mono">Nombres de Archivo Requeridos *</label>
                        <input type="text" name="files" x-model="currentBio.files" required placeholder="Ej. SCPH-39001.BIN, SCPH-70012.BIN" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Versión / Región</label>
                        <input type="text" name="version" x-model="currentBio.version" placeholder="Ej. USA / EUR / JPN v2.30" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Tamaño Estimado</label>
                        <input type="text" name="size" x-model="currentBio.size" placeholder="Ej. 14.2 MB" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Formato de Archivo</label>
                        <input type="text" name="format" x-model="currentBio.format" placeholder="Ej. .BIN / .ROM / .ZIP" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-400 mb-1 font-mono">URL de Descarga Directa *</label>
                    <input type="url" name="download_url" x-model="currentBio.download_url" required placeholder="https://archive.org/.../bios.zip" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-blue-400 font-mono">
                </div>

                <div>
                    <label class="block text-gray-400 mb-1 font-mono">Emuladores Recomendados</label>
                    <input type="text" name="emulator" x-model="currentBio.emulator" placeholder="Ej. PCSX2, NetherSX2, RetroArch" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Checksum MD5 (Opcional)</label>
                        <input type="text" name="md5" x-model="currentBio.md5" placeholder="d5ce512e94628f80424564c7810df66c" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-gray-300 font-mono text-[11px]">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Checksum SHA-1 (Opcional)</label>
                        <input type="text" name="sha1" x-model="currentBio.sha1" placeholder="9a5e523efb672a9dfa7a8d54c1f59239d5621415" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-gray-300 font-mono text-[11px]">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-400 mb-1 font-mono">Descripción o Instrucciones de Instalación</label>
                    <textarea name="description" x-model="currentBio.description" rows="2" placeholder="Instrucciones para colocar la BIOS en la carpeta bios/ del emulador..." class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white"></textarea>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="currentBio.is_active" class="rounded bg-[#0A0C0F] border-[#232936] text-blue-600 focus:ring-0 w-4 h-4">
                        <span class="text-gray-300 font-mono">Visible en la Web Pública</span>
                    </label>

                    <div class="flex items-center gap-2">
                        <label class="text-gray-400 font-mono">Orden:</label>
                        <input type="number" name="order" x-model="currentBio.order" class="w-16 bg-[#0A0C0F] border border-[#232936] rounded-lg p-1.5 text-white font-mono text-center">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-[#232936]">
                    <button type="button" @click="openModal = false" class="px-4 py-2 rounded-lg bg-[#171B22] text-gray-300">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold">Guardar BIOS</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
