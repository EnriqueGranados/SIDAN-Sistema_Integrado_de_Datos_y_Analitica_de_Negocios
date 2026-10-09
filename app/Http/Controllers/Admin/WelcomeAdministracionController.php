<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\WelcomeEspacio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WelcomeAdministracionController extends Controller
{
    private const MAX_HERO = 6;
    private const MAX_EXPLORAR = 12;

    public function index(): View
    {
        $espacios = WelcomeEspacio::query()
            ->with([
                'actividad' => fn ($query) => $query->with([
                    'categoria',
                    'portada',
                ]),
            ])
            ->orderBy('seccion')
            ->orderBy('orden')
            ->get();

        foreach ($espacios as $espacio) {
            if (!$espacio->actividad) {
                continue;
            }

            $espacio->actividad->imagen_welcome = $this->resolverImagen(
                $espacio->actividad->portada?->url
            );
            $espacio->actividad->estado_welcome = $this->estadoVisibilidad(
                $espacio->actividad
            );
        }

        $espaciosHero = $espacios
            ->where('seccion', 'hero')
            ->values();

        $espaciosExplorar = $espacios
            ->where('seccion', 'explorar')
            ->values();

        $destacadaConfigurada = $this->actividadesElegibles()
            ->with([
                'categoria',
                'portada',
            ])
            ->where('destacada', true)
            ->orderByDesc('prioridad')
            ->orderByDesc('id_actividad')
            ->first();

        if ($destacadaConfigurada) {
            $destacadaConfigurada->imagen_welcome = $this->resolverImagen(
                $destacadaConfigurada->portada?->url
            );
            $destacadaConfigurada->estado_welcome = $this->estadoVisibilidad(
                $destacadaConfigurada
            );
        }

        $destacadaAutomatica = $this->actividadesVisiblesAhora()
            ->with([
                'categoria',
                'portada',
            ])
            ->orderByDesc('prioridad')
            ->orderByRaw('realizacion_desde IS NULL')
            ->orderBy('realizacion_desde')
            ->orderByDesc('id_actividad')
            ->first();

        if ($destacadaAutomatica) {
            $destacadaAutomatica->imagen_welcome = $this->resolverImagen(
                $destacadaAutomatica->portada?->url
            );
            $destacadaAutomatica->estado_welcome = $this->estadoVisibilidad(
                $destacadaAutomatica
            );
        }

        return view('admin.welcome.index', [
            'espaciosHero' => $espaciosHero,
            'espaciosExplorar' => $espaciosExplorar,
            'destacadaConfigurada' => $destacadaConfigurada,
            'destacadaAutomatica' => $destacadaAutomatica,
            'destacada' => $destacadaConfigurada ?? $destacadaAutomatica,
            'maxHero' => self::MAX_HERO,
            'maxExplorar' => self::MAX_EXPLORAR,
        ]);
    }

    public function buscarActividades(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $buscar = trim((string) ($datos['q'] ?? ''));

        $query = $this->actividadesElegibles()
            ->with([
                'categoria',
                'portada',
            ]);

        if ($buscar !== '') {
            $patron = '%' . str_replace(
                ['\\', '%', '_'],
                ['\\\\', '\\%', '\\_'],
                $buscar
            ) . '%';

            $query->where(function (Builder $subquery) use ($patron) {
                $subquery
                    ->where('nombre', 'ILIKE', $patron)
                    ->orWhere('resumen', 'ILIKE', $patron)
                    ->orWhereHas('categoria', function (Builder $categoria) use ($patron) {
                        $categoria->where('nombre', 'ILIKE', $patron);
                    });
            });
        }

        $actividades = $query
            ->orderByDesc('prioridad')
            ->orderBy('nombre')
            ->limit(12)
            ->get()
            ->map(function (Actividad $actividad) {
                $estado = $this->estadoVisibilidad($actividad);

                return [
                    'id' => $actividad->id_actividad,
                    'nombre' => $actividad->nombre,
                    'categoria' => $actividad->categoria?->nombre ?? 'Sin categoría',
                    'imagen' => $this->resolverImagen($actividad->portada?->url),
                    'estado' => $estado['estado'],
                    'estado_texto' => $estado['texto'],
                    'detalle' => $estado['detalle'],
                    'prioridad' => (int) $actividad->prioridad,
                ];
            })
            ->values();

        return response()->json([
            'items' => $actividades,
        ]);
    }

    public function establecerDestacada(Request $request): JsonResponse|RedirectResponse
    {
        $datos = $request->validate([
            'id_actividad' => ['required', 'integer'],
        ]);

        $actividad = $this->actividadesElegibles()
            ->where('id_actividad', $datos['id_actividad'])
            ->first();

        if (!$actividad) {
            return $this->respuestaError(
                $request,
                'Solo pueden destacarse actividades públicas y publicadas.',
                422
            );
        }

        DB::transaction(function () use ($actividad) {
            Actividad::query()
                ->where('destacada', true)
                ->where('id_actividad', '!=', $actividad->id_actividad)
                ->update([
                    'destacada' => false,
                ]);

            $actividad->update([
                'destacada' => true,
            ]);
        });

        return $this->respuestaExito(
            $request,
            'Actividad destacada actualizada.'
        );
    }

    public function quitarDestacada(Request $request): JsonResponse|RedirectResponse
    {
        Actividad::query()
            ->where('destacada', true)
            ->update([
                'destacada' => false,
            ]);

        return $this->respuestaExito(
            $request,
            'Selección manual de actividad destacada eliminada.'
        );
    }

    public function actualizarPrioridad(
        Request $request,
        Actividad $actividad
    ): JsonResponse|RedirectResponse {
        $datos = $request->validate([
            'prioridad' => [
                'required',
                'integer',
                Rule::in([25, 50, 75, 100]),
            ],
        ]);

        $esElegible = $this->actividadesElegibles()
            ->where('id_actividad', $actividad->id_actividad)
            ->exists();

        if (!$esElegible) {
            return $this->respuestaError(
                $request,
                'Solo puedes cambiar la prioridad de una actividad pública y publicada desde este panel.',
                422
            );
        }

        $actividad->update([
            'prioridad' => (int) $datos['prioridad'],
        ]);

        return $this->respuestaExito(
            $request,
            'Prioridad actualizada.'
        );
    }

    public function crearEspacio(Request $request): JsonResponse|RedirectResponse
    {
        $datos = $request->validate([
            'seccion' => ['required', 'in:hero,explorar'],
        ]);

        $seccion = $datos['seccion'];
        $maximo = $this->maximoPorSeccion($seccion);
        $cantidad = WelcomeEspacio::query()
            ->where('seccion', $seccion)
            ->count();

        if ($cantidad >= $maximo) {
            return $this->respuestaError(
                $request,
                'Esta sección ya alcanzó el máximo recomendado de espacios para el Welcome.',
                422
            );
        }

        $orden = (int) WelcomeEspacio::query()
            ->where('seccion', $seccion)
            ->max('orden') + 1;

        WelcomeEspacio::query()->create([
            'seccion' => $seccion,
            'nombre' => $this->nombreEspacio($seccion, $orden),
            'orden' => $orden,
            'id_actividad' => null,
        ]);

        return $this->respuestaExito(
            $request,
            'Espacio agregado correctamente.'
        );
    }

    public function asignar(
        Request $request,
        WelcomeEspacio $espacio
    ): JsonResponse|RedirectResponse {
        $datos = $request->validate([
            'id_actividad' => ['required', 'integer'],
        ]);

        $actividad = $this->actividadesElegibles()
            ->where('id_actividad', $datos['id_actividad'])
            ->first();

        if (!$actividad) {
            return $this->respuestaError(
                $request,
                'Solo pueden asignarse actividades públicas y publicadas.',
                422
            );
        }

        DB::transaction(function () use ($espacio, $actividad) {
            WelcomeEspacio::query()
                ->where('seccion', $espacio->seccion)
                ->where('id_actividad', $actividad->id_actividad)
                ->where('id_espacio_welcome', '!=', $espacio->id_espacio_welcome)
                ->update([
                    'id_actividad' => null,
                ]);

            $espacio->update([
                'id_actividad' => $actividad->id_actividad,
            ]);
        });

        return $this->respuestaExito(
            $request,
            'Actividad asignada correctamente.'
        );
    }

    public function quitar(
        Request $request,
        WelcomeEspacio $espacio
    ): JsonResponse|RedirectResponse {
        $espacio->update([
            'id_actividad' => null,
        ]);

        return $this->respuestaExito(
            $request,
            'Actividad retirada del espacio.'
        );
    }

    public function mover(
        Request $request,
        WelcomeEspacio $espacio
    ): JsonResponse|RedirectResponse {
        $datos = $request->validate([
            'direccion' => ['required', 'in:arriba,abajo'],
        ]);

        $operador = $datos['direccion'] === 'arriba' ? '<' : '>';
        $ordenamiento = $datos['direccion'] === 'arriba' ? 'desc' : 'asc';

        $vecino = WelcomeEspacio::query()
            ->where('seccion', $espacio->seccion)
            ->where('orden', $operador, $espacio->orden)
            ->orderBy('orden', $ordenamiento)
            ->first();

        if (!$vecino) {
            return $this->respuestaExito(
                $request,
                'El espacio ya está en el extremo de la sección.'
            );
        }

        DB::transaction(function () use ($espacio, $vecino) {
            $ordenActual = (int) $espacio->orden;
            $ordenVecino = (int) $vecino->orden;

            $vecino->update(['orden' => 0]);
            $espacio->update(['orden' => $ordenVecino]);
            $vecino->update(['orden' => $ordenActual]);
        });

        return $this->respuestaExito(
            $request,
            'Orden actualizado.'
        );
    }

    public function eliminarEspacio(
        Request $request,
        WelcomeEspacio $espacio
    ): JsonResponse|RedirectResponse {
        $seccion = $espacio->seccion;

        DB::transaction(function () use ($espacio, $seccion) {
            $espacio->delete();

            WelcomeEspacio::query()
                ->where('seccion', $seccion)
                ->orderBy('orden')
                ->get()
                ->values()
                ->each(function (WelcomeEspacio $item, int $indice) {
                    $nuevoOrden = $indice + 1;

                    if ((int) $item->orden !== $nuevoOrden) {
                        $item->update(['orden' => -($nuevoOrden + 100)]);
                    }
                });

            WelcomeEspacio::query()
                ->where('seccion', $seccion)
                ->where('orden', '<', 0)
                ->orderBy('orden', 'desc')
                ->get()
                ->each(function (WelcomeEspacio $item) {
                    $item->update([
                        'orden' => abs((int) $item->orden) - 100,
                    ]);
                });
        });

        return $this->respuestaExito(
            $request,
            'Espacio eliminado.'
        );
    }

    private function actividadesElegibles(): Builder
    {
        return Actividad::query()
            ->whereNull('eliminado_en')
            ->where('visibilidad', 'publica')
            ->where('estado_publicacion', Actividad::ESTADO_PUBLICADA);
    }

    private function actividadesVisiblesAhora(): Builder
    {
        $ahora = now();

        return $this->actividadesElegibles()
            ->where(function (Builder $query) use ($ahora) {
                $query
                    ->whereNull('visible_desde')
                    ->orWhere('visible_desde', '<=', $ahora);
            })
            ->where(function (Builder $query) use ($ahora) {
                $query
                    ->whereNull('visible_hasta')
                    ->orWhere('visible_hasta', '>=', $ahora);
            });
    }

    private function estadoVisibilidad(Actividad $actividad): array
    {
        if ($actividad->estado_publicacion !== Actividad::ESTADO_PUBLICADA) {
            return [
                'estado' => 'no_publicada',
                'texto' => 'No publicada',
                'detalle' => 'La actividad está asignada, pero no puede mostrarse hasta que vuelva a estar publicada.',
            ];
        }

        if ($actividad->visibilidad !== 'publica') {
            return [
                'estado' => 'no_publica',
                'texto' => 'No pública',
                'detalle' => 'La actividad está asignada, pero su visibilidad no permite mostrarla en el portal.',
            ];
        }

        $ahora = now();

        if ($actividad->visible_desde && $actividad->visible_desde->gt($ahora)) {
            return [
                'estado' => 'programada',
                'texto' => 'Programada',
                'detalle' => 'Visible desde ' . $actividad->visible_desde->format('d/m/Y H:i'),
            ];
        }

        if ($actividad->visible_hasta && $actividad->visible_hasta->lt($ahora)) {
            return [
                'estado' => 'finalizada',
                'texto' => 'Fuera de ventana',
                'detalle' => 'Dejó de ser visible el ' . $actividad->visible_hasta->format('d/m/Y H:i'),
            ];
        }

        return [
            'estado' => 'visible',
            'texto' => 'Visible ahora',
            'detalle' => 'Se muestra actualmente en el portal público.',
        ];
    }

    private function maximoPorSeccion(string $seccion): int
    {
        return $seccion === 'hero'
            ? self::MAX_HERO
            : self::MAX_EXPLORAR;
    }

    private function nombreEspacio(string $seccion, int $orden): string
    {
        return $seccion === 'hero'
            ? 'Hero ' . $orden
            : 'Actividad ' . $orden;
    }

    private function respuestaExito(
        Request $request,
        string $mensaje
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $mensaje,
            ]);
        }

        return back()->with('success', $mensaje);
    }

    private function respuestaError(
        Request $request,
        string $mensaje,
        int $status
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $mensaje,
            ], $status);
        }

        return back()->withErrors([
            'welcome' => $mensaje,
        ]);
    }

    private function resolverImagen(?string $ruta): string
    {
        if (!$ruta) {
            return $this->imagenPredeterminada();
        }

        $ruta = str_replace('\\', '/', trim($ruta));

        if (Str::startsWith($ruta, ['http://', 'https://', 'data:'])) {
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

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
