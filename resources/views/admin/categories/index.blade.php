@extends('layouts.admin')

@section('title', 'Categorías & Géneros — Administración ROMHUB')

@section('content')
<div class="space-y-6" x-data="{ openModal: false, editMode: false, currentCategory: {} }">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans">Categorías & Géneros</h1>
            <p class="text-xs text-gray-400 font-mono mt-0.5">Taxonomía para filtrado y búsqueda de videojuegos</p>
        </div>
        <button @click="editMode = false; currentCategory = { name: '', slug: '', description: '' }; openModal = true" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-wide flex items-center gap-2 transition-all shadow-lg shadow-blue-600/30">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Nueva Categoría
        </button>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($categories as $category)
        <div class="bg-[#11141A] border border-[#232936] hover:border-blue-500/50 rounded-2xl p-5 flex flex-col justify-between space-y-4">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="p-2 rounded-lg bg-[#0A0C0F] text-blue-400 border border-[#232936]">
                        <i data-lucide="tag" class="w-4 h-4"></i>
                    </span>
                    <span class="text-xs font-mono text-emerald-400 font-bold">{{ $category->games_count }} Juegos</span>
                </div>
                <h2 class="text-base font-bold text-white font-sans">{{ $category->name }}</h2>
                <p class="text-xs text-gray-400 font-mono">{{ $category->slug }}</p>
            </div>

            <div class="pt-3 border-t border-[#232936] flex items-center justify-between font-mono text-xs">
                <a href="{{ route('search', ['category' => $category->slug]) }}" target="_blank" class="text-gray-400 hover:text-blue-400 flex items-center gap-1">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Explorar
                </a>

                <div class="flex items-center gap-1">
                    <button @click="editMode = true; currentCategory = {{ json_encode($category) }}; openModal = true" class="p-1.5 rounded bg-[#0A0C0F] hover:bg-[#171B22] text-gray-300 hover:text-white border border-[#232936]">
                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                    </button>
                    
                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta categoría?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded bg-rose-500/10 hover:bg-rose-600 text-rose-400 hover:text-white border border-rose-500/20">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Modal -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl" @click.outside="openModal = false">
            <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                <h3 class="text-base font-bold text-white font-sans" x-text="editMode ? 'Editar Categoría' : 'Nueva Categoría'"></h3>
                <button @click="openModal = false" class="text-gray-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form :action="editMode ? '{{ url('admin/categories') }}/' + currentCategory.id : '{{ route('admin.categories.store') }}'" method="POST" class="space-y-4 font-sans text-xs">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-gray-400 mb-1 font-mono">Nombre de la Categoría *</label>
                    <input type="text" name="name" x-model="currentCategory.name" required placeholder="Ej. Aventura Gráfica" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                </div>

                <div>
                    <label class="block text-gray-400 mb-1 font-mono">Slug (Opcional)</label>
                    <input type="text" name="slug" x-model="currentCategory.slug" placeholder="ej. aventura-grafica" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-[#232936]">
                    <button type="button" @click="openModal = false" class="px-4 py-2 rounded-lg bg-[#171B22] text-gray-300">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white font-bold">Guardar</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
