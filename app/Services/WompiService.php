<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WompiService
{
    public function obtenerToken(bool $renovar = false): string
    {
        $cacheKey = 'wompi.access_token';

        if (!$renovar && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = Http::asForm()
            ->acceptJson()
            ->timeout(30)
            ->post(config('services.wompi.token_url'), [
                'grant_type' => 'client_credentials',
                'audience' => config('services.wompi.audience'),
                'client_id' => config('services.wompi.client_id'),
                'client_secret' => config('services.wompi.client_secret'),
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'No se pudo autenticar con Wompi. Código HTTP: '.$response->status()
            );
        }

        $token = $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in', 3600);

        if (!$token) {
            throw new RuntimeException('Wompi no devolvió un access_token.');
        }

        Cache::put(
            $cacheKey,
            $token,
            now()->addSeconds(max(60, $expiresIn - 60))
        );

        return $token;
    }

    public function crearEnlacePago(array $datos): array
    {
        $response = Http::withToken($this->obtenerToken())
            ->acceptJson()
            ->timeout(30)
            ->post(
                config('services.wompi.api_url').'/EnlacePago',
                $datos
            );

        if ($response->status() === 401) {
            Cache::forget('wompi.access_token');

            $response = Http::withToken($this->obtenerToken(true))
                ->acceptJson()
                ->timeout(30)
                ->post(
                    config('services.wompi.api_url').'/EnlacePago',
                    $datos
                );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                'Error al crear el enlace de pago en Wompi. Código HTTP: '.$response->status()
            );
        }

        return $response->json();
    }
}