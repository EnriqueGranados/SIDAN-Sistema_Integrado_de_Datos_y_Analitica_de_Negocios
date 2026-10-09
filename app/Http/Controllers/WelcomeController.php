<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Categoria;
use App\Models\Etiqueta;
use App\Models\WelcomeEspacio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function index(Request $request): View
    {
        $modoPreview = $this->modoPreviewAutorizado($request);
        $relaciones = $this->relacionesPortal();

        $actividadesHero = $this->actividadesSeccion(
            'hero',
            $relaciones,
            $modoPreview
        );

        $actividades = $this->actividadesSeccion(
            'explorar',
            $relaciones,
            $modoPreview
        );

        $consultaDestacada = $modoPreview
            ? $this->actividadesPublicadasParaPreview()
            : $this->actividadesPublicas();

        $destacadaModelo = $consultaDestacada
            ->with($relaciones)
            ->orderByDesc('destacada')
            ->orderByDesc('prioridad')
            ->orderByRaw('realizacion_desde IS NULL')
            ->orderBy('realizacion_desde')
            ->orderByDesc('id_actividad')
            ->first();

        $destacada = $destacadaModelo
            ? $this->mapearActividad($destacadaModelo, $modoPreview)
            : null;

        $categoriasPopulares = Categoria::query()
            ->where('activo', true)
            ->whereHas(
                'actividades',
                fn (Builder $query) => $query->visiblesEnPortal()
            )
            ->withCount([
                'actividades as actividades_publicas_count' =>
                    fn (Builder $query) => $query->visiblesEnPortal(),
            ])
            ->orderByDesc('actividades_publicas_count')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->limit(6)
            ->pluck('nombre')
            ->values();

        $etiquetasPopulares = Etiqueta::query()
            ->where('activo', true)
            ->whereHas(
                'actividades',
                fn (Builder $query) => $query->visiblesEnPortal()
            )
            ->withCount([
                'actividades as actividades_publicas_count' =>
                    fn (Builder $query) => $query->visiblesEnPortal(),
            ])
            ->orderByDesc('actividades_publicas_count')
            ->orderBy('nombre')
            ->limit(8)
            ->pluck('nombre')
            ->values();

        return view('welcome', compact(
            'actividadesHero',
            'actividades',
            'destacada',
            'categoriasPopulares',
            'etiquetasPopulares',
            'modoPreview'
        ));
    }

    public function catalogo(Request $request): View
    {
        $query = $this->actividadesPublicas()
            ->with($this->relacionesPortal());

        $this->aplicarFiltros($query, $request);

        $paginador = $query
            ->orderByDesc('prioridad')
            ->orderByRaw('realizacion_desde IS NULL')
            ->orderBy('realizacion_desde')
            ->orderByDesc('id_actividad')
            ->paginate(12)
            ->withQueryString();

        $paginador->through(
            fn (Actividad $actividad) => $this->mapearActividad($actividad)
        );

        return view('activities.index', [
            'actividades' => $paginador,
        ]);
    }

    public function sugerencias(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'tipo' => 'required|in:actividad,ubicacion,categoria,etiqueta',
            'q' => 'nullable|string|max:100',
        ]);

        $tipo = $datos['tipo'];
        $buscar = trim((string) ($datos['q'] ?? ''));

        if ($tipo === 'actividad' && $buscar === '') {
            return response()->json(['items' => []]);
        }

        $items = match ($tipo) {
            'actividad' => $this->sugerirActividades($buscar),
            'ubicacion' => $this->sugerirUbicaciones($buscar),
            'categoria' => $this->sugerirCategorias($buscar),
            'etiqueta' => $this->sugerirEtiquetas($buscar),
        };

        return response()->json(['items' => $items]);
    }

    public function show(Request $request, string $slug): View
    {
        $modoPreview = $this->modoPreviewAutorizado($request);
        $consulta = $modoPreview
            ? $this->actividadesPublicadasParaPreview()
            : $this->actividadesPublicas();

        $actividadModelo = $consulta
            ->where('slug', $slug)
            ->with([
                'categoria',
                'espacio',
                'portada',
                'medios',
                'etiquetas',
                'creador.informacion_personal',
                'sesiones' => fn ($query) => $query
                    ->with(['espacio', 'recursos'])
                    ->orderBy('fecha_inicio')
                    ->orderBy('orden'),
                'items' => fn ($query) => $query
                    ->where('activo', true)
                    ->with([
                        'variantes' => fn ($query) => $query
                            ->where('activo', true)
                            ->orderBy('orden'),
                        'medios',
                    ])
                    ->orderBy('orden'),
                'promociones' => fn ($query) => $query
                    ->publicables()
                    ->with([
                        'items' => fn ($items) => $items
                            ->where('activo', true)
                            ->orderBy('orden'),
                    ])
                    ->orderBy('vigente_desde')
                    ->orderBy('id_promocion'),
            ])
            ->firstOrFail();

        $actividad = $this->mapearActividad($actividadModelo, $modoPreview);
        $accionSolicitada = $request->query('accion');

        return view('activities.show', compact(
            'actividad',
            'actividadModelo',
            'accionSolicitada',
            'modoPreview'
        ));
    }

    private function actividadesPublicas(): Builder
    {
        return Actividad::query()->visiblesEnPortal();
    }

    private function actividadesPublicadasParaPreview(): Builder
    {
        return Actividad::query()
            ->whereNull('eliminado_en')
            ->where('visibilidad', 'publica')
            ->where('estado_publicacion', Actividad::ESTADO_PUBLICADA);
    }

    private function modoPreviewAutorizado(Request $request): bool
    {
        if (!$request->boolean('preview')) {
            return false;
        }

        $usuario = $request->user();
        $rol = $usuario?->rol?->nombre;

        return in_array($rol, ['superadmin', 'admin'], true);
    }

    private function actividadesSeccion(
        string $seccion,
        array $relaciones,
        bool $modoPreview = false
    ): array {
        $espacios = WelcomeEspacio::query()
            ->where('seccion', $seccion)
            ->whereNotNull('id_actividad')
            ->with([
                'actividad' => fn ($query) => $query->with($relaciones),
            ])
            ->orderBy('orden')
            ->get();

        return $espacios
            ->map(function (WelcomeEspacio $espacio) use ($modoPreview) {
                $actividad = $espacio->actividad;

                if (!$actividad) {
                    return null;
                }

                if ($modoPreview) {
                    if (
                        !$actividad->estaPublicada()
                        || $actividad->visibilidad !== 'publica'
                    ) {
                        return null;
                    }
                } elseif (!$actividad->estaVisibleEnPortal()) {
                    return null;
                }

                return $this->mapearActividad(
                    $actividad,
                    $modoPreview
                );
            })
            ->filter()
            ->values()
            ->all();
    }

    private function relacionesPortal(): array
    {
        return [
            'categoria',
            'espacio',
            'portada',
            'etiquetas' => fn ($query) => $query
                ->where('activo', true)
                ->orderBy('nombre'),
            'sesiones' => fn ($query) => $query
                ->with('espacio')
                ->orderBy('fecha_inicio')
                ->orderBy('orden'),
            'items' => fn ($query) => $query
                ->where('activo', true)
                ->with([
                    'variantes' => fn ($query) => $query
                        ->where('activo', true)
                        ->orderBy('orden'),
                ])
                ->orderBy('orden'),
        ];
    }

    private function aplicarFiltros(Builder $query, Request $request): void
    {
        $buscar = trim((string) ($request->query('q') ?? $request->query('buscar')));
        $ubicacion = trim((string) $request->query('ubicacion'));
        $categoria = trim((string) $request->query('categoria'));
        $etiqueta = trim((string) $request->query('etiqueta'));
        $fecha = trim((string) $request->query('fecha'));

        if ($buscar !== '') {
            $this->aplicarBusquedaTexto($query, $buscar);
        }

        if ($ubicacion !== '') {
            $this->aplicarFiltroUbicacion($query, $ubicacion);
        }

        if ($categoria !== '') {
            $query->whereHas(
                'categoria',
                fn (Builder $q) => $q
                    ->where('activo', true)
                    ->where('nombre', 'ILIKE', $this->patronLike($categoria))
            );
        }

        if ($etiqueta !== '') {
            $query->whereHas(
                'etiquetas',
                fn (Builder $q) => $q
                    ->where('activo', true)
                    ->where('nombre', 'ILIKE', $this->patronLike($etiqueta))
            );
        }

        if ($fecha !== '') {
            $this->aplicarFiltroFecha($query, $fecha);
        }
    }

    private function aplicarBusquedaTexto(Builder $query, string $buscar): void
    {
        $patron = $this->patronLike($buscar);

        $query->where(function (Builder $subquery) use ($patron) {
            $subquery
                ->where('nombre', 'ILIKE', $patron)
                ->orWhere('resumen', 'ILIKE', $patron)
                ->orWhere('descripcion', 'ILIKE', $patron)
                ->orWhere('ubicacion_externa', 'ILIKE', $patron)
                ->orWhereHas(
                    'categoria',
                    fn (Builder $q) => $q->where('nombre', 'ILIKE', $patron)
                )
                ->orWhereHas(
                    'etiquetas',
                    fn (Builder $q) => $q
                        ->where('activo', true)
                        ->where('nombre', 'ILIKE', $patron)
                )
                ->orWhereHas(
                    'espacio',
                    fn (Builder $q) => $q
                        ->where('nombre', 'ILIKE', $patron)
                        ->orWhere('direccion', 'ILIKE', $patron)
                )
                ->orWhereHas(
                    'sesiones',
                    function (Builder $q) use ($patron) {
                        $q->where('nombre', 'ILIKE', $patron)
                            ->orWhere('ubicacion', 'ILIKE', $patron)
                            ->orWhereHas(
                                'espacio',
                                fn (Builder $espacio) => $espacio
                                    ->where('nombre', 'ILIKE', $patron)
                                    ->orWhere('direccion', 'ILIKE', $patron)
                            );
                    }
                );
        });
    }

    private function aplicarFiltroUbicacion(Builder $query, string $ubicacion): void
    {
        $patron = $this->patronLike($ubicacion);

        $query->where(function (Builder $subquery) use ($patron) {
            $subquery
                ->where('ubicacion_externa', 'ILIKE', $patron)
                ->orWhereHas(
                    'espacio',
                    fn (Builder $q) => $q
                        ->where('nombre', 'ILIKE', $patron)
                        ->orWhere('direccion', 'ILIKE', $patron)
                )
                ->orWhereHas(
                    'sesiones',
                    function (Builder $q) use ($patron) {
                        $q->where('ubicacion', 'ILIKE', $patron)
                            ->orWhereHas(
                                'espacio',
                                fn (Builder $espacio) => $espacio
                                    ->where('nombre', 'ILIKE', $patron)
                                    ->orWhere('direccion', 'ILIKE', $patron)
                            );
                    }
                );
        });
    }

    private function aplicarFiltroFecha(Builder $query, string $fecha): void
    {
        try {
            $inicio = Carbon::createFromFormat(
                'Y-m-d',
                $fecha,
                config('app.timezone')
            )->startOfDay();

            $fin = $inicio->copy()->endOfDay();
        } catch (\Throwable) {
            return;
        }

        $query->where(function (Builder $subquery) use ($inicio, $fin) {
            $subquery
                ->where(function (Builder $actividad) use ($inicio, $fin) {
                    $actividad
                        ->whereNotNull('realizacion_desde')
                        ->where('realizacion_desde', '<=', $fin)
                        ->whereRaw(
                            'COALESCE(realizacion_hasta, realizacion_desde) >= ?',
                            [$inicio]
                        );
                })
                ->orWhereHas(
                    'sesiones',
                    function (Builder $sesion) use ($inicio, $fin) {
                        $sesion
                            ->where('fecha_inicio', '<=', $fin)
                            ->whereRaw(
                                'COALESCE(fecha_fin, fecha_inicio) >= ?',
                                [$inicio]
                            );
                    }
                );
        });
    }

    private function sugerirActividades(string $buscar): array
    {
        $query = $this->actividadesPublicas()
            ->with([
                'categoria',
                'espacio',
                'portada',
                'sesiones' => fn ($q) => $q
                    ->with('espacio')
                    ->orderBy('fecha_inicio')
                    ->orderBy('orden'),
            ]);

        $this->aplicarBusquedaTexto($query, $buscar);

        return $query
            ->orderByDesc('prioridad')
            ->orderByRaw('realizacion_desde IS NULL')
            ->orderBy('realizacion_desde')
            ->limit(8)
            ->get()
            ->map(fn (Actividad $actividad) => $this->mapearSugerenciaActividad($actividad))
            ->values()
            ->all();
    }

    private function sugerirUbicaciones(string $buscar): array
    {
        $query = $this->actividadesPublicas()
            ->with([
                'espacio',
                'sesiones' => fn ($q) => $q->with('espacio'),
            ]);

        if ($buscar !== '') {
            $this->aplicarFiltroUbicacion($query, $buscar);
        }

        $actividades = $query
            ->orderByDesc('prioridad')
            ->limit(40)
            ->get();

        $items = collect();

        foreach ($actividades as $actividad) {
            $this->agregarSugerenciaTexto(
                $items,
                $actividad->ubicacion_externa,
                'Ubicación externa'
            );

            if ($actividad->espacio) {
                $this->agregarSugerenciaTexto(
                    $items,
                    $actividad->espacio->nombre,
                    'Espacio'
                );

                $this->agregarSugerenciaTexto(
                    $items,
                    $actividad->espacio->direccion,
                    'Dirección'
                );
            }

            foreach ($actividad->sesiones as $sesion) {
                $this->agregarSugerenciaTexto(
                    $items,
                    $sesion->ubicacion,
                    'Sesión'
                );

                if ($sesion->espacio) {
                    $this->agregarSugerenciaTexto(
                        $items,
                        $sesion->espacio->nombre,
                        'Espacio de sesión'
                    );

                    $this->agregarSugerenciaTexto(
                        $items,
                        $sesion->espacio->direccion,
                        'Dirección'
                    );
                }
            }
        }

        $buscarNormalizado = $this->normalizarTexto($buscar);

        return $items
            ->unique('normalizado')
            ->filter(function (array $item) use ($buscarNormalizado) {
                return $buscarNormalizado === ''
                    || str_contains($item['normalizado'], $buscarNormalizado);
            })
            ->take(8)
            ->map(fn (array $item) => [
                'valor' => $item['valor'],
                'detalle' => $item['detalle'],
            ])
            ->values()
            ->all();
    }

    private function sugerirCategorias(string $buscar): array
    {
        $query = Categoria::query()
            ->where('activo', true)
            ->whereHas(
                'actividades',
                fn (Builder $q) => $q->visiblesEnPortal()
            )
            ->withCount([
                'actividades as actividades_publicas_count' =>
                    fn (Builder $q) => $q->visiblesEnPortal(),
            ]);

        if ($buscar !== '') {
            $query->where('nombre', 'ILIKE', $this->patronLike($buscar));
        }

        return $query
            ->orderByDesc('actividades_publicas_count')
            ->orderBy('nombre')
            ->limit(8)
            ->get()
            ->map(fn (Categoria $categoria) => [
                'valor' => $categoria->nombre,
                'detalle' => $categoria->actividades_publicas_count . ' actividades',
            ])
            ->values()
            ->all();
    }

    private function sugerirEtiquetas(string $buscar): array
    {
        $query = Etiqueta::query()
            ->where('activo', true)
            ->whereHas(
                'actividades',
                fn (Builder $q) => $q->visiblesEnPortal()
            )
            ->withCount([
                'actividades as actividades_publicas_count' =>
                    fn (Builder $q) => $q->visiblesEnPortal(),
            ]);

        if ($buscar !== '') {
            $query->where('nombre', 'ILIKE', $this->patronLike($buscar));
        }

        return $query
            ->orderByDesc('actividades_publicas_count')
            ->orderBy('nombre')
            ->limit(8)
            ->get()
            ->map(fn (Etiqueta $etiqueta) => [
                'valor' => $etiqueta->nombre,
                'detalle' => $etiqueta->actividades_publicas_count . ' actividades',
            ])
            ->values()
            ->all();
    }

    private function mapearSugerenciaActividad(Actividad $actividad): array
    {
        $fechaReferencia = $actividad->realizacion_desde
            ?? $actividad->sesiones->first()?->fecha_inicio;

        $ubicacion = $this->resolverUbicacion($actividad);

        return [
            'id' => (int) $actividad->id_actividad,
            'titulo' => $actividad->nombre,
            'categoria' => $actividad->categoria?->nombre ?? 'Sin categoría',
            'fecha' => $fechaReferencia
                ? Str::upper($fechaReferencia->locale('es')->translatedFormat('d M Y'))
                : 'POR DEFINIR',
            'lugar' => $ubicacion['nombre'],
            'imagen' => $this->resolverImagen($actividad->portada?->url),
            'url' => route('activities.show', $actividad->slug),
        ];
    }

    private function agregarSugerenciaTexto(
        Collection $items,
        ?string $valor,
        string $detalle
    ): void {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return;
        }

        $items->push([
            'valor' => $valor,
            'detalle' => $detalle,
            'normalizado' => $this->normalizarTexto($valor),
        ]);
    }

    private function patronLike(string $texto): string
    {
        $texto = str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\%', '\_'],
            trim($texto)
        );

        return '%' . $texto . '%';
    }

    private function normalizarTexto(string $texto): string
    {
        return Str::of($texto)
            ->ascii()
            ->lower()
            ->trim()
            ->toString();
    }

    private function mapearActividad(Actividad $actividad, bool $modoPreview = false): array
    {
        $fechaReferencia = $actividad->realizacion_desde
            ?? $actividad->sesiones->first()?->fecha_inicio;

        $fecha = $fechaReferencia
            ? Str::upper(
                $fechaReferencia
                    ->locale('es')
                    ->translatedFormat('d M')
            )
            : 'POR DEFINIR';

        $hora = $fechaReferencia
            ? $fechaReferencia->format('g:i a')
            : 'Hora por definir';

        $ubicacion = $this->resolverUbicacion($actividad);
        $descripcion = trim(
            (string) ($actividad->resumen ?: $actividad->descripcion)
        );

        if ($descripcion === '') {
            $descripcion = 'Consulta todos los detalles de esta actividad en SIDAN.';
        }

        $etiquetas = $actividad->etiquetas
            ->pluck('nombre')
            ->values()
            ->all();

        $tieneProductos = $actividad->items->contains(
            fn ($item) => strtolower((string) $item->tipo) === 'producto'
        );

        $precio = $this->precioActividad($actividad);
        $estado = $this->estadoActividad(
            $actividad,
            $tieneProductos
        );

        $parametrosDetalle = [
            'slug' => $actividad->slug,
        ];

        if ($modoPreview) {
            $parametrosDetalle['preview'] = 1;
        }

        $detalleUrl = route(
            'activities.show',
            $parametrosDetalle
        );

        [$accionTexto, $accion] = $this->resolverAccion(
            $actividad,
            $tieneProductos
        );

        $accionUrl = match ($accion) {
            'participar' => route('activities.participar', [
                'slug' => $actividad->slug,
            ]),
            'comprar' => route('activities.comprar', [
                'slug' => $actividad->slug,
            ]),
            default => $detalleUrl,
        };

        if ($modoPreview) {
            $accionUrl = $detalleUrl;
        }

        return [
            'id' => $actividad->id_actividad,
            'slug' => $actividad->slug,
            'titulo' => $actividad->nombre,
            'categoria' => $actividad->categoria?->nombre ?? 'Sin categoría',
            'etiquetas' => $etiquetas,

            'fecha' => $fecha,
            'hora' => $hora,

            'lugar' => $ubicacion['nombre'],
            'direccion' => $ubicacion['direccion'],

            'latitud' => $ubicacion['latitud'],
            'longitud' => $ubicacion['longitud'],
            'mapa_disponible' => $ubicacion['mapa_disponible'],
            'mapa_url' => $ubicacion['mapa_url'],
            'mapa_embed_url' => $ubicacion['mapa_embed_url'],

            'organizacion' => 'SIDAN',

            'precio' => $precio,
            'estado' => $estado,
            'descripcion' => $descripcion,
            'imagen' => $this->resolverImagen(
                $actividad->portada?->url
            ),

            'prioridad' => (int) $actividad->prioridad,

            'tipo_participacion' => $this->tipoParticipacion($actividad),
            'participacion_texto' => $this->textoParticipacion($actividad),
            'participacion_descripcion' => $this->descripcionParticipacion($actividad),
            'precio_inscripcion' => $actividad->precio_inscripcion !== null
                ? (float) $actividad->precio_inscripcion
                : null,
            'habilita_inscripcion' => (bool) $actividad->habilita_inscripcion,
            'requiere_cuenta' => (bool) $actividad->requiere_cuenta,
            'permite_lista_espera' => (bool) $actividad->permite_lista_espera,
            'tiene_productos' => $tieneProductos,

            'detalle_url' => $detalleUrl,
            'accion_texto' => $accionTexto,
            'accion_url' => $accionUrl,
        ];
    }

    private function resolverUbicacion(Actividad $actividad): array
    {
        $espacio = $actividad->espacio
            ?? $actividad->sesiones
                ->first(fn ($sesion) => $sesion->espacio !== null)
                ?->espacio;

        if ($espacio) {
            $latitud = $espacio->latitud !== null
                ? (float) $espacio->latitud
                : null;

            $longitud = $espacio->longitud !== null
                ? (float) $espacio->longitud
                : null;

            $mapaDisponible = $latitud !== null
                && $longitud !== null;

            return [
                'nombre' => $espacio->nombre,
                'direccion' => $espacio->direccion,
                'latitud' => $latitud,
                'longitud' => $longitud,
                'mapa_disponible' => $mapaDisponible,
                'mapa_url' => $mapaDisponible
                    ? $this->mapaUrl($latitud, $longitud)
                    : null,
                'mapa_embed_url' => $mapaDisponible
                    ? $this->mapaEmbedUrl($latitud, $longitud)
                    : null,
            ];
        }

        $sesion = $actividad->sesiones->first();

        $nombre = $actividad->ubicacion_externa
            ?? $sesion?->ubicacion
            ?? 'Ubicación por definir';

        return [
            'nombre' => $nombre,
            'direccion' => $nombre,
            'latitud' => null,
            'longitud' => null,
            'mapa_disponible' => false,
            'mapa_url' => null,
            'mapa_embed_url' => null,
        ];
    }

    private function resolverAccion(
        Actividad $actividad,
        bool $tieneProductos
    ): array {
        return match ($this->tipoParticipacion($actividad)) {
            Actividad::PARTICIPACION_REGISTRO_GRATUITO => [
                'Inscribirme gratis',
                'participar',
            ],
            Actividad::PARTICIPACION_REGISTRO_PAGO => [
                'Inscribirme',
                'participar',
            ],
            Actividad::PARTICIPACION_VENTA_DIRECTA => $tieneProductos
                ? ['Ver productos', 'comprar']
                : ['Ver actividad', 'detalle'],
            default => [
                'Ver actividad',
                'detalle',
            ],
        };
    }

    private function tipoParticipacion(Actividad $actividad): string
    {
        $tipo = (string) ($actividad->tipo_participacion ?? '');

        if (in_array($tipo, [
            Actividad::PARTICIPACION_INFORMATIVA,
            Actividad::PARTICIPACION_REGISTRO_GRATUITO,
            Actividad::PARTICIPACION_REGISTRO_PAGO,
            Actividad::PARTICIPACION_VENTA_DIRECTA,
        ], true)) {
            return $tipo;
        }

        return $actividad->habilita_inscripcion
            ? Actividad::PARTICIPACION_REGISTRO_GRATUITO
            : Actividad::PARTICIPACION_INFORMATIVA;
    }

    private function textoParticipacion(Actividad $actividad): string
    {
        return match ($this->tipoParticipacion($actividad)) {
            Actividad::PARTICIPACION_REGISTRO_GRATUITO => 'Inscripción gratuita',
            Actividad::PARTICIPACION_REGISTRO_PAGO => 'Inscripción con pago',
            Actividad::PARTICIPACION_VENTA_DIRECTA => 'Compra directa',
            default => 'Acceso libre / informativa',
        };
    }

    private function descripcionParticipacion(Actividad $actividad): string
    {
        return match ($this->tipoParticipacion($actividad)) {
            Actividad::PARTICIPACION_REGISTRO_GRATUITO =>
                'Puedes registrarte para participar sin pagar inscripción. Los productos y servicios son opcionales.',
            Actividad::PARTICIPACION_REGISTRO_PAGO =>
                'Para participar debes completar una inscripción con pago. Los productos adicionales se manejan aparte.',
            Actividad::PARTICIPACION_VENTA_DIRECTA =>
                'La actividad está orientada a la compra de productos o servicios y no requiere una inscripción general.',
            default =>
                'Puedes consultar el contenido y el cronograma sin completar una inscripción general.',
        };
    }

    private function precioActividad(Actividad $actividad): string
    {
        $tipoParticipacion = $this->tipoParticipacion($actividad);

        if ($tipoParticipacion === Actividad::PARTICIPACION_REGISTRO_GRATUITO) {
            return 'Participación gratis';
        }

        if ($tipoParticipacion === Actividad::PARTICIPACION_REGISTRO_PAGO) {
            $precioInscripcion = (float) ($actividad->precio_inscripcion ?? 0);

            return $precioInscripcion > 0
                ? '$' . number_format($precioInscripcion, 2) . ' inscripción'
                : 'Inscripción con pago';
        }

        if ($tipoParticipacion === Actividad::PARTICIPACION_INFORMATIVA) {
            return 'Acceso libre';
        }

        $precios = new Collection();

        foreach ($actividad->items as $item) {
            $precios->push((float) $item->precio);

            foreach ($item->variantes as $variante) {
                if ($variante->precio !== null) {
                    $precios->push(
                        (float) $variante->precio
                    );
                }
            }
        }

        if ($precios->isEmpty()) {
            return 'Gratis';
        }

        $minimo = (float) $precios->min();
        $maximo = (float) $precios->max();

        if ($maximo <= 0) {
            return 'Gratis';
        }

        if ($minimo <= 0 && $maximo > 0) {
            return 'Desde gratis';
        }

        return $minimo === $maximo
            ? '$' . number_format($minimo, 2)
            : 'Desde $' . number_format($minimo, 2);
    }

    private function estadoActividad(
        Actividad $actividad,
        bool $tieneProductos
    ): string {
        if ($actividad->estado_operativo !== 'normal') {
            return Str::headline(
                $actividad->estado_operativo
            );
        }

        $tipoParticipacion = $this->tipoParticipacion($actividad);

        if ($tipoParticipacion === Actividad::PARTICIPACION_INFORMATIVA) {
            return 'Acceso libre';
        }

        if ($tipoParticipacion === Actividad::PARTICIPACION_VENTA_DIRECTA) {
            return $tieneProductos ? 'Venta disponible' : 'Disponible';
        }

        $ahora = now();

        if (
            $actividad->inscripcion_desde
            && $actividad->inscripcion_desde->gt($ahora)
        ) {
            return 'Inscripción próxima';
        }

        if (
            $actividad->inscripcion_hasta
            && $actividad->inscripcion_hasta->lt($ahora)
        ) {
            return 'Inscripción cerrada';
        }

        if ($actividad->cupo_total !== null) {
            return 'Cupo limitado';
        }

        return 'Inscripción abierta';
    }

    private function mapaUrl(
        float $latitud,
        float $longitud
    ): string {
        return 'https://www.openstreetmap.org/'
            . '?mlat=' . $latitud
            . '&mlon=' . $longitud
            . '#map=17/'
            . $latitud
            . '/'
            . $longitud;
    }

    private function mapaEmbedUrl(
        float $latitud,
        float $longitud
    ): string {
        $margen = 0.006;

        $izquierda = $longitud - $margen;
        $derecha = $longitud + $margen;
        $abajo = $latitud - $margen;
        $arriba = $latitud + $margen;

        return 'https://www.openstreetmap.org/export/embed.html'
            . '?bbox='
            . urlencode(
                "{$izquierda},{$abajo},{$derecha},{$arriba}"
            )
            . '&layer=mapnik'
            . '&marker='
            . urlencode(
                "{$latitud},{$longitud}"
            );
    }

    private function resolverImagen(?string $ruta): string
    {
        if (!$ruta) {
            return $this->imagenPredeterminada();
        }

        $ruta = str_replace(
            '\\',
            '/',
            trim($ruta)
        );

        if (
            Str::startsWith(
                $ruta,
                [
                    'http://',
                    'https://',
                    'data:',
                ]
            )
        ) {
            return $ruta;
        }

        $ruta = ltrim($ruta, '/');

        if (Str::startsWith($ruta, 'public/')) {
            $ruta = substr($ruta, 7);
        }

        return Str::startsWith($ruta, 'storage/')
            ? asset($ruta)
            : asset('storage/' . $ruta);
    }

    private function imagenPredeterminada(): string
    {
        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800" viewBox="0 0 1200 800">
    <defs>
        <linearGradient id="f" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#07111f"/>
            <stop offset="1" stop-color="#0d6a43"/>
        </linearGradient>
    </defs>
    <rect width="1200" height="800" fill="url(#f)"/>
    <circle cx="930" cy="160" r="260" fill="#22c55e" fill-opacity=".12"/>
    <circle cx="180" cy="680" r="300" fill="#17457f" fill-opacity=".28"/>
    <text x="600" y="390" text-anchor="middle" fill="#ffffff" font-family="Arial, sans-serif" font-size="112" font-weight="700">SIDAN</text>
    <text x="600" y="465" text-anchor="middle" fill="#cbd5e1" font-family="Arial, sans-serif" font-size="34">Actividad</text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,'
            . base64_encode($svg);
    }
}