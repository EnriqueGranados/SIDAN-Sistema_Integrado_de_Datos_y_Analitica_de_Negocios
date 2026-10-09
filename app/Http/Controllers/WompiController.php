<?php

namespace App\Http\Controllers;

use App\Services\WompiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Http\Request;

class WompiController extends Controller
{
    public function prueba(WompiService $wompi): RedirectResponse|JsonResponse
    {
        try {
            $referencia = 'SIDAN-PRUEBA-' . Str::upper(Str::random(10));

            $respuesta = $wompi->crearEnlacePago([
                'identificadorEnlaceComercio' => $referencia,
                'monto' => 1.00,
                'nombreProducto' => 'Prueba de pago SIDAN',

                'configuracion' => [
                    'urlWebhook' => config('services.wompi.webhook_url'),
                    'notificarTransaccionCliente' => false,
                ],
            ]);

            $idEnlace = $respuesta['idEnlace'] ?? null;
            $urlEnlace = $respuesta['urlEnlace'] ?? null;

            if (!$idEnlace || !$urlEnlace) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wompi no devolvió la información necesaria del enlace de pago.',
                    'respuesta' => $respuesta,
                ], 500);
            }

            session()->put('wompi_prueba', [
                'id_enlace' => (int) $idEnlace,
                'referencia' => $referencia,
            ]);

            return redirect()->away($urlEnlace);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function webhook(Request $request, WompiService $wompi)
    {
        $body = $request->getContent();
        $hashRecibido = $request->header('wompi_hash');

        if (!$hashRecibido) {
            \Log::warning('Webhook Wompi rechazado: hash ausente.');

            return response()->json([
                'success' => false,
                'message' => 'Webhook no autorizado.',
            ], 401);
        }

        $hashCalculado = hash_hmac(
            'sha256',
            $body,
            config('services.wompi.client_secret')
        );

        if (
            !hash_equals(
                strtolower($hashCalculado),
                strtolower($hashRecibido)
            )
        ) {
            \Log::warning('Webhook Wompi rechazado: hash inválido.');

            return response()->json([
                'success' => false,
                'message' => 'Webhook no autorizado.',
            ], 401);
        }

        $datos = json_decode($body, true);

        if (!is_array($datos)) {
            return response()->json([
                'success' => false,
                'message' => 'Contenido inválido.',
            ], 400);
        }

        $idTransaccion = $datos['IdTransaccion'] ?? null;
        $resultado = $datos['ResultadoTransaccion'] ?? null;
        $monto = $datos['Monto'] ?? null;
        $esProductiva = $datos['EsProductiva'] ?? null;
        $referencia = $datos['EnlacePago']['IdentificadorEnlaceComercio'] ?? null;
        $idEnlace = $datos['EnlacePago']['Id'] ?? null;

        \Log::info('WEBHOOK WOMPI VALIDADO', [
            'id_transaccion' => $idTransaccion,
            'id_enlace' => $idEnlace,
            'referencia' => $referencia,
            'resultado' => $resultado,
            'monto' => $monto,
            'es_productiva' => $esProductiva,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Webhook procesado correctamente.',
        ]);
    }
    public function estadoPrueba(WompiService $wompi): JsonResponse
    {
        try {
            $prueba = session('wompi_prueba');

            if (!$prueba || empty($prueba['id_enlace'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Todavía no se ha creado un enlace de prueba.',
                ], 404);
            }

            $respuesta = $wompi->obtenerEnlacePago(
                (int) $prueba['id_enlace']
            );

            return response()->json([
                'success' => true,
                'idEnlace' => $prueba['id_enlace'],
                'referencia' => $prueba['referencia'] ?? null,
                'wompi' => $respuesta,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo consultar el estado del pago en Wompi.',
            ], 502);
        }
    }

    public function resultado(Request $request, WompiService $wompi)
    {
        try {
            $idTransaccion = $request->query('idTransaccion');

            if (!$idTransaccion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wompi no envió el identificador de la transacción.',
                    'parametros' => $request->query(),
                ], 400);
            }

            $transaccion = $wompi->obtenerTransaccion($idTransaccion);

            return response()->json([
                'success' => true,
                'parametros_redirect' => $request->query(),
                'transaccion' => $transaccion,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo consultar la transacción en Wompi.',
            ], 502);
        }
    }
}