<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Etiqueta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EtiquetaController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $estado = (string) $request->input('estado', '');

        $etiquetas = Etiqueta::query()
            ->withCount('actividades')
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery
                        ->where('nombre', 'ilike', "%{$buscar}%")
                        ->orWhere('slug', 'ilike', "%{$buscar}%");
                });
            })
            ->when($estado === 'activo', function ($query) {
                $query->where('activo', true);
            })
            ->when($estado === 'inactivo', function ($query) {
                $query->where('activo', false);
            })
            ->orderBy('nombre')
            ->orderBy('id_etiqueta')
            ->paginate(10)
            ->withQueryString();

        return view('admin.etiquetas.listado', compact(
            'etiquetas',
            'buscar',
            'estado'
        ));
    }

    public function create()
    {
        return view('admin.etiquetas.crear');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:120',
                Rule::unique('tbl_etiquetas', 'nombre'),
            ],
            'activo' => [
                'nullable',
                'boolean',
            ],
        ]);

        Etiqueta::create([
            'nombre' => trim($datos['nombre']),
            'slug' => $this->generarSlugUnico($datos['nombre']),
            'activo' => $request->boolean('activo'),
        ]);

        return redirect()
            ->route('admin.etiquetas.index')
            ->with('success', 'Etiqueta creada correctamente.');
    }

    public function edit(Etiqueta $etiqueta)
    {
        $etiqueta->loadCount('actividades');

        return view('admin.etiquetas.editar', compact('etiqueta'));
    }

    public function update(
        Request $request,
        Etiqueta $etiqueta
    ) {
        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:120',
                Rule::unique('tbl_etiquetas', 'nombre')
                    ->ignore(
                        $etiqueta->id_etiqueta,
                        'id_etiqueta'
                    ),
            ],
            'activo' => [
                'nullable',
                'boolean',
            ],
        ]);

        $nombre = trim($datos['nombre']);

        $etiqueta->update([
            'nombre' => $nombre,
            'slug' => $this->generarSlugUnico(
                $nombre,
                $etiqueta->id_etiqueta
            ),
            'activo' => $request->boolean('activo'),
        ]);

        return redirect()
            ->route('admin.etiquetas.index')
            ->with('success', 'Etiqueta actualizada correctamente.');
    }

    public function destroy(Etiqueta $etiqueta)
    {
        if ($etiqueta->actividades()->exists()) {
            return redirect()
                ->route('admin.etiquetas.index')
                ->with(
                    'error',
                    'No puedes eliminar una etiqueta que está asignada a actividades.'
                );
        }

        $etiqueta->delete();

        return redirect()
            ->route('admin.etiquetas.index')
            ->with('success', 'Etiqueta eliminada correctamente.');
    }

    private function generarSlugUnico(
        string $nombre,
        ?int $ignorarId = null
    ): string {
        $base = Str::slug($nombre);

        if ($base === '') {
            $base = 'etiqueta';
        }

        $slug = $base;
        $contador = 2;

        while (
            Etiqueta::query()
                ->when(
                    $ignorarId !== null,
                    fn ($query) => $query->where(
                        'id_etiqueta',
                        '!=',
                        $ignorarId
                    )
                )
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$base}-{$contador}";
            $contador++;
        }

        return $slug;
    }
}