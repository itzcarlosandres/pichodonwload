@extends('layouts.admin')

@section('title', 'Insignias & Badges Dinámicos — Administración ROMHUB')

@section('content')
<div class="space-y-6" x-data="badgesManager()">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans">Insignias & Badges Dinámicos</h1>
            <p class="text-xs text-gray-400 font-mono mt-0.5">Personaliza insignias visuales (No-Intro, Redump, 60 FPS, Traducciones) con paletas de color e iconos en tiempo real</p>
        </div>
        <button @click="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-wide flex items-center gap-2 transition-all shadow-lg shadow-blue-600/30">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Crear Badge
        </button>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-950/80 border border-emerald-500/30 rounded-xl text-xs text-emerald-300 font-sans flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(isset($errors) && $errors->any())
    <div class="p-4 bg-rose-950/80 border border-rose-500/30 rounded-xl text-xs text-rose-300 space-y-1 font-sans">
        <p class="font-bold">Por favor corrige los siguientes errores:</p>
        @foreach($errors->all() as $err)
        <p>• {{ $err }}</p>
        @endforeach
    </div>
    @endif

    <!-- Badges Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($badges as $badge)
        <div class="bg-[#11141A] border border-[#232936] hover:border-blue-500/50 rounded-2xl p-5 flex flex-col justify-between space-y-4">
            
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <!-- Live Visual Badge Display with Icon -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-mono font-bold shadow-sm" style="background-color: {{ $badge->bg_color }}; color: {{ $badge->text_color }}; border: 1px solid {{ $badge->border_color }}">
                        @if($badge->icon)
                            <i data-lucide="{{ $badge->icon }}" class="w-3.5 h-3.5"></i>
                        @endif
                        <span>{{ $badge->name }}</span>
                    </span>
                    <span class="text-xs font-mono text-gray-500">{{ $badge->games_count }} Juegos</span>
                </div>

                <div class="space-y-1.5 font-mono text-[11px] text-gray-400 bg-[#0A0C0F] p-3 rounded-xl border border-[#232936]">
                    <div class="flex justify-between items-center">
                        <span>Icono:</span>
                        <span class="text-white flex items-center gap-1.5 font-bold">
                            @if($badge->icon) <i data-lucide="{{ $badge->icon }}" class="w-3.5 h-3.5 text-blue-400"></i> @endif
                            <span>{{ $badge->icon ?: 'shield-check' }}</span>
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Fondo:</span>
                        <span class="flex items-center gap-1.5 text-white font-semibold">
                            <span class="w-3 h-3 rounded-full border border-gray-700 shrink-0" style="background-color: {{ $badge->bg_color }}"></span>
                            <span class="font-mono text-[10px]">{{ $badge->bg_color }}</span>
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Texto:</span>
                        <span class="flex items-center gap-1.5 font-semibold" style="color: {{ $badge->text_color }}">
                            <span class="w-3 h-3 rounded-full border border-gray-700 shrink-0" style="background-color: {{ $badge->text_color }}"></span>
                            <span class="font-mono text-[10px]">{{ $badge->text_color }}</span>
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Borde:</span>
                        <span class="flex items-center gap-1.5 text-white font-semibold">
                            <span class="w-3 h-3 rounded-full border border-gray-700 shrink-0" style="background-color: {{ $badge->border_color }}"></span>
                            <span class="font-mono text-[10px]">{{ $badge->border_color }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card Actions -->
            <div class="pt-3 border-t border-[#232936] flex items-center justify-between font-mono text-xs">
                <span class="text-[10px] text-gray-500 truncate">{{ $badge->slug }}</span>

                <div class="flex items-center gap-1">
                    <button @click="openEditModal({{ json_encode($badge) }})" class="p-1.5 rounded bg-[#0A0C0F] hover:bg-[#171B22] text-gray-300 hover:text-white border border-[#232936]" title="Editar">
                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                    </button>
                    
                    <form action="{{ route('admin.badges.destroy', $badge->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este badge?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded bg-rose-500/10 hover:bg-rose-600 text-rose-400 hover:text-white border border-rose-500/20" title="Eliminar">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                </div>
            </div>

        </div>
        @endforeach
    </div>

    <!-- Complete Interactive Badge Customizer Modal -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl max-w-xl w-full p-6 space-y-5 shadow-2xl max-h-[92vh] overflow-y-auto" @click.outside="openModal = false">
            
            <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                <h3 class="text-base font-bold text-white font-sans flex items-center gap-2" x-text="editMode ? 'Editar Insignia' : 'Crear Nueva Insignia'"></h3>
                <button @click="openModal = false" class="text-gray-400 hover:text-white p-1 rounded bg-[#0A0C0F]"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <!-- Real-time Live Badge Preview Box -->
            <div class="p-5 bg-[#0A0C0F] rounded-xl border border-[#232936] text-center space-y-2">
                <span class="text-[10px] font-mono uppercase text-gray-500 block">Vista Previa en Tiempo Real</span>
                <div class="py-2 flex items-center justify-center">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-lg text-xs font-mono font-bold shadow-md transition-all"
                          :style="{
                              backgroundColor: currentBadge.bg_color,
                              color: currentBadge.text_color,
                              borderColor: currentBadge.border_color,
                              borderWidth: '1px',
                              borderStyle: 'solid'
                          }">
                        <i :data-lucide="currentBadge.icon || 'shield-check'" :style="{ color: currentBadge.text_color }" class="w-4 h-4 shrink-0"></i>
                        <span x-text="currentBadge.name || 'Texto de Insignia'" :style="{ color: currentBadge.text_color }"></span>
                    </span>
                </div>
            </div>

            <form :action="editMode ? '{{ url('admin/badges') }}/' + currentBadge.id : '{{ route('admin.badges.store') }}'" method="POST" class="space-y-5 font-sans text-xs">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <input type="hidden" name="slug" x-model="currentBadge.slug">

                <!-- Badge Name Input -->
                <div>
                    <label class="block text-gray-400 mb-1 font-mono">Nombre de la Insignia *</label>
                    <input type="text" name="name" x-model="currentBadge.name" required placeholder="Ej. Verificado 60 FPS" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white focus:border-blue-500">
                </div>

                <!-- 1-Click Preset Palettes Selector -->
                <div class="space-y-2">
                    <label class="block text-gray-400 font-mono text-[11px] uppercase">Paletas Rápidas Prediseñadas (1 Clic)</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <template x-for="preset in presets" :key="preset.label">
                            <button type="button" 
                                    @click="applyPreset(preset)" 
                                    class="p-2 rounded-lg border text-left flex items-center justify-between gap-1 transition-all hover:scale-[1.02]"
                                    :style="`background-color: ${preset.bg}; color: ${preset.text}; border-color: ${preset.border}`">
                                <span class="text-[10px] font-mono font-bold truncate" x-text="preset.label"></span>
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="`background-color: ${preset.text}`"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Icon Picker Selector -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-gray-400 font-mono text-[11px] uppercase">Seleccionar Icono</label>
                        <span class="text-[11px] font-mono text-blue-400 font-bold" x-text="`Icono actual: ${currentBadge.icon}`"></span>
                    </div>
                    <input type="hidden" name="icon" x-model="currentBadge.icon">
                    
                    <div class="grid grid-cols-8 gap-2 bg-[#0A0C0F] p-3 rounded-xl border border-[#232936]">
                        <template x-for="ico in availableIcons" :key="ico">
                            <button type="button" 
                                    @click="selectIcon(ico)"
                                    :class="currentBadge.icon === ico ? 'bg-blue-600 text-white border-blue-400' : 'bg-[#11141A] text-gray-400 hover:text-white border-[#232936]'"
                                    class="p-2.5 rounded-lg border flex items-center justify-center transition-colors" 
                                    :title="ico">
                                <i :data-lucide="ico" class="w-4 h-4"></i>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Custom Hex Color Pickers -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono">
                    <div>
                        <label class="block text-gray-400 mb-1 text-[11px] font-bold uppercase">Color de Fondo</label>
                        <div class="flex items-center gap-2 bg-[#0A0C0F] border border-[#232936] rounded-lg p-1.5 focus-within:border-blue-500">
                            <input type="color" x-model="currentBadge.bg_color" class="w-7 h-7 rounded bg-transparent border-0 cursor-pointer">
                            <input type="text" name="bg_color" x-model="currentBadge.bg_color" class="w-full bg-transparent border-0 text-xs text-white font-mono font-bold focus:outline-none uppercase">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-1 text-[11px] font-bold uppercase">Color de Texto</label>
                        <div class="flex items-center gap-2 bg-[#0A0C0F] border border-[#232936] rounded-lg p-1.5 focus-within:border-blue-500">
                            <input type="color" x-model="currentBadge.text_color" class="w-7 h-7 rounded bg-transparent border-0 cursor-pointer">
                            <input type="text" name="text_color" x-model="currentBadge.text_color" :style="{ color: currentBadge.text_color }" class="w-full bg-transparent border-0 text-xs font-mono font-bold focus:outline-none uppercase">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-1 text-[11px] font-bold uppercase">Color de Borde</label>
                        <div class="flex items-center gap-2 bg-[#0A0C0F] border border-[#232936] rounded-lg p-1.5 focus-within:border-blue-500">
                            <input type="color" x-model="currentBadge.border_color" class="w-7 h-7 rounded bg-transparent border-0 cursor-pointer">
                            <input type="text" name="border_color" x-model="currentBadge.border_color" class="w-full bg-transparent border-0 text-xs text-white font-mono font-bold focus:outline-none uppercase">
                        </div>
                    </div>
                </div>

                <!-- Submit and Cancel -->
                <div class="flex justify-end gap-2 pt-3 border-t border-[#232936]">
                    <button type="button" @click="openModal = false" class="px-4 py-2.5 rounded-lg bg-[#171B22] text-gray-300">Cancelar</button>
                    <button type="submit" class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold transition-colors">Guardar Insignia</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function badgesManager() {
    return {
        openModal: false, 
        editMode: false, 
        currentBadge: { 
            id: null,
            name: 'Verificado 60 FPS', 
            slug: 'verificado-60-fps', 
            bg_color: '#064e3b', 
            text_color: '#34d399', 
            border_color: '#059669', 
            icon: 'shield-check' 
        },
        
        // Quick Color Presets
        presets: [
            { label: 'Verde Élite (Verificado)', bg: '#064e3b', text: '#34d399', border: '#059669' },
            { label: 'Azul Cyber (Oficial)', bg: '#1e3a8a', text: '#60a5fa', border: '#2563eb' },
            { label: 'Oro Premium (Joya)', bg: '#78350f', text: '#fbbf24', border: '#d97706' },
            { label: 'Rojo Carmesí (Traducción)', bg: '#881337', text: '#fb7185', border: '#e11d48' },
            { label: 'Púrpura Neón (Romhack)', bg: '#3b0764', text: '#c084fc', border: '#7c3aed' },
            { label: 'Cian Glaciar (No-Intro 1:1)', bg: '#083344', text: '#38bdf8', border: '#0891b2' },
            { label: 'Slate Oscuro (Retro)', bg: '#18181b', text: '#a1a1aa', border: '#3f3f46' },
            { label: 'Naranja Fuego (Top)', bg: '#7c2d12', text: '#fb923c', border: '#ea580c' }
        ],

        // Available Lucide Icons for Badges
        availableIcons: [
            'shield-check', 'award', 'check-circle', 'zap', 'star', 'sparkles', 
            'cpu', 'flame', 'globe', 'flag', 'disc', 'heart', 
            'tag', 'gem', 'crown', 'gamepad-2'
        ],

        openCreateModal() {
            this.editMode = false;
            this.currentBadge = {
                id: null,
                name: 'Nueva Insignia',
                slug: '',
                bg_color: '#064e3b',
                text_color: '#34d399',
                border_color: '#059669',
                icon: 'shield-check'
            };
            this.openModal = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        openEditModal(badge) {
            this.editMode = true;
            this.currentBadge = Object.assign({}, badge);
            this.openModal = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        applyPreset(p) {
            this.currentBadge.bg_color = p.bg;
            this.currentBadge.text_color = p.text;
            this.currentBadge.border_color = p.border;
        },

        selectIcon(iconName) {
            this.currentBadge.icon = iconName;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        }
    };
}
</script>
@endsection
