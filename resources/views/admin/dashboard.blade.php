@extends('layouts.navbars')

@section('title', 'Panel de Administrador')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Panel de Administrador</h1>
            <p class="text-sm text-gray-400 mt-1">Bienvenido de vuelta, {{ auth()->user()->nombres }} 👋</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 text-sm font-medium text-gray-300 bg-white/5 border border-white/10 rounded-lg hover:bg-white/10 transition">
                Exportar
            </button>
            <button class="px-4 py-2 text-sm font-medium text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 shadow-lg shadow-emerald-500/20 transition">
                + Nueva actividad
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-[#0f172a] border border-white/5 rounded-2xl p-6 hover:border-emerald-500/30 transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <span class="text-xs font-medium text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-full">+12%</span>
            </div>
            <p class="text-sm text-gray-400">Actividades activas</p>
            <p class="text-3xl font-bold text-white mt-1">24</p>
        </div>

        <div class="bg-[#0f172a] border border-white/5 rounded-2xl p-6 hover:border-blue-500/30 transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <span class="text-xs font-medium text-blue-400 bg-blue-500/10 px-2 py-1 rounded-full">+5%</span>
            </div>
            <p class="text-sm text-gray-400">Estudiantes</p>
            <p class="text-3xl font-bold text-white mt-1">156</p>
        </div>

        <div class="bg-[#0f172a] border border-white/5 rounded-2xl p-6 hover:border-yellow-500/30 transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-yellow-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <span class="text-xs font-medium text-yellow-400 bg-yellow-500/10 px-2 py-1 rounded-full">Pendiente</span>
            </div>
            <p class="text-sm text-gray-400">Pagos este mes</p>
            <p class="text-3xl font-bold text-white mt-1">$3,280</p>
        </div>

        <div class="bg-[#0f172a] border border-white/5 rounded-2xl p-6 hover:border-purple-500/30 transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <span class="text-xs font-medium text-purple-400 bg-purple-500/10 px-2 py-1 rounded-full">+18%</span>
            </div>
            <p class="text-sm text-gray-400">Reportes generados</p>
            <p class="text-3xl font-bold text-white mt-1">48</p>
        </div>
    </div>

    {{-- Grid: Gráfico + Actividad reciente --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Gráfico placeholder --}}
        <div class="lg:col-span-2 bg-[#0f172a] border border-white/5 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-white">Actividad reciente</h2>
                    <p class="text-sm text-gray-400">Últimos 7 días</p>
                </div>
                <select class="bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-gray-300 focus:outline-none focus:border-emerald-500/50">
                    <option>Esta semana</option>
                    <option>Este mes</option>
                    <option>Este año</option>
                </select>
            </div>
            <div class="h-64 flex items-end justify-between gap-2 px-2">
                @php $bars = [40, 65, 45, 80, 55, 90, 70]; @endphp
                @foreach($bars as $i => $h)
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <div class="w-full bg-gradient-to-t from-emerald-500 to-emerald-400 rounded-t-lg transition-all hover:from-emerald-400 hover:to-emerald-300" 
                             style="height: {{ $h }}%"></div>
                        <span class="text-xs text-gray-500">{{ ['L','M','M','J','V','S','D'][$i] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Actividad reciente --}}
        <div class="bg-[#0f172a] border border-white/5 rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-white mb-6">Actividad reciente</h2>
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-white">Nuevo pago registrado</p>
                        <p class="text-xs text-gray-500 mt-0.5">Juan Pérez · $150.00</p>
                        <p class="text-xs text-gray-600 mt-1">Hace 2 horas</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-white">Actividad creada</p>
                        <p class="text-xs text-gray-500 mt-0.5">Congreso de Innovación 2026</p>
                        <p class="text-xs text-gray-600 mt-1">Hace 5 horas</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-purple-500/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-white">Nuevo estudiante</p>
                        <p class="text-xs text-gray-500 mt-0.5">María González se registró</p>
                        <p class="text-xs text-gray-600 mt-1">Ayer</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-yellow-500/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-white">Recordatorio enviado</p>
                        <p class="text-xs text-gray-500 mt-0.5">12 estudiantes notificados</p>
                        <p class="text-xs text-gray-600 mt-1">Hace 2 días</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla rápida --}}
    <div class="bg-[#0f172a] border border-white/5 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
            <h2 class="text-lg font-semibold text-white">Próximas actividades</h2>
            <a href="#" class="text-sm text-emerald-400 hover:text-emerald-300 transition">Ver todas →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white/[0.02]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actividad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cupos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <tr class="hover:bg-white/[0.02] transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white font-bold text-sm">CI</div>
                                <div>
                                    <p class="text-sm font-medium text-white">Congreso de Innovación</p>
                                    <p class="text-xs text-gray-500">San Miguel, El Salvador</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300">18 Oct · 8:00 AM</td>
                        <td class="px-6 py-4 text-sm text-gray-300">45/100</td>
                        <td class="px-6 py-4"><span class="px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Activa</span></td>
                    </tr>
                    <tr class="hover:bg-white/[0.02] transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-sm">TC</div>
                                <div>
                                    <p class="text-sm font-medium text-white">Taller de Cocina Creativa</p>
                                    <p class="text-xs text-gray-500">San Miguel, El Salvador</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300">20 Oct · 2:00 PM</td>
                        <td class="px-6 py-4 text-sm text-gray-300">18/20</td>
                        <td class="px-6 py-4"><span class="px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">Por llenar</span></td>
                    </tr>
                    <tr class="hover:bg-white/[0.02] transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white font-bold text-sm">ER</div>
                                <div>
                                    <p class="text-sm font-medium text-white">Excursión: Ruta de Montaña</p>
                                    <p class="text-xs text-gray-500">Morazán, El Salvador</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300">25 Oct · 5:30 AM</td>
                        <td class="px-6 py-4 text-sm text-gray-300">30/50</td>
                        <td class="px-6 py-4"><span class="px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Activa</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection