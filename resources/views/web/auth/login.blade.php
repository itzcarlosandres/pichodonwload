@extends('layouts.web')

@section('title', 'Iniciar Sesión — ' . \App\Models\Setting::get('site_name', 'ROMHUB'))

@section('content')
<main class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md bg-white border-2 border-[#1E1E1E] rounded-3xl p-8 space-y-6 shadow-sm relative overflow-hidden">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-[#CE2D2D] flex items-center justify-center text-white mx-auto shadow-md">
                <i data-lucide="disc" class="w-7 h-7"></i>
            </div>
            <h1 class="text-2xl font-black text-[#18181B] tracking-tight font-sans">Acceso a la Bóveda</h1>
            <p class="text-xs text-gray-500 font-sans">Inicia sesión con tus credenciales</p>
        </div>

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
                    placeholder="admin@romhub.io"
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

            <button type="submit" class="w-full py-3.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-bold text-xs uppercase tracking-wide transition-colors shadow-sm cursor-pointer">
                Ingresar al Sistema
            </button>
        </form>

        <div class="pt-4 border-t border-[#E5E0D8] text-center text-xs font-mono text-gray-500 space-y-2">
            <p>¿No tienes una cuenta? <a href="{{ route('register') }}" class="text-[#CE2D2D] font-bold hover:underline">Regístrate gratis</a></p>
            <div class="p-2.5 bg-[#FAF7F2] rounded-lg border border-[#DDD6CB] text-[10px] text-gray-600 text-left">
                <span class="text-[#CE2D2D] font-bold block mb-0.5">Credenciales demo:</span>
                Admin: admin@romhub.io / AdminPass123!<br>
                User: alex_retro@gmail.com / UserPass123!
            </div>
        </div>

    </div>
</main>
@endsection
