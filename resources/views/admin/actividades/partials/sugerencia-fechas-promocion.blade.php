<p
    id="sugerenciaFechasPromocion"
    class="sm:col-span-2 text-xs leading-5 text-slate-500 dark:text-slate-400"
    data-desde="{{ $actividad->visible_desde?->copy()->timezone('America/El_Salvador')->format('Y-m-d\TH:i') }}"
    data-hasta="{{ $actividad->realizacion_hasta?->copy()->timezone('America/El_Salvador')->format('Y-m-d\TH:i') }}"
>
    SIDAN propone las fechas de visualización y finalización de la actividad.
    Podés cambiarlas.
</p>