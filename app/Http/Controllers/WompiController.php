<?php

namespace App\Http\Controllers;

use App\Services\WompiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Http\Request;
use App\Services\PagoService;

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


    public function webhook(
        Request $request,
        PagoService $pagos
    ): JsonResponse {
        // Leer el contenido original sin modificarlo.
        $body = $request->getContent();
        $hashRecibido = $request->header('wompi_hash');

        $secret = config('services.wompi.client_secret');

        if (!$secret) {
            \Log::error('Wompi: API Secret no configurado.');

            return response()->json([
                'success' => false,
            ], 500);
        }

        if (!$hashRecibido) {
            return response()->json([
                'success' => false,
                'message' => 'Firma ausente.',
            ], 401);
        }

        // Calcular la firma del cuerpo recibido.
        $hashCalculado = hash_hmac(
            'sha256',
            $body,
            $secret
        );

        // Rechazar notificaciones sin firma válida.
        if (
            !hash_equals(
                strtolower($hashCalculado),
                strtolower($hashRecibido)
            )
        ) {
            \Log::warning('Webhook Wompi con firma incorrecta.');

            return response()->json([
                'success' => false,
                'message' => 'Firma inválida.',
            ], 401);
        }

        $datos = json_decode($body, true);

        if (!is_array($datos)) {
            return response()->json([
                'success' => false,
                'message' => 'JSON inválido.',
            ], 400);
        }

        try {
            // La firma fue validada.
            // Procesamos la transacción en PostgreSQL.
            $pagos->confirmarWebhook($datos);

            return response()->json([
                'success' => true,
            ], 200);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No fue posible conciliar el pago.',
            ], 422);
        }
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