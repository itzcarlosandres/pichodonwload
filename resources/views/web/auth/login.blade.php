@extends('layouts.web')

@section('title', 'Acceso Administrativo — ' . \App\Models\Setting::get('site_name', 'PICHO Roms'))

@section('content')
<main class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md bg-white border-2 border-[#1E1E1E] rounded-3xl p-8 space-y-6 shadow-sm relative overflow-hidden">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-[#CE2D2D] flex items-center justify-center text-white mx-auto shadow-md">
                <i data-lucide="shield-check" class="w-7 h-7"></i>
            </div>
            <span class="inline-block px-2.5 py-0.5 rounded-full bg-red-100 text-[#CE2D2D] font-mono text-[10px] font-bold tracking-wider uppercase">Portal Administrativo</span>
            <h1 class="text-2xl font-black text-[#18181B] tracking-tight font-sans">Acceso al Panel</h1>
            <p class="text-xs text-gray-500 font-sans">Ingresa tus credenciales de administrador</p>
        </div>

        @if(session('info'))
        <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 font-sans font-medium flex items-center gap-2">
            <i data-lucide="info" class="w-4 h-4 shrink-0 text-blue-600"></i>
            <span>{{ session('info') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-800 font-sans font-medium flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-red-600"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if(isset($errors) && $errors->any())
        <div class="p-3 bg-red-50 border border-red-300 rounded-xl text-xs text-red-800 space-y-1 font-sans font-bold">
            @foreach($errors->all() as $err)
            <p>• {{ $err }}</p>
            @endforeach
        </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-mono text-gray-600 mb-1 font-bold">Correo Electrónico</label>
                <input 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus
                    placeholder="admin@correo.com"
                    class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-xl p-3 text-sm text-[#18181B] font-medium focus:outline-none font-sans"
                >
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-mono text-gray-600 font-bold">Contraseña</label>
                </div>
                <input 
                    type="password" 
                    name="password" 
                    required 
                    placeholder="••••••••"
                    class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-xl p-3 text-sm text-[#18181B] font-medium focus:outline-none font-sans"
                >
            </div>

            <div class="flex items-center justify-between text-xs font-mono text-gray-600 font-bold">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-[#FAF7F2] border-[#DDD6CB] text-[#CE2D2D] focus:ring-0">
                    <span>Recordar sesión</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-bold text-xs uppercase tracking-wide transition-colors shadow-sm cursor-pointer flex items-center justify-center gap-2">
                <i data-lucide="lock" class="w-4 h-4"></i> Ingresar al Panel
            </button>
        </form>

        <div class="pt-4 border-t border-[#E5E0D8] text-center text-[11px] font-mono text-gray-500 flex items-center justify-center gap-1.5">
            <i data-lucide="shield" class="w-3.5 h-3.5 text-gray-400"></i>
            <span>Acceso exclusivo para personal autorizado</span>
        </div>

    </div>
</main>
@endsection
