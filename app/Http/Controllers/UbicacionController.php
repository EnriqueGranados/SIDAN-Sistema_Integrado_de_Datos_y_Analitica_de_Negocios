<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class UbicacionController extends Controller
{
    private function consultar(string $ruta): Response
    {
        return Http::withHeaders([
            'X-CSCAPI-KEY' => config('services.countrystatecity.key'),
        ])
            ->acceptJson()
            ->timeout(10)
            ->get(
                rtrim(config('services.countrystatecity.url'), '/')
                . '/' . $ruta
            );
    }

    private function responder(string $ruta): JsonResponse
    {
        $respuesta = $this->consultar($ruta);

        if ($respuesta->failed()) {
            return response()->json([
                'message' => 'No fue posible cargar las ubicaciones.',
            ], 502);
        }

        return response()->json($respuesta->json());
    }

    public function paises(): JsonResponse
    {
        return $this->responder('countries');
    }

    public function estados(string $pais): JsonResponse
    {
        abort_unless(
            preg_match('/^[A-Za-z]{2}$/', $pais),
            422
        );

        return $this->responder(
            'countries/' . strtoupper($pais) . '/states'
        );
    }

    public function ciudades(string $pais, string $estado): JsonResponse
    {
        abort_unless(
            preg_match('/^[A-Za-z]{2}$/', $pais)
            && preg_match('/^[A-Za-z0-9-]{1,10}$/', $estado),
            422
        );

        return $this->responder(
            'countries/' . strtoupper($pais)
            . '/states/' . rawurlencode($estado)
            . '/cities'
        );
    }
}