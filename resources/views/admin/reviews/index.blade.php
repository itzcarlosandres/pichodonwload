@extends('layouts.admin')

@section('title', 'Moderación de Reseñas — Administración ROMHUB')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans">Moderación de Reseñas & Benchmarks</h1>
            <p class="text-xs text-gray-400 font-mono mt-0.5">Aprueba o rechaza los reportes de rendimiento enviados por la comunidad</p>
        </div>
    </div>

    <!-- Reviews Table -->
    <div class="bg-[#11141A] border border-[#232936] rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sans">
                <thead class="bg-[#0A0C0F] border-b border-[#232936] text-[10px] font-mono uppercase text-gray-400">
                    <tr>
                        <th class="py-3.5 px-4">Juego</th>
                        <th class="py-3.5 px-3">Usuario</th>
                        <th class="py-3.5 px-3">Calificación</th>
                        <th class="py-3.5 px-4">Comentario & Emulador</th>
                        <th class="py-3.5 px-3">Estado</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#232936]/60">
                    @forelse($reviews as $review)
                    <tr class="hover:bg-[#171B22]/50 transition-colors">
                        
                        <td class="py-3.5 px-4">
                            <span class="font-bold text-white block truncate max-w-xs">{{ $review->game->title ?? 'Juego Eliminado' }}</span>
                            <span class="text-[10px] font-mono text-blue-400">{{ $review->game->console->name ?? '' }}</span>
                        </td>

                        <td class="py-3.5 px-3 font-mono text-gray-300">
                            {{ $review->author_name ?: ($review->user->name ?? 'Invitado') }}
                        </td>

                        <td class="py-3.5 px-3 font-mono text-amber-400 font-bold">
                            ★ {{ $review->score }}/5
                        </td>

                        <td class="py-3.5 px-4">
                            <p class="text-xs text-gray-200 line-clamp-2">{{ $review->comment }}</p>
                            <div class="flex items-center gap-2 text-[10px] font-mono text-gray-400 mt-1">
                                <span>Emulador: <strong class="text-gray-300">{{ $review->tested_emulator ?: 'Genérico' }}</strong></span>
                                <span>•</span>
                                <span>FPS: <strong class="text-emerald-400">{{ $review->fps_performance ?: '60 FPS' }}</strong></span>
                            </div>
                        </td>

                        <td class="py-3.5 px-3 font-mono">
                            <span class="px-2 py-0.5 rounded {{ $review->is_approved ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' }} text-[10px] font-bold">
                                {{ $review->is_approved ? 'APROBADO' : 'PENDIENTE' }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5 font-mono">
                                @if(!$review->is_approved)
                                <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold">
                                        Aprobar
                                    </button>
                                </form>
                                @endif

                                <form action="{{ route('admin.reviews.reject', $review->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Rechazar y eliminar esta reseña?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded bg-rose-500/10 hover:bg-rose-600 text-rose-400 hover:text-white border border-rose-500/20 text-[11px]">
                                        Rechazar
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-gray-500 font-mono text-xs">
                            No hay reseñas pendientes en la cola de moderación.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-[#232936]">
            {{ $reviews->links() }}
        </div>
    </div>

</div>
@endsection
