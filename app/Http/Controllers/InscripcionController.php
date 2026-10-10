<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Inscripcion;
use App\Services\InscripcionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InscripcionController extends Controller
{
    // GET: mostrar el formulario.
    public function create(
        Request $request,
        string $slug,
        InscripcionService $servicio
    ): View {
        $actividad = Actividad::visiblesEnPortal()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->verificarCuenta($request, $actividad);
        $servicio->verificar($actividad);
        $this->verificarFormularioPersonalizado($actividad);

        $usuario = $request->user();

        $nombre = $usuario
            ? trim(
                ($usuario->informacion_personal?->nombres ?? '') .
                ' ' .
                ($usuario->informacion_personal?->apellidos ?? '')
            )
            : '';

        $correo = $usuario?->correo ?? '';

        return view('inscripciones.create', compact(
            'actividad',
            'nombre',
            'correo'
        ));
    }

    // POST: registrar una inscripción.
    public function store(
        Request $request,
        string $slug,
        InscripcionService $servicio
    ): RedirectResponse {
        $actividad = Actividad::visiblesEnPortal()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->verificarCuenta($request, $actividad);
        $this->verificarFormularioPersonalizado($actividad);

        $usuario = $request->user();

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'correo' => ['required', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:25'],
            'confirmacion' => ['accepted'],
        ]);

        // Para cuentas autenticadas usamos el correo real de la cuenta.
        if ($usuario) {
            $nombre = trim(
                ($usuario->informacion_personal?->nombres ?? '') .
                ' ' .
                ($usuario->informacion_personal?->apellidos ?? '')
            );

            if ($nombre !== '') {
                $datos['nombre'] = $nombre;
            }

            $datos['correo'] = $usuario->correo;
        }

        $inscripcion = $servicio->registrar(
            $actividad,
            $usuario?->id_usuario,
            $datos
        );

        // Permitimos al visitante consultar su operación
        // durante la sesión actual.
        $request->session()->put(
            'sidan.inscripciones.' . $inscripcion->id_inscripcion,
            true
        );

        // Si requiere pago, enviamos al resumen de la orden.
        if ($inscripcion->orden) {
            $request->session()->put(
                'sidan.ordenes.' . $inscripcion->orden->id_orden,
                true
            );

            return redirect()->route(
                'sidan.ordenes.show',
                $inscripcion->orden->id_orden
            );
        }

        // Si es gratuita, mostramos su confirmación.
        return redirect()->route(
            'sidan.inscripciones.show',
            $inscripcion->id_inscripcion
        );
    }

    // GET: consultar el estado de una inscripción.
    public function show(
        Request $request,
        Inscripcion $inscripcion
    ): View {
        $propietario = $request->user()
            && $inscripcion->id_usuario !== null
            && (int) $inscripcion->id_usuario
                === (int) $request->user()->id_usuario;

        $sesion = $request->session()->has(
            'sidan.inscripciones.' . $inscripcion->id_inscripcion
        );

        abort_unless($propietario || $sesion, 403);

        return view('inscripciones.show', [
            'inscripcion' => $inscripcion->load(
                'actividad',
                'orden'
            ),
        ]);
    }

    private function verificarCuenta(
        Request $request,
        Actividad $actividad
    ): void {
        $usuario = $request->user();

        abort_if(
            $usuario && ($usuario->eliminado || !$usuario->estado_activo),
            403,
            'La cuenta no está habilitada.'
        );

        abort_if(
            $actividad->requiere_cuenta && !$usuario,
            403,
            'Debes iniciar sesión para esta actividad.'
        );
    }

    // Temporal: evita perder respuestas de formularios dinámicos.
    private function verificarFormularioPersonalizado(
        Actividad $actividad
    ): void {
        $tieneCampos = $actividad->formularios()
            ->where('estado', 'publicado')
            ->whereHas('campos', function ($query) {
                $query->where('activo', true);
            })
            ->exists();

        abort_if(
            $tieneCampos,
            422,
            'Esta actividad necesita el módulo de formularios dinámicos.'
        );
    }
}
