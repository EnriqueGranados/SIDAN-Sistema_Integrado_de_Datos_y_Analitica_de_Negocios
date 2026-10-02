<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\RevisionActividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActividadRevisionController extends Controller
{
    public function index(Request $request)
    {
        $query = Actividad::query()
            ->with('categoria')
            ->where('estado_publicacion', 'pendiente_revision')
            ->orderByDesc('updated_at');

        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);

            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'ilike', "%{$buscar}%")
                    ->orWhere('resumen', 'ilike', "%{$buscar}%");
            });
        }

        $actividades = $query->paginate(15)->withQueryString();

        return view('admin.actividades.revision.listado', compact('actividades'));
    }

    public function show(Actividad $actividad)
    {
        abort_unless($actividad->estado_publicacion === 'pendiente_revision', 404);

        $actividad->load([
            'categoria',
            'etiquetas',
            'creador.informacion_personal',
            'items.variantes',
            'items.medios',
            'sesiones',
            'medios',
            'responsables',
            'revisiones.usuario.informacion_personal',
        ]);

        return view('admin.actividades.revision.detalle', compact('actividad'));
    }

    public function aprobar(Request $request, Actividad $actividad)
    {
        if ($actividad->estado_publicacion !== 'pendiente_revision') {
            return redirect()->route('admin.actividades.revision.index')
                ->with('error', 'Esta actividad ya no está pendiente de revisión.');
        }

        $data = $request->validate([
            'observacion' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($actividad, $data) {
            $actividad->estado_publicacion = 'aprobada';
            $actividad->actualizado_por = Auth::id();
            $actividad->save();

            $this->registrarRevision(
                $actividad,
                'aprobada',
                $data['observacion'] ?? 'Actividad aprobada.'
            );
        });

        return redirect()->route('admin.actividades.revision.index')
            ->with('success', 'Actividad aprobada correctamente.');
    }

    public function solicitarCambios(Request $request, Actividad $actividad)
    {
        if ($actividad->estado_publicacion !== 'pendiente_revision') {
            return redirect()->route('admin.actividades.revision.index')
                ->with('error', 'Esta actividad ya no está pendiente de revisión.');
        }

        $data = $request->validate([
            'observacion' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'observacion.required' => 'Debes indicar los cambios solicitados.',
            'observacion.min' => 'La observación debe contener al menos 5 caracteres.',
        ]);

        DB::transaction(function () use ($actividad, $data) {
            $actividad->estado_publicacion = 'cambios_solicitados';
            $actividad->actualizado_por = Auth::id();
            $actividad->save();

            $this->registrarRevision(
                $actividad,
                'cambios_solicitados',
                $data['observacion']
            );
        });

        return redirect()->route('admin.actividades.revision.index')
            ->with('success', 'Los cambios fueron solicitados correctamente.');
    }

    public function rechazar(Request $request, Actividad $actividad)
    {
        if ($actividad->estado_publicacion !== 'pendiente_revision') {
            return redirect()->route('admin.actividades.revision.index')
                ->with('error', 'Esta actividad ya no está pendiente de revisión.');
        }

        $data = $request->validate([
            'observacion' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'observacion.required' => 'Debes indicar el motivo del rechazo.',
            'observacion.min' => 'La observación debe contener al menos 5 caracteres.',
        ]);

        DB::transaction(function () use ($actividad, $data) {
            $actividad->estado_publicacion = 'rechazada';
            $actividad->actualizado_por = Auth::id();
            $actividad->save();

            $this->registrarRevision(
                $actividad,
                'rechazada',
                $data['observacion']
            );
        });

        return redirect()->route('admin.actividades.revision.index')
            ->with('success', 'Actividad rechazada.');
    }

    private function registrarRevision(Actividad $actividad, string $accion, ?string $observacion = null): void
    {
        RevisionActividad::create([
            'id_actividad' => $actividad->id_actividad,
            'numero_revision' => $actividad->revision_actual,
            'id_usuario' => Auth::id(),
            'accion' => $accion,
            'observacion' => $observacion,
            'creado_en' => now(),
        ]);
    }
}