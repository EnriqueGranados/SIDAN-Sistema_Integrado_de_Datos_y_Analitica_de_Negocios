<?php

return [
    'transferencia' => [
        'banco' => env('PAGOS_BANCO'),
        'titular' => env('PAGOS_TITULAR'),
        'cuenta' => env('PAGOS_CUENTA'),
        'tipo' => env('PAGOS_TIPO_CUENTA', 'Ahorro'),
    ],
];