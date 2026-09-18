@extends('layouts.admin')

@section('title', 'Sagas & Franquicias Míticas — Panel de Administración')

@section('content')
<div class="space-y-6" x-data="franchisesManager()">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans flex items-center gap-2.5">
                <i data-lucide="sparkles" class="w-6 h-6 text-amber-400"></i> Sagas & Franquicias Míticas
            </h1>
            <p class="text-xs text-gray-400 font-mono mt-0.5">Crea colecciones, autocompleta datos con Gemini IA y gestiona la visibilidad pública</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('collections.index') }}" target="_blank" class="px-3 py-2 rounded-xl bg-[#11141A] hover:bg-[#171B22] border border-[#232936] text-gray-300 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="external-link" class="w-4 h-4 text-gray-400"></i> Ver en Web Pública
            </a>
            <button @click="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-wide flex items-center gap-2 transition-all shadow-lg shadow-blue-600/30">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Nueva Saga
            </button>
        </div>
    </div>

    <!-- Franchises Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($franchises as $fr)
        <div class="bg-[#11141A] border border-[#232936] hover:border-amber-500/40 rounded-2xl overflow-hidden flex flex-col justify-between transition-all group shadow-xl">
            
            <!-- Banner & Info Header -->
            <div class="h-36 bg-[#0A0C0F] relative overflow-hidden border-b border-[#232936]">
                <img src="{{ $fr->image ?: 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=700&fit=crop' }}" 
                     alt="{{ $fr->name }}" 
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 opacity-70">
                
                <div class="absolute inset-0 bg-gradient-to-t from-[#11141A] via-[#11141A]/60 to-transparent flex items-end p-4">
                    <div class="flex items-center gap-3 w-full">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-lg shrink-0" 
                             style="background-color: {{ $fr->color }};">
                            <i data-lucide="{{ $fr->icon ?: 'sparkles' }}" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-base font-black text-white truncate font-sans">{{ $fr->name }}</h2>
                            <p class="text-[11px] font-mono text-gray-300 truncate">{{ $fr->subtitle }}</p>
                        </div>
                        
                        <!-- 1-Click Fast Toggle Status -->
                        <form action="{{ route('admin.franchises.toggleStatus', $fr->id) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="px-2 py-0.5 rounded text-[10px] font-mono font-bold shrink-0 transition-all {{ $fr->is_active ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500 hover:text-white' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30 hover:bg-rose-500 hover:text-white' }}"
                                    title="Haz clic para {{ $fr->is_active ? 'Ocultar' : 'Publicar' }}">
                                {{ $fr->is_active ? 'PUBLICADA' : 'OCULTA' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Body Details -->
            <div class="p-4 space-y-3 flex-1 flex flex-col justify-between">
                <div class="space-y-2">
                    <p class="text-xs text-gray-400 font-sans line-clamp-2 leading-relaxed">{{ $fr->description }}</p>

                    <!-- Search Keywords Tags -->
                    @if($fr->search_terms)
                        <div class="space-y-1">
                            <span class="text-[10px] font-mono text-gray-500 uppercase font-bold flex items-center gap-1">
                                <i data-lucide="search" class="w-3 h-3 text-amber-400"></i> Palabras clave automáticas:
                            </span>
                            <div class="flex flex-wrap gap-1">
                                @foreach($fr->search_terms as $term)
                                    <span class="px-1.5 py-0.5 rounded bg-[#0A0C0F] border border-[#232936] text-[10px] font-mono text-amber-400">
                                        {{ $term }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer Actions -->
                <div class="pt-3 border-t border-[#232936] flex items-center justify-between font-mono text-xs">
                    <a href="{{ route('collections.show', $fr->slug) }}" target="_blank" class="text-amber-400 hover:underline flex items-center gap-1">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Ver en Web Pública
                    </a>

                    <div class="flex items-center gap-1.5">
                        @php
                            $frArray = $fr->toArray();
                            $frArray['search_terms'] = is_array($fr->search_terms) ? implode(', ', $fr->search_terms) : ($fr->search_terms ?? '');
                            $frArray['game_ids'] = $fr->games->pluck('id')->toArray();
                        @endphp
                        <button @click="openEditModal({{ json_encode($frArray) }})" class="p-1.5 rounded bg-[#0A0C0F] hover:bg-blue-600 text-gray-300 hover:text-white border border-[#232936] transition-colors" title="Editar">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </button>

                        <form action="{{ route('admin.franchises.destroy', $fr->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta saga del catálogo?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded bg-rose-500/10 hover:bg-rose-600 text-rose-400 hover:text-white border border-rose-500/20 transition-colors" title="Eliminar">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
        @empty
        <div class="col-span-full p-8 text-center text-gray-500 font-mono text-xs bg-[#11141A] rounded-2xl border border-[#232936]">
            No hay sagas registradas. Haz clic en "Nueva Saga" para crear una.
        </div>
        @endforelse
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl max-w-2xl w-full p-6 space-y-4 shadow-2xl my-8" @click.outside="openModal = false">
            
            <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                <h3 class="text-base font-bold text-white font-sans flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-5 h-5 text-amber-400"></i>
                    <span x-text="editMode ? 'Editar Saga / Franquicia' : 'Nueva Saga'"></span>
                </h3>
                <button @click="openModal = false" class="text-gray-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form :action="editMode ? '{{ url('admin/franchises') }}/' + currentFranchise.id : '{{ route('admin.franchises.store') }}'" method="POST" class="space-y-4 font-sans text-xs">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Saga Name with AI Helper Button -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-gray-400 font-mono">Nombre de la Saga *</label>
                        <button type="button" @click="generateWithAi()" class="px-2.5 py-1 rounded bg-purple-600/20 hover:bg-purple-600 text-purple-300 hover:text-white border border-purple-500/30 text-[11px] font-mono flex items-center gap-1 transition-colors">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                            <span x-text="aiLoading ? 'Generando con Gemini...' : '✨ Autocompletar con Gemini IA'"></span>
                        </button>
                    </div>
                    <input type="text" name="name" x-model="currentFranchise.name" @input="onNameChange()" required placeholder="Ej. Silent Hill, Final Fantasy, Need for Speed" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white focus:border-amber-500 focus:outline-none text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Slug URL (Opcional)</label>
                        <input type="text" name="slug" x-model="currentFranchise.slug" placeholder="silent-hill, final-fantasy" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Subtítulo / Lema</label>
                        <input type="text" name="subtitle" x-model="currentFranchise.subtitle" placeholder="Ej. El terror psicológico definitivo de Konami" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Color Temático</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="color" x-model="currentFranchise.color" class="w-10 h-9 rounded bg-transparent border border-[#232936] cursor-pointer">
                            <input type="text" x-model="currentFranchise.color" class="flex-1 bg-[#0A0C0F] border border-[#232936] rounded-lg p-2 text-white font-mono uppercase text-[11px]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Ícono Lucide</label>
                        <input type="text" name="icon" x-model="currentFranchise.icon" placeholder="sparkles, flame, swords, skull" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Orden de aparición</label>
                        <input type="number" name="order" x-model="currentFranchise.order" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono text-center">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-400 mb-1 font-mono">URL Imagen Banner de Portada</label>
                    <input type="url" name="image" x-model="currentFranchise.image" placeholder="https://images.unsplash.com/..." class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono">
                </div>

                <!-- Keywords Automatic Search -->
                <div class="p-3.5 bg-[#0A0C0F] border border-[#232936] rounded-xl space-y-1.5">
                    <label class="block text-amber-400 font-mono font-bold flex items-center gap-1.5">
                        <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                        <span>Palabras Clave para Búsqueda Automática (Recomendado)</span>
                    </label>
                    <input type="text" name="search_terms" x-model="currentFranchise.search_terms" placeholder="Silent Hill, Shattered Memories, Homecoming" class="w-full bg-[#11141A] border border-[#232936] rounded-lg p-2.5 text-amber-400 font-mono text-xs">
                    <p class="text-[10px] font-mono text-gray-500">El sistema buscará automáticamente en la base de datos todos los videojuegos que contengan alguna de estas palabras en su título.</p>
                </div>

                <div>
                    <label class="block text-gray-400 mb-1 font-mono">Descripción de la Saga</label>
                    <textarea name="description" x-model="currentFranchise.description" rows="2" placeholder="Reseña histórica de la saga..." class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white"></textarea>
                </div>

                <!-- Manual Game Link Picker -->
                <div>
                    <label class="block text-gray-400 mb-1 font-mono">Vincular Juegos Manualmente (Opcional):</label>
                    <select name="game_ids[]" multiple size="3" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2 text-white font-mono text-xs">
                        @foreach($games as $game)
                            <option value="{{ $game->id }}" :selected="currentFranchise.game_ids && currentFranchise.game_ids.includes({{ $game->id }})">
                                {{ $game->title }} [{{ $game->console->name ?? 'Consola' }}]
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="currentFranchise.is_active" class="rounded bg-[#0A0C0F] border-[#232936] text-amber-500 focus:ring-0 w-4 h-4">
                        <span class="text-gray-300 font-mono font-bold">Publicar en la Web Pública</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-[#232936]">
                    <button type="button" @click="openModal = false" class="px-4 py-2 rounded-lg bg-[#171B22] text-gray-300">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-bold">Guardar y Publicar</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function franchisesManager() {
    return {
        openModal: false,
        editMode: false,
        aiLoading: false,
        currentFranchise: {
            id: null,
            name: '',
            slug: '',
            subtitle: '',
            description: '',
            image: '',
            icon: 'sparkles',
            color: '#CE2D2D',
            search_terms: '',
            game_ids: [],
            is_active: true,
            order: 0
        },

        openCreateModal() {
            this.editMode = false;
            this.currentFranchise = {
                id: null,
                name: '',
                slug: '',
                subtitle: '',
                description: '',
                image: '',
                icon: 'sparkles',
                color: '#CE2D2D',
                search_terms: '',
                game_ids: [],
                is_active: true,
                order: {{ count($franchises) + 1 }}
            };
            this.openModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        openEditModal(data) {
            this.editMode = true;
            this.currentFranchise = data;
            this.openModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        onNameChange() {
            if (!this.editMode) {
                this.currentFranchise.slug = this.currentFranchise.name
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');

                if (!this.currentFranchise.search_terms) {
                    this.currentFranchise.search_terms = this.currentFranchise.name;
                }
            }
        },

        async generateWithAi() {
            if (!this.currentFranchise.name || !this.currentFranchise.name.trim()) {
                alert('Escribe primero el nombre de la saga (ej. Silent Hill, Need for Speed).');
                return;
            }

            this.aiLoading = true;
            try {
                const response = await fetch('{{ route('admin.ai.franchise') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ name: this.currentFranchise.name })
                });

                const data = await response.json();
                if (data.success) {
                    if (data.subtitle) this.currentFranchise.subtitle = data.subtitle;
                    if (data.description) this.currentFranchise.description = data.description;
                    if (data.color) this.currentFranchise.color = data.color;
                    if (data.icon) this.currentFranchise.icon = data.icon;
                    if (data.search_terms) this.currentFranchise.search_terms = data.search_terms;
                    this.onNameChange();
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.aiLoading = false;
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            }
        }
    };
}
</script>
@endsection
