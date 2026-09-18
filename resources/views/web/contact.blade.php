@extends('layouts.web')

@section('title', 'Contacto & Soporte — ' . \App\Models\Setting::get('site_name', 'ROMHUB'))

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-8 space-y-8">

    <!-- Header Banner -->
    <div class="bg-white border-2 border-[#1E1E1E] rounded-3xl p-6 sm:p-10 relative overflow-hidden shadow-sm">
        <div class="relative z-10 max-w-3xl space-y-3">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#FDF2F2] border border-[#FCA5A5] text-[#CE2D2D] text-xs font-mono font-bold">
                <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                <span>ATENCIÓN AL USUARIO & COMUNIDAD</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-[#18181B] tracking-tight font-sans">
                Centro de Contacto & Soporte
            </h1>
            <p class="text-sm text-gray-600 leading-relaxed font-sans">
                ¿Tienes alguna consulta, reporte de enlace caído, sugerencia de catálogo o solicitud DMCA? Envíanos un mensaje y te responderemos a la mayor brevedad.
            </p>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-mono flex items-center gap-2 shadow-sm font-bold">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0 text-emerald-600"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-xl bg-red-50 border border-red-300 text-red-800 text-xs font-mono space-y-1 shadow-sm font-bold">
        @foreach($errors->all() as $err)
            <div class="flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-red-600"></i>
                <span>{{ $err }}</span>
            </div>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Contact Form (7 cols) -->
        <div class="lg:col-span-7 bg-white border-2 border-[#1E1E1E] rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
            <div class="border-b border-[#E5E0D8] pb-3">
                <h2 class="text-sm font-bold text-[#18181B] font-mono uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4 text-[#CE2D2D]"></i>
                    Formulario de Mensaje Directo
                </h2>
                <p class="text-xs text-gray-500 font-sans mt-0.5">Completa los campos para contactar con nuestro equipo de administración.</p>
            </div>

            <form action="{{ route('contact.submit') }}" method="POST" class="space-y-4 font-sans text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-mono text-gray-600 mb-1 font-bold">Nombre o Alias <span class="text-[#CE2D2D]">*</span></label>
                        <input type="text" name="name" required value="{{ old('name', auth()->user()?->name) }}" placeholder="Tu nombre" class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-lg p-3 text-[#18181B] font-medium placeholder-gray-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-mono text-gray-600 mb-1 font-bold">Correo Electrónico <span class="text-[#CE2D2D]">*</span></label>
                        <input type="email" name="email" required value="{{ old('email', auth()->user()?->email) }}" placeholder="tu@correo.com" class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-lg p-3 text-[#18181B] font-medium placeholder-gray-400 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-mono text-gray-600 mb-1 font-bold">Motivo del Contacto <span class="text-[#CE2D2D]">*</span></label>
                    <select name="subject" required class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-lg p-3 text-[#18181B] font-bold font-mono focus:outline-none">
                        <option value="Reporte de Enlace Caído" {{ old('subject') === 'Reporte de Enlace Caído' ? 'selected' : '' }}>🔗 Reporte de Enlace Caído o Servidor Lento</option>
                        <option value="Sugerencia de Juego o Consola" {{ old('subject') === 'Sugerencia de Juego o Consola' ? 'selected' : '' }}>💡 Sugerencia de Catálogo (Nuevo ROM / Consola)</option>
                        <option value="Solicitud DMCA / Takedown" {{ old('subject') === 'Solicitud DMCA / Takedown' ? 'selected' : '' }}>⚖️ Notificación de Derechos de Autor (DMCA Takedown)</option>
                        <option value="Problema Técnico o Bug" {{ old('subject') === 'Problema Técnico o Bug' ? 'selected' : '' }}>🐛 Error Técnico en la Plataforma</option>
                        <option value="Consulta General" {{ old('subject') === 'Consulta General' ? 'selected' : '' }}>✉️ Otra Consulta General</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-mono text-gray-600 mb-1 font-bold">Mensaje <span class="text-[#CE2D2D]">*</span></label>
                    <textarea name="message" rows="5" required placeholder="Escribe los detalles de tu consulta. Si reportas un enlace, incluye el título y la URL..." class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-lg p-3 text-[#18181B] font-medium placeholder-gray-400 focus:outline-none leading-relaxed">{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-bold transition-colors flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Enviar Mensaje</span>
                </button>
            </form>
        </div>

        <!-- Info & Channels (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-6 space-y-4 shadow-sm">
                <h3 class="text-xs font-mono font-bold uppercase text-[#18181B] tracking-wider border-b border-[#E5E0D8] pb-3 flex items-center gap-2">
                    <i data-lucide="mail-check" class="w-4 h-4 text-[#CE2D2D]"></i>
                    Canal Directo por Correo
                </h3>
                <p class="text-xs text-gray-600 leading-relaxed font-sans">
                    También puedes escribirnos directamente desde tu cliente de correo favorito:
                </p>

                <div class="p-3.5 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] space-y-1">
                    <span class="text-[10px] font-mono uppercase text-gray-500 block font-bold">Buzón de Atención</span>
                    <a href="mailto:{{ $contactEmail }}" class="text-xs font-mono font-bold text-[#CE2D2D] hover:underline block truncate">
                        {{ $contactEmail }}
                    </a>
                </div>

                <div class="p-3.5 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] space-y-2">
                    <span class="text-[10px] font-mono uppercase text-gray-500 block font-bold">Horario de Revisión</span>
                    <div class="text-xs text-gray-700 font-sans space-y-1">
                        <div class="flex justify-between"><span>Lunes a Viernes:</span> <strong class="text-[#18181B] font-mono">09:00 - 19:00 UTC</strong></div>
                        <div class="flex justify-between"><span>Fines de Semana:</span> <strong class="text-[#CE2D2D] font-mono font-bold">Monitoreo activo</strong></div>
                    </div>
                </div>
            </div>

            <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-6 space-y-3 shadow-sm">
                <h3 class="text-xs font-mono font-bold uppercase text-[#18181B] tracking-wider border-b border-[#E5E0D8] pb-2 flex items-center gap-2">
                    <i data-lucide="shield" class="w-4 h-4 text-amber-600"></i>
                    ¿Asuntos de Copyright?
                </h3>
                <p class="text-xs text-gray-600 leading-relaxed font-sans">
                    Para reclamaciones de propiedad intelectual y avisos de retirada de contenido bajo la ley DMCA, consulta nuestra sección dedicada:
                </p>
                <a href="{{ route('dmca') }}" class="inline-flex items-center gap-1.5 text-xs font-mono text-[#CE2D2D] hover:underline font-bold pt-1">
                    <span>Ver Política DMCA</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

        </div>

    </div>

</main>
@endsection
