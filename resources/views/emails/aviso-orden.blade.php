@php
    // Diseño exclusivo del correo. La programación, estados y adjuntos no se modifican.
    $mensajes = [
        'creada' => 'Se registró correctamente tu orden. Revisá el detalle y los próximos pasos para pagar.',
        'comprobante_recibido' => 'Recibimos tu comprobante de transferencia. El pago todavía no está acreditado y debe ser validado por administración.',
        'abono' => 'Se confirmó un pago de tu orden. Solo el dinero acreditado cuenta como abonado; los comprobantes pendientes todavía requieren revisión.',
        'pagada' => 'Tu orden quedó pagada completamente. Consultá los detalles de los productos y su entrega en SIDAN.',
        'vencimiento_proximo' => 'Tu orden tiene un saldo pendiente y se aproxima la fecha límite para pagarlo.',
        'vencida' => 'La orden venció. Si tenés dinero acreditado, podrías regularizar presencialmente durante el plazo permitido. No enviés nuevas transferencias sin autorización.',
        'regularizacion_por_finalizar' => 'Tu plazo de 30 días para regularizar presencialmente una orden vencida está por terminar.',
        'finalizada' => 'Tu orden ha sido finalizada y ya no admite nuevos abonos. Los pagos anteriormente acreditados permanecen registrados.',
    ];
    $categorias = [
        'creada' => 'NUEVA ORDEN',
        'comprobante_recibido' => 'COMPROBANTE RECIBIDO',
        'abono' => 'PAGO ACREDITADO',
        'pagada' => 'PAGO COMPLETADO',
        'vencimiento_proximo' => 'RECORDATORIO DE PAGO',
        'vencida' => 'ORDEN VENCIDA',
        'regularizacion_por_finalizar' => 'PLAZO POR FINALIZAR',
        'finalizada' => 'ORDEN FINALIZADA',
    ];
    $estados = [
        'pendiente_pago' => 'Pendiente de pago',
        'pagada' => 'Pagada',
        'vencida' => 'Vencida',
        'finalizada' => 'Finalizada',
        'cancelada' => 'Cancelada',
    ];
    $etiqueta = $categorias[$evento] ?? 'ACTUALIZACIÓN DE ORDEN';
    $descripcion = $mensajes[$evento] ?? 'Hay novedades sobre tu orden.';
    $estadoOrden = $estados[$orden->estado] ?? ucfirst(str_replace('_', ' ', $orden->estado));
    $urlAcceso = route('login');
    $esAdvertencia = in_array($evento, ['comprobante_recibido', 'vencimiento_proximo', 'vencida', 'regularizacion_por_finalizar', 'finalizada'], true);
    $esUrgente = in_array($evento, ['vencida', 'regularizacion_por_finalizar', 'finalizada'], true);
    $colorEtiqueta = $esUrgente ? '#b45309' : ($esAdvertencia ? '#a16207' : '#16a34a');
    $fondoNota = $esAdvertencia ? '#fffaf0' : '#f8fafc';
    $bordeNota = $esAdvertencia ? '#fde68a' : '#e2e8f0';
    $abonoActual = $evento === 'abono' && !str_starts_with($referencia, 'wompi-')
        ? $orden->pago?->abonos?->firstWhere('id_abono', (int) $referencia)
        : null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tituloEvento }} - SIDAN</title>
