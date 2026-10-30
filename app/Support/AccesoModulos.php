<?php

namespace App\Support;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Str;

class AccesoModulos
{
    public static function catalogo(): array
    {
        return config('modulos.catalogo', []);
    }

    public static function asignables(): array
    {
        return array_diff_key(
            static::catalogo(),
            ['roles' => true]
        );
    }

    public static function esSuperadmin(?User $usuario): bool
    {
        return $usuario
            && $usuario->estado_activo
            && !$usuario->eliminado
            && $usuario->rol?->nombre === 'superadmin';
    }

    public static function modulosDelRol(?Rol $rol): array
    {
        if (!$rol) {
            return [];
        }

        if ($rol->nombre === 'superadmin') {
            return array_keys(static::catalogo());
        }

        if ($rol->nombre === 'usuario') {
            return [];
        }

        return array_values(array_intersect(
            array_keys(static::asignables()),
            is_array($rol->modulos) ? $rol->modulos : []
        ));
    }

    public static function permite(?User $usuario, string $modulo): bool
    {
        if (
            !$usuario
            || !$usuario->estado_activo
            || $usuario->eliminado
            || !array_key_exists($modulo, static::catalogo())
        ) {
            return false;
        }

        return in_array(
            $modulo,
            static::modulosDelRol($usuario->rol),
            true
        );
    }

    public static function permiteRuta(?User $usuario, ?string $ruta): bool
    {
        if (!$ruta) {
            return false;
        }

        foreach (config('modulos.rutas', []) as $patron => $modulos) {
            if (!Str::is($patron, $ruta)) {
                continue;
            }

            foreach ($modulos as $modulo) {
                if (static::permite($usuario, $modulo)) {
                    return true;
                }
            }

            return false;
        }

        // Una ruta administrativa nueva debe declararse en el catálogo.
        return false;
    }

    public static function inicio(?User $usuario): string
    {
        foreach (static::catalogo() as $clave => $modulo) {
            if (static::permite($usuario, $clave)) {
                return $modulo['ruta'];
            }
        }

        return 'welcome';
    }

    public static function puedeAsignar(?User $usuario, Rol $rol): bool
    {
        if (static::esSuperadmin($usuario)) {
            return true;
        }

        if (!static::permite($usuario, 'usuarios')) {
            return false;
        }

        if (in_array($rol->nombre, ['superadmin', 'admin'], true)) {
            return false;
        }

        $modulosDisponibles = static::modulosDelRol($usuario->rol);
        $modulosSolicitados = static::modulosDelRol($rol);

        return empty(array_diff(
            $modulosSolicitados,
            $modulosDisponibles
        ));
    }

    public static function puedeGestionar(
        ?User $operador,
        User $destinatario
    ): bool {
        if (
            !$operador
            || (string) $operador->getKey() === (string) $destinatario->getKey()
        ) {
            return false;
        }

        if (static::esSuperadmin($operador)) {
            return true;
        }

        if (!$destinatario->rol) {
            return false;
        }

        return static::puedeAsignar($operador, $destinatario->rol);
    }
}