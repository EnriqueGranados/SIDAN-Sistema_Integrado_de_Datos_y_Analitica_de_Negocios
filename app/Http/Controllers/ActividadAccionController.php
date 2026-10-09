<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActividadAccionController extends Controller
{
    public function participar(Request $request, string $slug): RedirectResponse
    {
        $actividad = $this->buscarActividadPublica($slug);

        if ($respuesta = $this->validarUsuarioAutenticado($request)) {
            return $respuesta;
        }

        if ($respuesta = $this->exigirCuentaSiCorresponde($request, $actividad)) {
            return $respuesta;
        }

        if (!$actividad->requiereInscripcionGeneral()) {
            return redirect()
                ->route('activities.show', $actividad->slug)
                ->with('error', 'Esta actividad no utiliza inscripción general.');
        }

        if (!$this->inscripcionDisponible($actividad)) {
            return redirect()
                ->route('activities.show', $actividad->slug)
                ->with(
                    'error',
                    'La inscripción para esta actividad no está disponible en este momento.'
                );
        }

        $url = route('activities.show', [
            'slug' => $actividad->slug,
            'accion' => 'participar',
        ]) . '#participar';

        return redirect($url)
            ->with('accion_actividad', 'participar')
            ->with(
                'info',
                $actividad->participacionConPago()
                    ? 'La actividad requiere inscripción con pago. Revisa el cronograma y las condiciones antes de continuar.'
                    : 'La inscripción es gratuita. Revisa el cronograma y los detalles antes de continuar.'
            );
    }

    public function comprar(Request $request, string $slug): RedirectResponse
    {
        $actividad = $this->buscarActividadPublica($slug);

        if ($respuesta = $this->validarUsuarioAutenticado($request)) {
            return $respuesta;
        }

        if ($respuesta = $this->exigirCuentaSiCorresponde($request, $actividad)) {
            return $respuesta;
        }

        $tieneProductos = DB::table('tbl_items_actividad')
            ->where('id_actividad', $actividad->id_actividad)
            ->where('activo', true)
            ->exists();

        if (!$tieneProductos) {
            return redirect()
                ->route('activities.show', $actividad->slug)
                ->with('error', 'Esta actividad no tiene productos o servicios disponibles.');
        }

        $url = route('activities.show', [
            'slug' => $actividad->slug,
            'accion' => 'comprar',
        ]) . '#productos';

        return redirect($url)
            ->with('accion_actividad', 'comprar')
            ->with(
                'info',
                'Estos son los productos y servicios disponibles para esta actividad.'
            );
    }

    private function exigirCuentaSiCorresponde(
        Request $request,
        Actividad $actividad
    ): ?RedirectResponse {
        if (!$actividad->requiere_cuenta || $request->user()) {
            return null;
        }

        return redirect()
            ->guest(route('login'))
            ->with(
                'info',
                'Inicia sesión para continuar. Después regresarás automáticamente a esta actividad.'
            );
    }

    private function validarUsuarioAutenticado(Request $request): ?RedirectResponse
    {
        $usuario = $request->user();

        if (!$usuario) {
            return null;
        }

        if ($usuario->eliminado || !$usuario->estado_activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    $usuario->eliminado
                        ? 'Tu cuenta ha sido eliminada del sistema. Contacta al administrador.'
                        : 'Tu cuenta está bloqueada. Contacta al administrador para reactivarla.'
                );
        }

        return null;
    }

    private function buscarActividadPublica(string $slug): Actividad
    {
        return Actividad::query()
            ->visiblesEnPortal()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function inscripcionDisponible(Actividad $actividad): bool
    {
        $ahora = now();

        if (
            $actividad->inscripcion_desde
            && $ahora->lt($actividad->inscripcion_desde)
        ) {
            return false;
        }

        if (
            $actividad->inscripcion_hasta
            && $ahora->gt($actividad->inscripcion_hasta)
        ) {
            return false;
        }

        $estadoOperativo = strtolower((string) $actividad->estado_operativo);

        if (
            in_array(
                $estadoOperativo,
                [
                    'cancelada',
                    'cancelado',
                    'finalizada',
                    'finalizado',
                    'cerrada',
                    'cerrado',
                ],
                true
            )
        ) {
            return false;
        }

        return true;
    }
}
