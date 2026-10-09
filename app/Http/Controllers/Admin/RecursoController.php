<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recurso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecursoController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $estado = $request->input('estado', '');
        $movilidad = $request->input('movilidad', '');
        $categoria = trim((string) $request->input('categoria', ''));

        $recursos = Recurso::query()
            ->withCount('espacios')
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery
                        ->where('nombre', 'ilike', "%{$buscar}%")
                        ->orWhere('descripcion', 'ilike', "%{$buscar}%")
                        ->orWhere('categoria', 'ilike', "%{$buscar}%");
                });
            })
            ->when($estado === 'activo', fn ($query) => $query->where('activo', true))
            ->when($estado === 'inactivo', fn ($query) => $query->where('activo', false))
            ->when($movilidad === 'movil', fn ($query) => $query->where('es_movil', true))
            ->when($movilidad === 'fijo', fn ($query) => $query->where('es_movil', false))
            ->when($categoria !== '', fn ($query) => $query->where('categoria', $categoria))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        $categorias = Recurso::query()
            ->whereNotNull('categoria')
            ->where('categoria', '<>', '')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');

        return view('admin.recursos.listado', compact(
            'recursos',
            'categorias',
            'buscar',
            'estado',
            'movilidad',
            'categoria'
        ));
    }

    public function create(): View
    {
        $categorias = $this->categoriasDisponibles();

        return view('admin.recursos.crear', compact('categorias'));
    }
    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        $datos['nombre'] = $this->limpiarTexto($datos['nombre']);
        $datos['descripcion'] = $this->limpiarOpcional($datos['descripcion'] ?? null);
        $datos['categoria'] = $this->limpiarOpcional($datos['categoria'] ?? null);
        $datos['unidad_medida'] = $this->limpiarTexto($datos['unidad_medida']);
        $datos['es_movil'] = $request->boolean('es_movil');
        $datos['activo'] = $request->boolean('activo');

        Recurso::create($datos);

        return redirect()
            ->route('admin.recursos.index')
            ->with('success', 'El recurso se creó correctamente.');
    }

    public function edit(Recurso $recurso): View
    {
        $recurso->loadCount('espacios');

        $categorias = $this->categoriasDisponibles();

        return view('admin.recursos.editar', compact('recurso', 'categorias'));
    }

    public function update(Request $request, Recurso $recurso): RedirectResponse
    {
        $datos = $this->validar($request, $recurso);

        $datos['nombre'] = $this->limpiarTexto($datos['nombre']);
        $datos['descripcion'] = $this->limpiarOpcional($datos['descripcion'] ?? null);
        $datos['categoria'] = $this->limpiarOpcional($datos['categoria'] ?? null);
        $datos['unidad_medida'] = $this->limpiarTexto($datos['unidad_medida']);
        $datos['es_movil'] = $request->boolean('es_movil');
        $datos['activo'] = $request->boolean('activo');

        $recurso->update($datos);

        return redirect()
            ->route('admin.recursos.index')
            ->with('success', 'El recurso se actualizó correctamente.');
    }

    public function destroy(Recurso $recurso): RedirectResponse
    {
        if ($recurso->espacios()->exists()) {
            return redirect()
                ->route('admin.recursos.index')
                ->with(
                    'error',
                    'No se puede eliminar el recurso porque está asignado a uno o más espacios. Puedes desactivarlo si ya no deseas utilizarlo.'
                );
        }

        $recurso->delete();

        return redirect()
            ->route('admin.recursos.index')
            ->with('success', 'El recurso se eliminó correctamente.');
    }

    private function validar(Request $request, ?Recurso $recurso = null): array
    {
        return $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:120',
                Rule::unique('tbl_recursos', 'nombre')
                    ->ignore($recurso?->id_recurso, 'id_recurso'),
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'categoria' => [
                'nullable',
                'string',
                'max:80',
            ],
            'unidad_medida' => [
                'required',
                'string',
                'max:40',
            ],
            'es_movil' => [
                'nullable',
                'boolean',
            ],
            'activo' => [
                'nullable',
                'boolean',
            ],
        ], [
            'nombre.required' => 'Escribe el nombre del recurso.',
            'nombre.max' => 'El nombre no puede superar los 120 caracteres.',
            'nombre.unique' => 'Ya existe un recurso con ese nombre.',
            'descripcion.max' => 'La descripción no puede superar los 1000 caracteres.',
            'categoria.max' => 'La categoría no puede superar los 80 caracteres.',
            'unidad_medida.required' => 'Indica cómo se contabiliza este recurso.',
            'unidad_medida.max' => 'La unidad de medida no puede superar los 40 caracteres.',
        ]);
    }

    private function limpiarTexto(string $valor): string
    {
        return Str::squish($valor);
    }

    private function limpiarOpcional(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = Str::squish($valor);

        return $valor === '' ? null : $valor;
    }

    private function categoriasDisponibles()
    {
        $categoriasBase = collect([
            'Audiovisual',
            'Audio',
            'Mobiliario',
            'Tecnología',
            'Conectividad',
            'Energía eléctrica',
            'Presentación y señalización',
            'Logística',
            'Accesibilidad',
        ]);

        $categoriasRegistradas = Recurso::query()
            ->whereNotNull('categoria')
            ->where('categoria', '<>', '')
            ->distinct()
            ->pluck('categoria');

        return $categoriasBase
            ->merge($categoriasRegistradas)
            ->unique(fn ($categoria) => mb_strtolower($categoria))
            ->sort()
            ->values();
    }
}