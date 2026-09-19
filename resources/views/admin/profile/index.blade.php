@extends('layouts.admin')

@section('title', 'Mi Perfil & Credenciales — Panel de Administración')

@section('content')
<div class="max-w-4xl space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-[#232936] pb-5">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans flex items-center gap-2.5">
                <i data-lucide="shield-check" class="w-7 h-7 text-blue-500"></i> Mi Cuenta & Seguridad
            </h1>
            <p class="text-xs text-gray-400 font-mono mt-1">
                Modifica tu correo de acceso, nombre de usuario y actualiza tu contraseña de administrador.
            </p>
        </div>

        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#0A0C0F] border border-[#232936] text-xs font-mono">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span class="text-gray-400">Sesión Activa:</span>
            <span class="text-white font-bold">{{ $user->role }}</span>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-mono flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-mono space-y-1">
        <div class="font-bold flex items-center gap-2 text-rose-300">
            <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i> Por favor corrige los siguientes errores:
        </div>
        <ul class="list-disc list-inside pl-1 space-y-0.5 text-gray-300">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Card 1: Información de Acceso y Perfil -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 space-y-5 shadow-xl">
                <div class="border-b border-[#232936] pb-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider font-sans flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4 text-blue-400"></i> Datos de Acceso
                    </h3>
                    <span class="text-[10px] font-mono text-gray-500">ID #{{ $user->id }}</span>
                </div>

                <!-- Nombre Completo -->
                <div>
                    <label class="block text-xs font-mono text-gray-300 mb-1.5 font-semibold">
                        Nombre Completo
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        value="{{ old('name', $user->name) }}" 
                        required 
                        class="w-full bg-[#0A0C0F] border border-[#232936] focus:border-blue-500 rounded-xl px-3.5 py-2.5 text-xs text-white font-sans focus:outline-none transition-colors"
                        placeholder="Super Administrador"
                    >
                </div>

                <!-- Nombre de Usuario -->
                <div>
                    <label class="block text-xs font-mono text-gray-300 mb-1.5 font-semibold">
                        Nombre de Usuario (Username)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-500 text-xs font-mono">@</span>
                        <input 
                            type="text" 
                            name="username" 
                            value="{{ old('username', $user->username) }}" 
                            required 
                            class="w-full bg-[#0A0C0F] border border-[#232936] focus:border-blue-500 rounded-xl pl-8 pr-3.5 py-2.5 text-xs text-white font-mono focus:outline-none transition-colors"
                            placeholder="admin"
                        >
                    </div>
                </div>

                <!-- Correo Electrónico -->
                <div>
                    <label class="block text-xs font-mono text-gray-300 mb-1.5 font-semibold flex items-center justify-between">
                        <span>Correo Electrónico de Inicio de Sesión</span>
                        <span class="text-[10px] text-blue-400 font-normal">Acceso Principal</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-500">
                            <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                        </span>
                        <input 
                            type="email" 
                            name="email" 
                            value="{{ old('email', $user->email) }}" 
                            required 
                            class="w-full bg-[#0A0C0F] border border-[#232936] focus:border-blue-500 rounded-xl pl-9 pr-3.5 py-2.5 text-xs text-white font-mono focus:outline-none transition-colors"
                            placeholder="tu@correo.com"
                        >
                    </div>
                    <p class="text-[11px] text-gray-500 font-sans mt-1.5">
                        Este será el correo que usarás para ingresar al panel y recibir alertas.
                    </p>
                </div>

                <div class="pt-2 border-t border-[#232936]/60 text-[11px] font-mono text-gray-400 flex items-center justify-between">
                    <span>Rol del Sistema:</span>
                    <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-bold">{{ $user->role }}</span>
                </div>
            </div>

            <!-- Card 2: Cambiar Contraseña -->
            <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-6 space-y-5 shadow-xl flex flex-col justify-between">
                <div class="space-y-5">
                    <div class="border-b border-[#232936] pb-3 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider font-sans flex items-center gap-2">
                            <i data-lucide="key-round" class="w-4 h-4 text-amber-400"></i> Actualizar Contraseña
                        </h3>
                        <span class="text-[10px] font-mono text-amber-400/80 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">Opcional</span>
                    </div>

                    <p class="text-xs text-gray-400 font-sans">
                        Deja estos campos vacíos si solo deseas cambiar tu correo o nombre.
                    </p>

                    <!-- Contraseña Actual -->
                    <div>
                        <label class="block text-xs font-mono text-gray-300 mb-1.5 font-semibold">
                            Contraseña Actual
                        </label>
                        <input 
                            type="password" 
                            name="current_password" 
                            class="w-full bg-[#0A0C0F] border border-[#232936] focus:border-amber-500 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:outline-none transition-colors"
                            placeholder="••••••••••••"
                        >
                        <p class="text-[10px] text-gray-500 font-mono mt-1">Requerido si vas a cambiar tu contraseña.</p>
                    </div>

                    <!-- Nueva Contraseña -->
                    <div>
                        <label class="block text-xs font-mono text-gray-300 mb-1.5 font-semibold">
                            Nueva Contraseña
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            class="w-full bg-[#0A0C0F] border border-[#232936] focus:border-amber-500 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:outline-none transition-colors"
                            placeholder="Mínimo 6 caracteres"
                        >
                    </div>

                    <!-- Confirmar Nueva Contraseña -->
                    <div>
                        <label class="block text-xs font-mono text-gray-300 mb-1.5 font-semibold">
                            Confirmar Nueva Contraseña
                        </label>
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            class="w-full bg-[#0A0C0F] border border-[#232936] focus:border-amber-500 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:outline-none transition-colors"
                            placeholder="Repite la nueva contraseña"
                        >
                    </div>
                </div>

                <div class="p-3 bg-amber-500/5 rounded-xl border border-amber-500/20 text-[11px] font-mono text-amber-300/80 flex items-start gap-2">
                    <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5 text-amber-400"></i>
                    <span>Asegúrate de recordar tu nueva contraseña para no perder el acceso a la administración.</span>
                </div>
            </div>

        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.dashboard') }}" class="px-5 py-3 rounded-xl bg-[#171B22] hover:bg-[#232936] text-gray-300 font-bold text-xs uppercase tracking-wider transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-black text-xs uppercase tracking-wider transition-all shadow-xl shadow-blue-600/30 flex items-center gap-2 cursor-pointer">
                <i data-lucide="save" class="w-4 h-4"></i> Guardar Cambios de Perfil
            </button>
        </div>

    </form>

</div>
@endsection
