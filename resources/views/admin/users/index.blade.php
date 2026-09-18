@extends('layouts.admin')

@section('title', 'Usuarios & Roles — Administración ROMHUB')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans">Usuarios & Control de Roles (RBAC)</h1>
            <p class="text-xs text-gray-400 font-mono mt-0.5">Gestión de cuentas de preservadores, moderadores y superadministradores</p>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-[#11141A] border border-[#232936] rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sans">
                <thead class="bg-[#0A0C0F] border-b border-[#232936] text-[10px] font-mono uppercase text-gray-400">
                    <tr>
                        <th class="py-3.5 px-4">Usuario</th>
                        <th class="py-3.5 px-3">Correo</th>
                        <th class="py-3.5 px-3">Nivel / XP</th>
                        <th class="py-3.5 px-3">Rol del Sistema</th>
                        <th class="py-3.5 px-3">Estado</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#232936]/60 font-mono">
                    @foreach($users as $user)
                    <tr class="hover:bg-[#171B22]/50 transition-colors">
                        
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                                <div class="font-sans">
                                    <span class="font-bold text-white block">{{ $user->name }}</span>
                                    <span class="text-[10px] font-mono text-gray-500">&#64;{{ $user->username }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="py-3 px-3 text-gray-300">{{ $user->email }}</td>

                        <td class="py-3 px-3">
                            <span class="text-emerald-400 font-bold">Nv. {{ $user->level }}</span>
                            <span class="text-gray-500 text-[10px] ml-1">({{ $user->xp }} XP)</span>
                        </td>

                        <!-- Role Switcher Form -->
                        <td class="py-3 px-3">
                            <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="role" onchange="this.form.submit()" class="bg-[#0A0C0F] border border-[#232936] rounded px-2 py-1 text-[11px] text-gray-200">
                                    <option value="ADMIN" {{ $user->role === 'ADMIN' ? 'selected' : '' }}>ADMIN</option>
                                    <option value="MODERATOR" {{ $user->role === 'MODERATOR' ? 'selected' : '' }}>MODERATOR</option>
                                    <option value="USER" {{ $user->role === 'USER' ? 'selected' : '' }}>USER</option>
                                </select>
                            </form>
                        </td>

                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded {{ $user->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }} text-[10px] font-bold">
                                {{ $user->is_active ? 'ACTIVO' : 'SUSPENDIDO' }}
                            </span>
                        </td>

                        <td class="py-3 px-4 text-right">
                            <form action="{{ route('admin.users.toggleStatus', $user->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded bg-[#0A0C0F] hover:bg-[#171B22] border border-[#232936] text-[11px] text-gray-300 transition-colors">
                                    {{ $user->is_active ? 'Suspender' : 'Activar' }}
                                </button>
                            </form>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-[#232936]">
            {{ $users->links() }}
        </div>
    </div>

</div>
@endsection
