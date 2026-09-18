@extends('layouts.web')

@section('title', 'Registro de Usuario — ' . \App\Models\Setting::get('site_name', 'ROMHUB'))

@section('content')
<main class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md bg-white border-2 border-[#1E1E1E] rounded-3xl p-8 space-y-6 shadow-sm relative overflow-hidden">
        
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-[#CE2D2D] flex items-center justify-center text-white mx-auto shadow-md">
                <i data-lucide="user-plus" class="w-6 h-6"></i>
            </div>
            <h1 class="text-2xl font-black text-[#18181B] tracking-tight font-sans">Crear Cuenta</h1>
            <p class="text-xs text-gray-500 font-sans">Únete a la comunidad de preservación y guarda tus favoritos</p>
        </div>

        @if(isset($errors) && $errors->any())
        <div class="p-3 bg-red-50 border border-red-300 rounded-xl text-xs text-red-800 space-y-1 font-sans font-bold">
            @foreach($errors->all() as $err)
            <p>• {{ $err }}</p>
            @endforeach
        </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-mono text-gray-600 mb-1 font-bold">Nombre Completo</label>
                <input 
                    type="text" 
                    name="name" 
                    value="{{ old('name') }}" 
                    required 
                    autofocus
                    placeholder="Alex Retro"
                    class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-xl p-3 text-sm text-[#18181B] font-medium focus:outline-none font-sans"
                >
            </div>

            <div>
                <label class="block text-xs font-mono text-gray-600 mb-1 font-bold">Nombre de Usuario (Nickname)</label>
                <input 
                    type="text" 
                    name="username" 
                    value="{{ old('username') }}" 
                    required 
                    placeholder="alex_retro"
                    class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-xl p-3 text-sm text-[#18181B] font-medium focus:outline-none font-sans"
                >
            </div>

            <div>
                <label class="block text-xs font-mono text-gray-600 mb-1 font-bold">Correo Electrónico</label>
                <input 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    placeholder="alex@ejemplo.com"
                    class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-xl p-3 text-sm text-[#18181B] font-medium focus:outline-none font-sans"
                >
            </div>

            <div>
                <label class="block text-xs font-mono text-gray-600 mb-1 font-bold">Contraseña</label>
                <input 
                    type="password" 
                    name="password" 
                    required 
                    placeholder="Mínimo 8 caracteres"
                    class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-xl p-3 text-sm text-[#18181B] font-medium focus:outline-none font-sans"
                >
            </div>

            <div>
                <label class="block text-xs font-mono text-gray-600 mb-1 font-bold">Confirmar Contraseña</label>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    required 
                    placeholder="Repite tu contraseña"
                    class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-xl p-3 text-sm text-[#18181B] font-medium focus:outline-none font-sans"
                >
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-bold text-xs uppercase tracking-wide transition-colors shadow-sm cursor-pointer">
                Registrarse & Comenzar
            </button>
        </form>

        <div class="pt-4 border-t border-[#E5E0D8] text-center text-xs font-mono text-gray-500">
            ¿Ya tienes una cuenta? <a href="{{ route('login') }}" class="text-[#CE2D2D] font-bold hover:underline">Inicia sesión</a>
        </div>

    </div>
</main>
@endsection
