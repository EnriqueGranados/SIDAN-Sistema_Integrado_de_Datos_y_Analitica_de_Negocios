<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WompiService
{
    // Obtener el token de autenticación necesario para consumir la API de Wompi
    public function obtenerToken(bool $renovar = false): string
    {
        $cacheKey = 'wompi.access_token';

        // Utilizar el token almacenado en caché mientras siga disponible
        if (!$renovar && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Solicitar un nuevo token de acceso a Wompi
        $response = Http::asForm()
            ->acceptJson()
            ->timeout(30)
            ->post(config('services.wompi.token_url'), [
                'grant_type' => 'client_credentials',
                'audience' => config('services.wompi.audience'),
                'client_id' => config('services.wompi.client_id'),
                'client_secret' => config('services.wompi.client_secret'),
            ]);

        // Verificar que la autenticación con Wompi haya sido exitosa
        if ($response->failed()) {
            throw new RuntimeException(
                'No se pudo autenticar con Wompi. Código HTTP: ' . $response->status()
            );
        }

        // Obtener el token y su tiempo de duración
        $token = $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in', 3600);

        // Verificar que Wompi haya devuelto el token correctamente
        if (!$token) {
            throw new RuntimeException('Wompi no devolvió un access_token.');
        }

        // Guardar el token temporalmente para evitar solicitar uno en cada petición
        Cache::put(
            $cacheKey,
            $token,
            now()->addSeconds(max(60, $expiresIn - 60))
        );

        return $token;
    }

    // Crear un enlace de pago utilizando la API de Wompi
    public function crearEnlacePago(array $datos): array
    {
        // Enviar los datos del pago utilizando el token de autenticación
        $response = Http::withToken($this->obtenerToken())
            ->acceptJson()
            ->timeout(30)
            ->post(
                config('services.wompi.api_url') . '/EnlacePago',
                $datos
            );

        // Renovar el token y repetir la petición si Wompi indica que no está autorizado
        if ($response->status() === 401) {
            Cache::forget('wompi.access_token');

            $response = Http::withToken($this->obtenerToken(true))
                ->acceptJson()
                ->timeout(30)
                ->post(
                    config('services.wompi.api_url') . '/EnlacePago',
                    $datos
                );
        }

        // Verificar que el enlace de pago haya sido creado correctamente
        if ($response->failed()) {
            throw new RuntimeException(
                'Error al crear el enlace de pago en Wompi. Código HTTP: ' . $response->status()
            );
        }

        // Retornar la respuesta de Wompi en forma de arreglo
        return $response->json();
    }
}