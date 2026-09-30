<?php

namespace App\Http\Controllers;

use App\Services\WompiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Throwable;

class WompiController extends Controller
{
    public function prueba(WompiService $wompi): RedirectResponse
    {
        try {
            $referencia = 'SIDAN-PRUEBA-'.Str::upper(Str::random(10));

            $respuesta = $wompi->crearEnlacePago([
                'identificadorEnlaceComercio' => $referencia,
                'monto' => 1.00,
                'nombreProducto' => 'Prueba de pago SIDAN',
            ]);

            if (empty($respuesta['urlEnlace'])) {
                return back()->with('error', 'Wompi no devolvió una URL de pago.');
            }

            return redirect()->away($respuesta['urlEnlace']);
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'No se pudo crear el enlace de pago con Wompi.');
        }
    }
}