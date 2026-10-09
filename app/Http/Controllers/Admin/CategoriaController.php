<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $estado = $request->input('estado');

        $categorias = Categoria::query()
            ->withCount('actividades')
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('nombre', 'ilike', "%{$buscar}%")
                        ->orWhere('slug', 'ilike', "%{$buscar}%")
                        ->orWhere('descripcion', 'ilike', "%{$buscar}%");
                });
            })
            ->when(in_array($estado, ['activo', 'inactivo'], true), function ($query) use ($estado) {
                $query->where('activo', $estado === 'activo');
            })
            ->orderBy('orden')
            ->orderBy('id_categoria')
            ->paginate(10)
            ->withQueryString();

        return view('admin.categorias.listado', compact(
            'categorias',
            'buscar',
            'estado'
        ));
    }

    public function create()
    {
        return view('admin.categorias.crear');
    }

    public function store(Request $request)
    {
        $request->merge([
            'nombre' => trim((string) $request->nombre),
            'descripcion' => $request->filled('descripcion')
                ? trim((string) $request->descripcion)
                : null,
        ]);

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tbl_categorias', 'nombre'),
            ],
            'descripcion' => [
                'nullable',
                'string',
            ],
            'activo' => [
                'required',
                'boolean',
            ],
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.string' => 'El nombre de la categoría debe ser un texto válido.',
            'nombre.max' => 'El nombre de la categoría no puede superar los 100 caracteres.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
            'descripcion.string' => 'La descripción debe ser un texto válido.',
            'activo.required' => 'Debes indicar el estado de la categoría.',
            'activo.boolean' => 'El estado de la categoría no es válido.',
        ]);

        $slug = $this->generarSlugUnico($validated['nombre']);

        try {
            $siguienteOrden = ((int) Categoria::max('orden')) + 1;

            Categoria::create([
                'nombre' => $validated['nombre'],
                'slug' => $slug,
                'descripcion' => $validated['descripcion'] ?? null,
                'orden' => $siguienteOrden,
                'activo' => $validated['activo'],
            ]);

            return redirect()
                ->route('admin.categorias.index')
                ->with('success', 'La categoría fue creada correctamente.');
        } catch (QueryException $e) {
            Log::error('Error al crear categoría', [
                'error' => $e->getMessage(),
            ]);

            if (($e->errorInfo[0] ?? null) === '23505') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'nombre' => 'Ya existe una categoría con ese nombre o slug.',
                    ]);
            }

            return back()
                ->withInput()
                ->with('error', 'No se pudo crear la categoría. Inténtalo nuevamente.');
        }
    }

    public function edit(Categoria $categoria)
    {
        $categoria->loadCount('actividades');

        return view('admin.categorias.editar', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->merge([
            'nombre' => trim((string) $request->nombre),
            'descripcion' => $request->filled('descripcion')
                ? trim((string) $request->descripcion)
                : null,
        ]);

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tbl_categorias', 'nombre')
                    ->ignore($categoria->id_categoria, 'id_categoria'),
            ],
            'descripcion' => [
                'nullable',
                'string',
            ],
            'activo' => [
                'required',
                'boolean',
            ],
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.string' => 'El nombre de la categoría debe ser un texto válido.',
            'nombre.max' => 'El nombre de la categoría no puede superar los 100 caracteres.',
            'nombre.unique' => 'Ya existe otra categoría con ese nombre.',
            'descripcion.string' => 'La descripción debe ser un texto válido.',
            'activo.required' => 'Debes indicar el estado de la categoría.',
            'activo.boolean' => 'El estado de la categoría no es válido.',
        ]);

        $slug = $this->generarSlugUnico(
            $validated['nombre'],
            $categoria->id_categoria
        );

        try {
            $categoria->update([
                'nombre' => $validated['nombre'],
                'slug' => $slug,
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $validated['activo'],
            ]);

            return redirect()
                ->route('admin.categorias.index')
                ->with('success', 'La categoría fue actualizada correctamente.');
        } catch (QueryException $e) {
            Log::error('Error al actualizar categoría', [
                'categoria' => $categoria->id_categoria,
                'error' => $e->getMessage(),
            ]);

            if (($e->errorInfo[0] ?? null) === '23505') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'nombre' => 'Ya existe otra categoría con ese nombre o slug.',
                    ]);
            }

            return back()
                ->withInput()
                ->with('error', 'No se pudo actualizar la categoría. Inténtalo nuevamente.');
        }
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'categorias' => [
                'required',
                'array',
                'min:1',
            ],
            'categorias.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('tbl_categorias', 'id_categoria'),
            ],
        ], [
            'categorias.required' => 'Debes enviar las categorías a ordenar.',
            'categorias.array' => 'El orden de categorías no es válido.',
            'categorias.min' => 'Debes enviar al menos una categoría.',
            'categorias.*.integer' => 'Una de las categorías enviadas no es válida.',
            'categorias.*.distinct' => 'No pueden existir categorías repetidas.',
            'categorias.*.exists' => 'Una de las categorías enviadas ya no existe.',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                foreach ($validated['categorias'] as $indice => $idCategoria) {
                    Categoria::where('id_categoria', $idCategoria)
                        ->update([
                            'orden' => $indice + 1,
                        ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'El orden de las categorías fue actualizado.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al reordenar categorías', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar el nuevo orden.',
            ], 500);
        }
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->actividades()->exists()) {
            return back()->with(
                'error',
                'No se puede eliminar la categoría porque tiene actividades asociadas.'
            );
        }

        try {
            DB::transaction(function () use ($categoria) {
                $categoria->delete();

                $categorias = Categoria::query()
                    ->orderBy('orden')
                    ->orderBy('id_categoria')
                    ->get();

                foreach ($categorias as $indice => $categoriaRestante) {
                    $nuevoOrden = $indice + 1;

                    if ((int) $categoriaRestante->orden !== $nuevoOrden) {
                        $categoriaRestante->update([
                            'orden' => $nuevoOrden,
                        ]);
                    }
                }
            });

            return redirect()
                ->route('admin.categorias.index')
                ->with('success', 'La categoría fue eliminada correctamente.');
        } catch (QueryException $e) {
            Log::error('Error al eliminar categoría', [
                'categoria' => $categoria->id_categoria,
                'error' => $e->getMessage(),
            ]);

            if (($e->errorInfo[0] ?? null) === '23503') {
                return back()->with(
                    'error',
                    'No se puede eliminar la categoría porque está siendo utilizada.'
                );
            }

            return back()->with(
                'error',
                'No se pudo eliminar la categoría. Inténtalo nuevamente.'
            );
        }
    }

    private function generarSlugUnico(string $nombre, ?int $ignorarId = null): string
    {
        $base = Str::slug($nombre);

        if ($base === '') {
            $base = 'categoria';
        }

        $base = Str::limit($base, 100, '');
        $slug = $base;
        $contador = 2;

        while (
            Categoria::query()
                ->where('slug', $slug)
                ->when(
                    $ignorarId !== null,
                    fn ($query) => $query->where('id_categoria', '!=', $ignorarId)
                )
                ->exists()
        ) {
            $sufijo = '-' . $contador;
            $slug = Str::limit($base, 110 - strlen($sufijo), '') . $sufijo;
            $contador++;
        }

        return $slug;
    }
}