@if ($sesiones->isNotEmpty())
    <section class="sidan-panel hidden" data-panel="cronograma">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">Cronograma</p>
                    <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white">Tu itinerario, organizado por día</h2>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Abrí una sesión para ver sus detalles y los productos que le corresponden.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 dark:bg-white/10 dark:text-slate-300">
                    {{ $sesiones->count() }} {{ $sesiones->count() === 1 ? 'sesión' : 'sesiones' }} · {{ $sesionesPorDia->count() }} {{ $sesionesPorDia->count() === 1 ? 'día' : 'días' }}
                </span>
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-50 p-4 dark:bg-white/5">
                <p class="text-xs text-slate-500 dark:text-slate-400">Los productos de las sesiones y los generales se agregan al mismo carrito.</p>
                @auth
                    @if ($items->isNotEmpty())
                        <button type="submit" form="formCompraActividad" data-revisar-carrito disabled
                            class="rounded-xl bg-sidan-500 px-4 py-2.5 text-xs font-black text-white disabled:cursor-not-allowed disabled:opacity-40">
                            Revisar carrito
                        </button>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="text-sm font-bold text-sidan-500 hover:underline">Iniciar sesión para comprar</a>
                @endauth
            </div>

            @if ($compraBloqueadaPorInscripcion)
                <p class="mt-4 rounded-xl bg-amber-50 p-4 text-sm font-semibold text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
                    Para comprar productos, primero debés completar y confirmar tu inscripción en la actividad.
                </p>
            @endif
            @if ($errors->has('compra'))
                <p role="alert" class="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300">{{ $errors->first('compra') }}</p>
            @endif

            <div class="mt-6 flex gap-2 overflow-x-auto pb-2">
                @foreach ($sesionesPorDia as $claveDia => $sesionesDia)
                    @php $primeraSesionDia = $sesionesDia->first(); @endphp
                    <button type="button" data-dia-target="{{ $claveDia }}"
                        class="sidan-dia-tab shrink-0 rounded-xl border border-slate-200 px-4 py-3 text-left text-sm transition dark:border-white/10">
                        <span class="block text-[10px] font-black uppercase tracking-wide text-zinc-500">{{ ucfirst($primeraSesionDia['dia_semana']) }}</span>
                        <span class="mt-0.5 block font-black text-sidan-900 dark:text-white">{{ $primeraSesionDia['fecha_corta'] }}</span>
                    </button>
                @endforeach
            </div>

            <div class="mt-4">
                @foreach ($sesionesPorDia as $claveDia => $sesionesDia)
                    @php $primeraSesionDia = $sesionesDia->first(); @endphp
                    <div class="sidan-dia-panel {{ $claveDia === $diaInicialCronograma ? '' : 'hidden' }}" data-dia-panel="{{ $claveDia }}">
                        <div class="mb-4 flex items-center justify-between gap-3 rounded-2xl bg-sidan-950 px-5 py-4 text-white">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wide">{{ ucfirst($primeraSesionDia['dia_semana']) }}</p>
                                <p class="mt-1 text-xl font-black">{{ $primeraSesionDia['fecha_corta'] }}</p>
                            </div>
                            <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-black">{{ $sesionesDia->count() }} {{ $sesionesDia->count() === 1 ? 'sesión' : 'sesiones' }}</span>
                        </div>

                        <div class="space-y-4">
                            @foreach ($sesionesDia as $datosSesion)
                                @php $productosSesion = $itemsPorSesion[$datosSesion['id_sesion']] ?? collect(); @endphp
                                <details class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/60 dark:border-white/10 dark:bg-white/[0.025]">
                                    <summary class="cursor-pointer p-4 text-sidan-900 dark:text-white">
                                        <span class="ml-1 font-black">{{ $datosSesion['hora_inicio'] }} · {{ $datosSesion['nombre'] }}</span>
                                        <span class="mt-2 flex flex-wrap items-center gap-2 pl-5">
                                            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black uppercase text-slate-600 dark:bg-white/10 dark:text-slate-300">{{ $datosSesion['modalidad'] }}</span>
                                            @if ($datosSesion['obligatoria'])
                                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-black uppercase text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">Obligatoria</span>
                                            @endif
                                            @if ($productosSesion->isNotEmpty())
                                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $productosSesion->count() }} {{ $productosSesion->count() === 1 ? 'producto' : 'productos' }}</span>
                                            @endif
                                        </span>
                                        <span class="mt-2 block pl-5 text-sm text-slate-500 dark:text-slate-400">{{ $datosSesion['lugar'] }}</span>
                                    </summary>
                                    <div class="border-t border-slate-200 p-4 dark:border-white/10">
                                        <div class="grid gap-3 sm:grid-cols-3">
                                            @foreach (['Horario' => $datosSesion['hora_inicio'] . ($datosSesion['hora_fin'] ? ' – ' . $datosSesion['hora_fin'] : ''), 'Cupo' => $datosSesion['cupo'], 'Reserva' => $datosSesion['reserva']] as $etiqueta => $valor)
                                                <div class="rounded-xl bg-white p-3 dark:bg-white/5">
                                                    <p class="text-[11px] font-bold uppercase text-slate-400">{{ $etiqueta }}</p>
                                                    <p class="mt-1 text-sm font-bold text-sidan-900 dark:text-white">{{ $valor }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if ($productosSesion->isNotEmpty())
                                            <h3 class="mb-4 mt-6 text-base font-black text-sidan-900 dark:text-white">Productos de esta sesión</h3>
                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                @foreach ($productosSesion as $item)
                                                    @include('activities.partials.producto', ['item' => $item, 'ocultarProducto' => false])
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
