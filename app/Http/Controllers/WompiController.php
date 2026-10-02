<?php

namespace App\Http\Controllers;

use App\Services\WompiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Throwable;

class WompiController extends Controller
{
    // Crear enlace de pago de prueba con Wompi
    public function prueba(WompiService $wompi): RedirectResponse
    {
        try {

            // Generar referencia única para identificar el pago
            $referencia = 'SIDAN-PRUEBA-' . Str::upper(Str::random(10));

            // Solicitar a Wompi la creación del enlace de pago
            $respuesta = $wompi->crearEnlacePago([
                'identificadorEnlaceComercio' => $referencia,
                'monto' => 1.00,
                'nombreProducto' => 'Prueba de pago SIDAN',
            ]);

            // Verificar que Wompi haya devuelto una URL de pago
            if (empty($respuesta['urlEnlace'])) {
                return back()->with(
                    'error',
                    'Wompi no devolvió una URL de pago.'
                );
            }

            // Redirigir al usuario hacia la pasarela de pago de Wompi
            return redirect()->away($respuesta['urlEnlace']);

        } catch (Throwable $e) {

            // Registrar cualquier error ocurrido durante el proceso
            report($e);

            // Regresar a la página anterior mostrando un mensaje de error
            return back()->with(
                'error',
                'No se pudo crear el enlace de pago con Wompi.'
            );
        }
    }
}