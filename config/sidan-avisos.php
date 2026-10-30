<?php
return [
    // Activar únicamente tras configurar SMTP y el trabajador de cola.
    'habilitados' => (bool) env('SIDAN_MAIL_ORDENES', false),
    'aviso_vencimiento_horas' => 48,
    'aviso_regularizacion_horas' => 72,
];