</head>
<body style="margin:0;padding:0;background-color:#f6f8fb;color:#334155;font-family:Arial,Helvetica,sans-serif;">
    {{-- Texto preliminar que algunas aplicaciones de correo muestran en la bandeja. --}}
    <div style="display:none;font-size:1px;color:#f6f8fb;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
        {{ $tituloEvento }} · Orden #{{ $orden->id_orden }} en SIDAN.
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background-color:#f6f8fb;">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;">
                    {{-- Identidad visual similar al correo de recuperación de contraseña. --}}
                    <tr>
                        <td align="center" style="padding-bottom:24px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center" valign="middle" style="width:42px;height:42px;border-radius:12px;background-color:#0f172a;color:#ffffff;font-size:16px;font-weight:800;">
                                        S
                                    </td>
                                    <td style="padding-left:10px;font-size:22px;font-weight:800;color:#0f172a;">
                                        SIDAN
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff;border:1px solid #e2e8f0;border-radius:24px;padding:40px;">
                            <p style="margin:0 0 12px;color:{{ $colorEtiqueta }};font-size:12px;font-weight:800;letter-spacing:2px;text-transform:uppercase;">
                                {{ $etiqueta }}
                            </p>
                            <h1 style="margin:0;color:#0f172a;font-size:28px;line-height:36px;font-weight:800;">
                                {{ $tituloEvento }}
                            </h1>
                            <p style="margin:24px 0 0;color:#475569;font-size:15px;line-height:24px;">Hola,</p>
                            <p style="margin:12px 0 0;color:#475569;font-size:15px;line-height:24px;">
                                {{ $descripcion }}
                            </p>

                            {{-- Detalle de la orden sin alterar montos ni estados. --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;margin-top:26px;background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                                <tr>
                                    <td style="padding:20px;">
                                        <p style="margin:0 0 15px;color:#0f172a;font-size:17px;font-weight:800;">Orden #{{ $orden->id_orden }}</p>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;font-size:14px;line-height:21px;">
                                            <tr>
                                                <td style="padding:4px 10px 4px 0;color:#64748b;vertical-align:top;">Actividad</td>
                                                <td align="right" style="padding:4px 0;color:#334155;font-weight:600;vertical-align:top;">{{ $orden->actividad?->nombre ?? 'Actividad no disponible' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:4px 10px 4px 0;color:#64748b;">Estado</td>
                                                <td align="right" style="padding:4px 0;color:#334155;font-weight:600;">{{ $estadoOrden }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding:10px 0 0;border-bottom:1px solid #e2e8f0;"></td>
                                            </tr>
                                            <tr>
                                                <td style="padding:15px 10px 0 0;color:#334155;font-weight:700;">Total de la orden</td>
                                                <td align="right" style="padding:15px 0 0;color:#0f172a;font-size:20px;font-weight:800;">${{ number_format((float) $orden->total, 2) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @if($evento === 'abono')
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;margin-top:16px;background-color:#ecfdf5;border:1px solid #bbf7d0;border-radius:12px;">
                                    <tr>
                                        <td style="padding:15px 17px;color:#166534;font-size:14px;line-height:22px;">
                                            @if(str_starts_with($referencia, 'wompi-'))
                                                <strong>Pago confirmado:</strong> el proveedor confirmó el pago electrónico.
                                            @elseif($abonoActual)
                                                <strong>Abono #{{ $abonoActual->id_abono }} confirmado:</strong>
                                                ${{ number_format((float) $abonoActual->monto_recibido, 2) }}.
                                            @else
                                                <strong>Pago acreditado:</strong> consultá el detalle actualizado en SIDAN.
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @if($esAdvertencia)
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;margin-top:16px;background-color:{{ $fondoNota }};border:1px solid {{ $bordeNota }};border-radius:12px;">
                                    <tr>
                                        <td style="padding:15px 17px;color:#92400e;font-size:13px;line-height:21px;">
                                            @if($evento === 'comprobante_recibido')
                                                El envío del comprobante no significa que el dinero ya esté acreditado. Esperá la validación administrativa.
                                            @elseif($evento === 'vencimiento_proximo')
                                                Revisá el saldo pendiente de tu orden para evitar que venza el plazo de pago.
                                            @elseif($evento === 'vencida')
                                                Si la orden tiene abonos confirmados, consultá en SIDAN el plazo y las indicaciones para continuar pagando presencialmente.
                                            @elseif($evento === 'regularizacion_por_finalizar')
                                                La regularización de órdenes vencidas se realiza de forma presencial, dentro del plazo permitido.
                                            @elseif($evento === 'finalizada')
                                                Esta orden ya no admite nuevos pagos; sus abonos confirmados continúan registrados en el historial.
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <p style="margin:24px 0 0;color:#475569;font-size:14px;line-height:23px;">
                                Iniciá sesión para consultar tu historial, el estado de tus productos y los comprobantes disponibles en <strong>Mis órdenes</strong>.
                            </p>

                            {{-- Botón principal: la ruta ya existe y es nombrada 'login'. --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;margin-top:28px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $urlAcceso }}" style="display:inline-block;background-color:#22c55e;color:#ffffff;text-decoration:none;font-size:14px;font-weight:800;padding:14px 28px;border-radius:12px;">
                                            Iniciar sesión
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:26px 0 0;color:#64748b;font-size:12px;line-height:19px;">
                                Los documentos descargables son comprobantes internos de SIDAN, no facturas tributarias electrónicas (DTE).
                            </p>
                            <div style="margin-top:26px;padding-top:22px;border-top:1px solid #e2e8f0;">
                                <p style="margin:0;color:#94a3b8;font-size:12px;line-height:19px;">
                                    Si el botón no funciona, copiá y pegá este enlace en tu navegador:
                                </p>
                                <p style="margin:8px 0 0;font-size:12px;line-height:19px;word-break:break-all;overflow-wrap:anywhere;">
                                    <a href="{{ $urlAcceso }}" style="color:#16a34a;text-decoration:none;">{{ $urlAcceso }}</a>
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:24px 16px 0;color:#94a3b8;font-size:12px;line-height:19px;">
                            <p style="margin:0;">© {{ date('Y') }} SIDAN. Todos los derechos reservados.</p>
                            <p style="margin:6px 0 0;">Sistema Integrado de Datos y Analítica de Negocios</p>
                            <p style="margin:12px 0 0;">Este es un correo automático relacionado con tu orden.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
