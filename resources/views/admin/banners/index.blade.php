@extends('layouts.admin')

@section('title', 'Banners & Hero Carousel — Administración ROMHUB')

@section('content')
<div class="space-y-6" x-data="{ openModal: false }">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans">Banners & Carrusel Principal</h1>
            <p class="text-xs text-gray-400 font-mono mt-0.5">Administra los slides destacados y promociones de la página principal</p>
        </div>
        <button @click="openModal = true" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-wide flex items-center gap-2 transition-all shadow-lg shadow-blue-600/30">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Nuevo Banner
        </button>
    </div>

    <!-- Banners Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($banners as $banner)
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl overflow-hidden space-y-3 shadow-xl">
            <div class="aspect-[16/9] bg-[#0A0C0F] relative">
                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-[#11141A] via-transparent to-transparent"></div>
                <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded bg-blue-600 text-white text-[10px] font-mono font-bold">Orden #{{ $banner->order }}</span>
                    <span class="px-2 py-0.5 rounded {{ $banner->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }} text-[10px] font-mono font-bold">
                        {{ $banner->is_active ? 'ACTIVO' : 'INACTIVO' }}
                    </span>
                </div>
            </div>

            <div class="p-4 space-y-2">
                <h3 class="text-base font-bold text-white font-sans">{{ $banner->title }}</h3>
                <p class="text-xs text-gray-400 font-sans">{{ $banner->subtitle }}</p>
                <p class="text-xs text-blue-400 font-mono truncate">Enlace: {{ $banner->link_url }}</p>
            </div>

            <div class="px-4 pb-4 flex justify-end">
                <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este banner?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-600 text-rose-400 hover:text-white text-xs font-mono transition-colors flex items-center gap-1.5">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-2 p-12 text-center bg-[#11141A] border border-[#232936] rounded-2xl text-xs font-mono text-gray-500">
            No hay banners configurados. Crea uno para destacar lanzamientos en la portada.
        </div>
        @endforelse
    </div>

    <!-- Create Banner Modal -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl" @click.outside="openModal = false">
            <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                <h3 class="text-base font-bold text-white font-sans">Nuevo Banner Hero</h3>
                <button @click="openModal = false" class="text-gray-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 font-sans text-xs">
                @csrf
                <div>
                    <label class="block text-gray-400 mb-1 font-mono">Título Principal *</label>
                    <input type="text" name="title" required placeholder="Ej. Zelda: Tears of the Kingdom" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                </div>

                <div>
                    <label class="block text-gray-400 mb-1 font-mono">Subtítulo / Tagline</label>
                    <input type="text" name="subtitle" placeholder="Ej. Emulación 60 FPS 4K Verificada" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                </div>

                <div>
                    <label class="block text-gray-400 mb-1 font-mono">URL de Destino</label>
                    <input type="url" name="link_url" placeholder="https://..." class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono">
                </div>

                <div>
                    <label class="block text-gray-400 mb-1 font-mono">Imagen Banner (16:9 Panorámica) *</label>
                    <input type="file" name="banner_image" accept="image/*" required class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2 text-gray-300">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Orden</label>
                        <input type="number" name="order" value="0" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono">
                    </div>
                    <div class="flex items-center pt-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded bg-[#0A0C0F] border-[#232936] text-blue-600">
                            <span class="text-gray-300">Banner Activo</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-[#232936]">
                    <button type="button" @click="openModal = false" class="px-4 py-2 rounded-lg bg-[#171B22] text-gray-300">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white font-bold">Subir Banner WebP</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
