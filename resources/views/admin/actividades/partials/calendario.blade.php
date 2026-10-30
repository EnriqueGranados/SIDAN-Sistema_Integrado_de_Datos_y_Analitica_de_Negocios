{{-- Selector de fecha y hora reutilizable para el módulo de Actividades.
     No cambia nombres, tipos, validaciones ni formato de los inputs originales. --}}
@once
    <div id="sidanUniversalCalendarPicker"
        class="fixed z-[500] hidden w-[calc(100vw-2rem)] max-w-[23rem] overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/15 dark:border-white/10 dark:bg-[#0f172a] dark:shadow-black/40">
        <div class="border-b border-slate-200 px-4 py-4 dark:border-white/10">
            <div class="flex items-center gap-2">
                <button type="button" id="sidanCalendarPrevMonth"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-100 hover:text-sidan-600 dark:border-white/10 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-sidan-300"
                    aria-label="Mes anterior">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="grid min-w-0 flex-1 grid-cols-[1fr_auto] gap-2">
                    <select id="sidanCalendarMonth"
                        class="min-w-0 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-800 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-sidan-500/10 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                    </select>

                    <select id="sidanCalendarYear"
                        class="w-[6.25rem] rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-800 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-sidan-500/10 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                    </select>
                </div>

                <button type="button" id="sidanCalendarNextMonth"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-100 hover:text-sidan-600 dark:border-white/10 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-sidan-300"
                    aria-label="Mes siguiente">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-4">
            <div class="mb-2 grid grid-cols-7 gap-1 text-center">
                @foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $diaSemana)
                    <span class="py-1 text-[10px] font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">
                        {{ $diaSemana }}
                    </span>
                @endforeach
            </div>

            <div id="sidanCalendarDays" class="grid grid-cols-7 gap-1"></div>

            <div class="my-4 h-px bg-slate-200 dark:bg-white/10"></div>

            <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Hora
                    </p>
                    <p id="sidanCalendarSelectedDateLabel"
                        class="text-xs font-medium text-sidan-600 dark:text-sidan-300"></p>
                </div>

                <div class="grid grid-cols-[1fr_auto_1fr_1fr] items-center gap-2">
                    <select id="sidanCalendarHour"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-center text-sm font-bold text-slate-800 outline-none focus:border-sidan-500 focus:ring-2 focus:ring-sidan-500/10 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                    </select>

                    <span class="text-lg font-bold text-slate-400">:</span>

                    <select id="sidanCalendarMinute"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-center text-sm font-bold text-slate-800 outline-none focus:border-sidan-500 focus:ring-2 focus:ring-sidan-500/10 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                    </select>

                    <select id="sidanCalendarAmPm"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-center text-sm font-bold text-slate-800 outline-none focus:border-sidan-500 focus:ring-2 focus:ring-sidan-500/10 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        <option value="AM">AM</option>
                        <option value="PM">PM</option>
                    </select>
                </div>

                <p id="sidanCalendarError"
                    class="mt-3 hidden rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-600 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                </p>
            </div>
        </div>

        <div
            class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 bg-slate-50/80 px-4 py-3 dark:border-white/10 dark:bg-black/10">
            <button type="button" id="sidanCalendarClear"
                class="rounded-xl px-3 py-2 text-xs font-semibold text-slate-500 transition hover:bg-slate-200/60 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-slate-200">
                Limpiar
            </button>

            <div class="flex items-center gap-2">
                <button type="button" id="sidanCalendarCancel"
                    class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-white dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">
                    Cancelar
                </button>

                <button type="button" id="sidanCalendarApply"
                    class="rounded-xl bg-sidan-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-sidan-600 focus:outline-none focus:ring-2 focus:ring-sidan-500/30">
                    Aplicar
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarPicker = document.getElementById('sidanUniversalCalendarPicker');
            const calendarDays = document.getElementById('sidanCalendarDays');
            const calendarMonth = document.getElementById('sidanCalendarMonth');
            const calendarYear = document.getElementById('sidanCalendarYear');
            const calendarHour = document.getElementById('sidanCalendarHour');
            const calendarMinute = document.getElementById('sidanCalendarMinute');
            const calendarAmPm = document.getElementById('sidanCalendarAmPm');
            const calendarSelectedDateLabel = document.getElementById('sidanCalendarSelectedDateLabel');
            const calendarError = document.getElementById('sidanCalendarError');
            const calendarPrevMonth = document.getElementById('sidanCalendarPrevMonth');
            const calendarNextMonth = document.getElementById('sidanCalendarNextMonth');
            const calendarClear = document.getElementById('sidanCalendarClear');
            const calendarCancel = document.getElementById('sidanCalendarCancel');
            const calendarApply = document.getElementById('sidanCalendarApply');

            if (!calendarPicker) return;

            const nombresMeses = [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ];

            let calendarioInputActivo = null;
            let calendarioTriggerActivo = null;
            let calendarioFechaSeleccionada = null;
            let calendarioMesVisible = new Date().getMonth();
            let calendarioAnioVisible = new Date().getFullYear();

            const triggerPorInput = new WeakMap();

            function padFecha(numero) {
                return String(numero).padStart(2, '0');
            }

            function parseFechaLocal(valor) {
                const coincidencia = String(valor || '').match(
                    /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(?::\d{2})?$/
                );

                if (!coincidencia) return null;

                return {
                    year: Number(coincidencia[1]),
                    month: Number(coincidencia[2]) - 1,
                    day: Number(coincidencia[3]),
                    hour: Number(coincidencia[4]),
                    minute: Number(coincidencia[5]),
                };
            }

            function valorFechaLocal(fecha) {
                return `${fecha.year}-${padFecha(fecha.month + 1)}-${padFecha(fecha.day)}T${padFecha(fecha.hour)}:${padFecha(fecha.minute)}`;
            }

            function formatearFechaVisible(valor) {
                const fecha = parseFechaLocal(valor);
                if (!fecha) return '';

                const horas12 = fecha.hour % 12 || 12;
                const ampm = fecha.hour >= 12 ? 'PM' : 'AM';

                return `${fecha.day} ${nombresMeses[fecha.month].toLowerCase()} ${fecha.year} · ${padFecha(horas12)}:${padFecha(fecha.minute)} ${ampm}`;
            }

            function fechaBaseParaInput(input) {
                const actual = parseFechaLocal(input?.value);
                if (actual) return actual;

                const minimo = parseFechaLocal(input?.min);
                const ahora = new Date();

                let base = minimo || {
                    year: ahora.getFullYear(),
                    month: ahora.getMonth(),
                    day: ahora.getDate(),
                    hour: ahora.getHours(),
                    minute: ahora.getMinutes(),
                };

                base = { ...base };
                base.minute = Math.ceil(base.minute / 5) * 5;

                if (base.minute >= 60) {
                    base.minute = 0;
                    base.hour += 1;

                    if (base.hour >= 24) {
                        const siguiente = new Date(base.year, base.month, base.day + 1, 0, 0);
                        base.year = siguiente.getFullYear();
                        base.month = siguiente.getMonth();
                        base.day = siguiente.getDate();
                        base.hour = 0;
                    }
                }

                return base;
            }

            function clasesTrigger(input) {
                const usaCyan = String(input.className || '').includes('cyan');

                if (usaCyan) {
                    return 'group relative z-10 flex w-full items-center justify-between gap-3 rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-left outline-none transition hover:border-cyan-500/40 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/10';
                }

                return 'group relative z-10 flex w-full items-center justify-between gap-3 rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-left outline-none transition hover:border-sidan-500/40 focus:border-sidan-500 focus:ring-2 focus:ring-sidan-500/10';
            }

            function crearTrigger(input) {
                const trigger = document.createElement('button');
                trigger.type = 'button';
                trigger.className = clasesTrigger(input);
                trigger.setAttribute('aria-haspopup', 'dialog');
                trigger.setAttribute('aria-expanded', 'false');

                const texto = document.createElement('span');
                texto.className = 'sidan-calendar-trigger-text min-w-0 truncate text-sm text-gray-500';

                const icono = document.createElement('span');
                icono.className =
                    'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-gray-400 transition group-hover:border-sidan-500/30 group-hover:text-sidan-400';
                icono.innerHTML = `
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M8 7V3m8 4V3M5 11h14M6 5h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2z" />
                    </svg>
                `;

                trigger.appendChild(texto);
                trigger.appendChild(icono);

                trigger.addEventListener('click', function() {
                    abrirCalendario(input, trigger);
                });

                return trigger;
            }

            function actualizarTrigger(input) {
                const trigger = triggerPorInput.get(input);
                if (!trigger) return;

                const texto = trigger.querySelector('.sidan-calendar-trigger-text');
                const valorVisible = formatearFechaVisible(input.value);

                if (texto) {
                    texto.textContent = valorVisible || 'Selecciona fecha y hora';
                    texto.classList.toggle('text-gray-500', !valorVisible);
                    texto.classList.toggle('text-slate-900', Boolean(valorVisible));
                    texto.classList.toggle('dark:text-white', Boolean(valorVisible));
                }

                trigger.classList.toggle('border-sidan-500/40', Boolean(valorVisible));
            }

            function prepararInput(input) {
                if (!(input instanceof HTMLInputElement)) return;
                if (input.type !== 'datetime-local') return;
                if (input.dataset.sidanCalendarReady === '1') return;
                if (!input.closest('.activity-ui')) return;

                input.dataset.sidanCalendarReady = '1';

                const wrapper = document.createElement('div');
                wrapper.className = 'relative w-full min-w-0';

                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);

                input.style.position = 'absolute';
                input.style.inset = '0';
                input.style.width = '100%';
                input.style.height = '100%';
                input.style.opacity = '0';
                input.style.pointerEvents = 'none';
                input.style.zIndex = '0';

                const trigger = crearTrigger(input);
                wrapper.appendChild(trigger);
                triggerPorInput.set(input, trigger);

                input.addEventListener('focus', function() {
                    abrirCalendario(input, trigger);
                    requestAnimationFrame(() => trigger.focus({ preventScroll: true }));
                });

                input.addEventListener('input', function() {
                    actualizarTrigger(input);
                });

                input.addEventListener('change', function() {
                    actualizarTrigger(input);
                });

                actualizarTrigger(input);
            }

            function prepararCalendarios(contexto = document) {
                if (contexto instanceof HTMLInputElement && contexto.type === 'datetime-local') {
                    prepararInput(contexto);
                }

                if (!contexto.querySelectorAll) return;

                contexto.querySelectorAll('.activity-ui input[type="datetime-local"]').forEach(prepararInput);
            }

            function refrescarCalendarios() {
                document.querySelectorAll('.activity-ui input[type="datetime-local"][data-sidan-calendar-ready="1"]')
                    .forEach(actualizarTrigger);
            }

            function poblarSelectorMes() {
                calendarMonth.innerHTML = nombresMeses
                    .map((mes, indice) => `<option value="${indice}">${mes}</option>`)
                    .join('');
                calendarMonth.value = String(calendarioMesVisible);
            }

            function poblarSelectorAnio() {
                const minimo = parseFechaLocal(calendarioInputActivo?.min);
                const maximo = parseFechaLocal(calendarioInputActivo?.max);
                const actual = new Date().getFullYear();
                const desde = minimo?.year ?? Math.min(actual - 1, calendarioAnioVisible - 2);
                const hasta = maximo?.year ?? Math.max(actual + 10, calendarioAnioVisible + 8);

                calendarYear.innerHTML = '';

                for (let anio = desde; anio <= hasta; anio += 1) {
                    const option = document.createElement('option');
                    option.value = String(anio);
                    option.textContent = String(anio);
                    calendarYear.appendChild(option);
                }

                calendarYear.value = String(calendarioAnioVisible);
            }

            function poblarSelectorHora() {
                calendarHour.innerHTML = '';

                for (let hora = 1; hora <= 12; hora += 1) {
                    const option = document.createElement('option');
                    option.value = String(hora);
                    option.textContent = padFecha(hora);
                    calendarHour.appendChild(option);
                }
            }

            function poblarSelectorMinuto(minutoActual = 0) {
                const minutos = new Set();

                for (let minuto = 0; minuto < 60; minuto += 5) {
                    minutos.add(minuto);
                }

                minutos.add(Number(minutoActual));

                calendarMinute.innerHTML = [...minutos]
                    .sort((a, b) => a - b)
                    .map(minuto => `<option value="${minuto}">${padFecha(minuto)}</option>`)
                    .join('');
            }

            function sincronizarHoraPicker() {
                if (!calendarioFechaSeleccionada) return;

                const hora24 = calendarioFechaSeleccionada.hour;
                const hora12 = hora24 % 12 || 12;

                poblarSelectorMinuto(calendarioFechaSeleccionada.minute);
                calendarHour.value = String(hora12);
                calendarMinute.value = String(calendarioFechaSeleccionada.minute);
                calendarAmPm.value = hora24 >= 12 ? 'PM' : 'AM';
            }

            function obtenerHoraPicker24() {
                let hora = Number(calendarHour.value || 12) % 12;

                if (calendarAmPm.value === 'PM') {
                    hora += 12;
                }

                return hora;
            }

            function fechaDiaPermitida(year, month, day) {
                if (!calendarioInputActivo) return true;

                const fechaInicioDia = `${year}-${padFecha(month + 1)}-${padFecha(day)}T00:00`;
                const fechaFinDia = `${year}-${padFecha(month + 1)}-${padFecha(day)}T23:59`;
                const minimo = calendarioInputActivo.min || '';
                const maximo = calendarioInputActivo.max || '';

                if (minimo && fechaFinDia < minimo) return false;
                if (maximo && fechaInicioDia > maximo) return false;

                return true;
            }

            function actualizarEtiquetaDiaSeleccionado() {
                if (!calendarioFechaSeleccionada) {
                    calendarSelectedDateLabel.textContent = '';
                    return;
                }

                calendarSelectedDateLabel.textContent =
                    `${calendarioFechaSeleccionada.day} ${nombresMeses[calendarioFechaSeleccionada.month].toLowerCase()}`;
            }

            function renderizarDiasCalendario() {
                calendarDays.innerHTML = '';

                const primerDia = new Date(calendarioAnioVisible, calendarioMesVisible, 1);
                const ultimoDia = new Date(calendarioAnioVisible, calendarioMesVisible + 1, 0).getDate();
                const desplazamiento = (primerDia.getDay() + 6) % 7;
                const hoy = new Date();

                for (let i = 0; i < desplazamiento; i += 1) {
                    const vacio = document.createElement('span');
                    vacio.className = 'h-10 w-10';
                    calendarDays.appendChild(vacio);
                }

                for (let dia = 1; dia <= ultimoDia; dia += 1) {
                    const permitido = fechaDiaPermitida(calendarioAnioVisible, calendarioMesVisible, dia);
                    const seleccionado = calendarioFechaSeleccionada &&
                        calendarioFechaSeleccionada.year === calendarioAnioVisible &&
                        calendarioFechaSeleccionada.month === calendarioMesVisible &&
                        calendarioFechaSeleccionada.day === dia;
                    const esHoy = hoy.getFullYear() === calendarioAnioVisible &&
                        hoy.getMonth() === calendarioMesVisible &&
                        hoy.getDate() === dia;

                    const boton = document.createElement('button');
                    boton.type = 'button';
                    boton.textContent = String(dia);
                    boton.disabled = !permitido;
                    boton.className =
                        'flex h-10 w-10 items-center justify-center rounded-xl text-sm font-semibold outline-none transition focus:ring-2 focus:ring-sidan-500/20';

                    if (!permitido) {
                        boton.classList.add('cursor-not-allowed', 'text-slate-300', 'dark:text-slate-600');
                    } else if (seleccionado) {
                        boton.classList.add('bg-sidan-500', 'text-white', 'shadow-sm');
                    } else {
                        boton.classList.add(
                            'text-slate-700',
                            'hover:bg-slate-100',
                            'dark:text-slate-200',
                            'dark:hover:bg-white/5'
                        );

                        if (esHoy) {
                            boton.classList.add(
                                'border',
                                'border-sidan-400/50',
                                'text-sidan-600',
                                'dark:text-sidan-300'
                            );
                        }
                    }

                    boton.addEventListener('click', function() {
                        if (!permitido) return;

                        calendarioFechaSeleccionada = {
                            ...(calendarioFechaSeleccionada || fechaBaseParaInput(calendarioInputActivo)),
                            year: calendarioAnioVisible,
                            month: calendarioMesVisible,
                            day: dia,
                        };

                        calendarError.classList.add('hidden');
                        renderizarDiasCalendario();
                        actualizarEtiquetaDiaSeleccionado();
                    });

                    calendarDays.appendChild(boton);
                }
            }

            function posicionarCalendario(trigger) {
                if (!calendarPicker || !trigger) return;

                const rect = trigger.getBoundingClientRect();
                const margen = 12;
                const ancho = Math.min(368, window.innerWidth - (margen * 2));

                calendarPicker.style.width = `${ancho}px`;
                calendarPicker.style.visibility = 'hidden';
                calendarPicker.classList.remove('hidden');

                const alto = calendarPicker.offsetHeight;
                let izquierda = rect.left;
                let arriba = rect.bottom + 8;

                izquierda = Math.max(
                    margen,
                    Math.min(izquierda, window.innerWidth - ancho - margen)
                );

                if (arriba + alto > window.innerHeight - margen) {
                    arriba = Math.max(margen, rect.top - alto - 8);
                }

                calendarPicker.style.left = `${izquierda}px`;
                calendarPicker.style.top = `${arriba}px`;
                calendarPicker.style.visibility = 'visible';
            }

            function cerrarCalendario() {
                calendarPicker.classList.add('hidden');
                calendarPicker.style.removeProperty('visibility');

                if (calendarioTriggerActivo) {
                    calendarioTriggerActivo.setAttribute('aria-expanded', 'false');
                }

                calendarioInputActivo = null;
                calendarioTriggerActivo = null;
                calendarError.classList.add('hidden');
            }

            function abrirCalendario(input, trigger = triggerPorInput.get(input)) {
                if (!input || !trigger) return;

                calendarioInputActivo = input;
                calendarioTriggerActivo = trigger;
                calendarioFechaSeleccionada = fechaBaseParaInput(input);
                calendarioMesVisible = calendarioFechaSeleccionada.month;
                calendarioAnioVisible = calendarioFechaSeleccionada.year;

                poblarSelectorMes();
                poblarSelectorAnio();
                poblarSelectorHora();
                sincronizarHoraPicker();
                renderizarDiasCalendario();
                actualizarEtiquetaDiaSeleccionado();
                calendarError.classList.add('hidden');

                trigger.setAttribute('aria-expanded', 'true');
                posicionarCalendario(trigger);
            }

            function aplicarFechaCalendario() {
                if (!calendarioInputActivo || !calendarioFechaSeleccionada) return;

                calendarioFechaSeleccionada.hour = obtenerHoraPicker24();
                calendarioFechaSeleccionada.minute = Number(calendarMinute.value || 0);

                const valor = valorFechaLocal(calendarioFechaSeleccionada);
                const minimo = calendarioInputActivo.min || '';
                const maximo = calendarioInputActivo.max || '';

                if (minimo && valor < minimo) {
                    calendarError.textContent =
                        `Selecciona una fecha y hora igual o posterior a ${formatearFechaVisible(minimo)}.`;
                    calendarError.classList.remove('hidden');
                    return;
                }

                if (maximo && valor > maximo) {
                    calendarError.textContent =
                        `Selecciona una fecha y hora igual o anterior a ${formatearFechaVisible(maximo)}.`;
                    calendarError.classList.remove('hidden');
                    return;
                }

                const input = calendarioInputActivo;
                input.value = valor;

                input.dispatchEvent(new Event('input', {
                    bubbles: true
                }));

                input.dispatchEvent(new Event('change', {
                    bubbles: true
                }));

                actualizarTrigger(input);
                cerrarCalendario();
            }

            function limpiarFechaCalendario() {
                if (!calendarioInputActivo) return;

                const input = calendarioInputActivo;
                input.value = '';

                input.dispatchEvent(new Event('input', {
                    bubbles: true
                }));

                input.dispatchEvent(new Event('change', {
                    bubbles: true
                }));

                actualizarTrigger(input);
                cerrarCalendario();
            }

            calendarPrevMonth.addEventListener('click', function() {
                calendarioMesVisible -= 1;

                if (calendarioMesVisible < 0) {
                    calendarioMesVisible = 11;
                    calendarioAnioVisible -= 1;
                }

                poblarSelectorMes();
                poblarSelectorAnio();
                renderizarDiasCalendario();
            });

            calendarNextMonth.addEventListener('click', function() {
                calendarioMesVisible += 1;

                if (calendarioMesVisible > 11) {
                    calendarioMesVisible = 0;
                    calendarioAnioVisible += 1;
                }

                poblarSelectorMes();
                poblarSelectorAnio();
                renderizarDiasCalendario();
            });

            calendarMonth.addEventListener('change', function() {
                calendarioMesVisible = Number(this.value);
                renderizarDiasCalendario();
            });

            calendarYear.addEventListener('change', function() {
                calendarioAnioVisible = Number(this.value);
                renderizarDiasCalendario();
            });

            calendarHour.addEventListener('change', () => calendarError.classList.add('hidden'));
            calendarMinute.addEventListener('change', () => calendarError.classList.add('hidden'));
            calendarAmPm.addEventListener('change', () => calendarError.classList.add('hidden'));
            calendarApply.addEventListener('click', aplicarFechaCalendario);
            calendarClear.addEventListener('click', limpiarFechaCalendario);
            calendarCancel.addEventListener('click', cerrarCalendario);

            document.addEventListener('click', function(event) {
                if (!calendarPicker.classList.contains('hidden')) {
                    const rutaEvento = typeof event.composedPath === 'function' ? event.composedPath() : [];
                    const dentroPicker = rutaEvento.length > 0 ?
                        rutaEvento.includes(calendarPicker) :
                        calendarPicker.contains(event.target);

                    const dentroTrigger = calendarioTriggerActivo ?
                        calendarioTriggerActivo.contains(event.target) :
                        false;

                    if (!dentroPicker && !dentroTrigger) {
                        cerrarCalendario();
                    }
                }

                requestAnimationFrame(function() {
                    prepararCalendarios();
                    refrescarCalendarios();
                });
            });

            document.addEventListener('change', function() {
                requestAnimationFrame(function() {
                    prepararCalendarios();
                    refrescarCalendarios();
                });
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && !calendarPicker.classList.contains('hidden')) {
                    cerrarCalendario();
                }
            });

            window.addEventListener('resize', cerrarCalendario);
            window.addEventListener('scroll', cerrarCalendario, true);

            window.SIDANCalendar = {
                attach: prepararInput,
                attachAll: prepararCalendarios,
                refresh: actualizarTrigger,
                refreshAll: refrescarCalendarios,
                open: function(input) {
                    prepararInput(input);
                    abrirCalendario(input);
                }
            };

            prepararCalendarios();

            queueMicrotask(function() {
                prepararCalendarios();
                refrescarCalendarios();
            });
        });
    </script>
@endonce
