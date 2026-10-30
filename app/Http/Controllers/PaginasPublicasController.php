<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Etiqueta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class PaginasPublicasController extends Controller
{
    public function categorias(): View
    {
        $categorias = Categoria::query()
            ->where('activo', true)
            ->whereHas('actividades', fn (Builder $query) => $query->visiblesEnPortal())
            ->withCount([
                'actividades as actividades_publicas_count' =>
                    fn (Builder $query) => $query->visiblesEnPortal(),
            ])
            ->orderByDesc('actividades_publicas_count')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $etiquetas = Etiqueta::query()
            ->where('activo', true)
            ->whereHas('actividades', fn (Builder $query) => $query->visiblesEnPortal())
            ->withCount([
                'actividades as actividades_publicas_count' =>
                    fn (Builder $query) => $query->visiblesEnPortal(),
            ])
            ->orderByDesc('actividades_publicas_count')
            ->orderBy('nombre')
            ->get();

        return view('publico.categorias', compact('categorias', 'etiquetas'));
    }

    public function comoFunciona(): View
    {
        return view('publico.como-funciona');
    }
}
