@php
    // Solo presentación: los importes provienen de RecibosOrdenController.
    $esReciboAbono = (bool) $abono;
    $tipoTitulo = $esReciboAbono ? 'Comprobante de abono' : 'Resumen de orden';
    $etiquetaDocumento = $esReciboAbono ? 'PAGO ACREDITADO' : 'RESUMEN DE COMPRA';
    $metodoPago = match ($pago?->proveedor) {
        'efectivo' => 'Efectivo',
        'transferencia' => 'Transferencia bancaria',
        'wompi' => 'Wompi / tarjeta',
        'promocion' => 'Promoción',
        default => ucfirst($pago?->proveedor ?? 'No registrado'),
    };
    $estadoOrden = match ($orden->estado) {
        'pendiente_pago' => 'Pendiente de pago',
        'pagada' => 'Pagada',
        'vencida' => 'Vencida',
        'finalizada' => 'Finalizada',
        'cancelada' => 'Cancelada',
        'revision_cupo' => 'Revisión de cupo',
        default => ucfirst(str_replace('_', ' ', $orden->estado)),
    };
    $fechaEmision = now()->timezone('America/El_Salvador')->format('d/m/Y h:i A');
    $fechaAbono = $abono ? ($abono->revisado_en ?? $abono->updated_at)?->copy()->timezone('America/El_Salvador')->format('d/m/Y h:i A') : null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>SIDAN - {{ $tipoTitulo }} #{{ $orden->id_orden }}</title>
    <style>
        @page { margin: 22px 28px; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 17px 22px;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.55;
            color: #334155;
            background: #f6f8fb;
        }
        .brand { width: auto; margin: 0 auto 20px; border-collapse: collapse; }
        .brand td { border: 0; vertical-align: middle; }
        .brand-symbol {
            width: 37px;
            height: 37px;
            background: #0f172a;
            color: #ffffff;
            font-family: DejaVu Sans, sans-serif;
            font-size: 17px;
            font-weight: bold;
            text-align: center;
            border-radius: 11px;
        }
        .brand-name { padding-left: 10px; font-size: 21px; font-weight: bold; color: #0f172a; }
        .sheet {
            padding: 27px 30px 24px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
        }
        .kicker { color: #16a34a; font-size: 9px; font-weight: bold; letter-spacing: 2px; margin-bottom: 8px; }
        h1 { font-size: 23px; line-height: 1.25; color: #0f172a; margin: 0 0 8px; }
        .intro { margin: 0 0 21px; color: #64748b; font-size: 10px; }
        .divider { height: 1px; background: #e2e8f0; margin: 20px 0; }
        .panel { border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 11px; padding: 12px 16px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table td { width: 50%; padding: 7px 12px 7px 0; vertical-align: top; overflow-wrap: break-word; }
        .data-label { display: block; color: #64748b; font-size: 8px; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.6px; }
        .data-value { display: block; color: #0f172a; font-weight: bold; font-size: 10px; word-wrap: break-word; }
        .section-title { margin: 22px 0 9px; color: #0f172a; font-weight: bold; font-size: 12px; }
        .payment-panel { background: #ecfdf5; border: 1px solid #bbf7d0; padding: 13px 16px; border-radius: 10px; margin-top: 16px; }
        .payment-title { font-weight: bold; color: #166534; font-size: 10px; margin: 0 0 5px; }
        .payment-number { font-size: 19px; font-weight: bold; color: #166534; }
        .detail-table { width: 100%; border-collapse: collapse; }
        .detail-table thead th { background: #f1f5f9; padding: 9px 10px; font-size: 9px; color: #475569; text-align: left; }
        .detail-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; word-wrap: break-word; }
        .detail-table tr { page-break-inside: avoid; }
        .align-right { text-align: right !important; }
        .align-center { text-align: center !important; }
        .summary { margin-top: 18px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; page-break-inside: avoid; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 6px 0; }
        .summary-table .key { color: #64748b; }
        .summary-table .value { color: #0f172a; font-weight: bold; text-align: right; }
        .summary-table .rule td { border-top: 1px solid #e2e8f0; padding-top: 12px; }
        .summary-table .grand td { font-size: 14px; color: #0f172a; font-weight: bold; }
        .summary-table .paid { color: #15803d; }
        .summary-table .pending { color: #b45309; }
        .note { padding: 11px 13px; margin-top: 14px; border: 1px solid #fde68a; background: #fffbeb; color: #92400e; font-size: 9px; border-radius: 9px; page-break-inside: avoid; }
        .footer { margin-top: 18px; color: #64748b; font-size: 9px; line-height: 1.6; text-align: center; }
        .footer-legal { margin: 10px 0 0; color: #94a3b8; font-size: 8px; }
    </style>
</head>
<body>
    <table class="brand" role="presentation">
        <tr>
            <td class="brand-symbol">S</td>
            <td class="brand-name">SIDAN</td>
        </tr>
    </table>

    <div class="sheet">
        <div class="kicker">{{ $etiquetaDocumento }}</div>
        <h1>{{ $tipoTitulo }}</h1>
        <p class="intro">Orden #{{ $orden->id_orden }} · Documento generado por el Sistema Integrado de Datos y Analítica de Negocios.</p>

        <div class="panel">
            <table class="data-table" role="presentation">
                <tr>
                    <td><span class="data-label">Actividad</span><span class="data-value">{{ $orden->actividad?->nombre ?? 'No disponible' }}</span></td>
                    <td><span class="data-label">Estado de la orden</span><span class="data-value">{{ $estadoOrden }}</span></td>
                </tr>
                <tr>
                    <td><span class="data-label">Comprador / correo</span><span class="data-value">{{ $orden->correo_contacto ?? 'No disponible' }}</span></td>
                    <td><span class="data-label">Método de pago</span><span class="data-value">{{ $metodoPago }}</span></td>
                </tr>
                <tr>
                    <td><span class="data-label">Número de orden</span><span class="data-value">#{{ $orden->id_orden }}</span></td>
                    <td><span class="data-label">Fecha de emisión</span><span class="data-value">{{ $fechaEmision }} (El Salvador)</span></td>
                </tr>
            </table>
        </div>

        @if ($abono)
            <div class="payment-panel">
                <p class="payment-title">Abono #{{ $abono->id_abono }} confirmado</p>
                <table class="summary-table" role="presentation">
                    <tr>
                        <td>Fecha de acreditación: {{ $fechaAbono ?? 'No disponible' }}</td>
                        <td class="align-right payment-number">${{ number_format((float) $abono->monto_recibido, 2) }}</td>
                    </tr>
                </table>
            </div>
        @endif

        <div class="section-title">Conceptos de la orden</div>
        <table class="detail-table">
            <thead>
                <tr>
                    <th>Producto o servicio</th>
                    <th class="align-center" style="width:65px">Cantidad</th>
                    <th class="align-right" style="width:115px">Importe neto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orden->detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->nombre_concepto }}</td>
                        <td class="align-center">{{ $detalle->cantidad }}</td>
                        <td class="align-right">${{ number_format((float) $detalle->subtotal - (float) $detalle->descuento, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <table class="summary-table" role="presentation">
                <tr class="grand">
                    <td>Total de la orden</td>
                    <td class="align-right">${{ number_format($total / 100, 2) }}</td>
                </tr>
                <tr class="rule">
                    <td class="key">Total acreditado hasta esta emisión</td>
                    <td class="value paid">${{ number_format($acreditado / 100, 2) }}</td>
                </tr>
                <tr>
                    <td class="key">{{ $orden->estado === 'cancelada' ? 'Saldo exigible (orden cancelada)' : 'Saldo pendiente' }}</td>
                    <td class="value {{ $orden->estado === 'cancelada' ? '' : ($saldo > 0 ? 'pending' : 'paid') }}">${{ number_format($saldo / 100, 2) }}</td>
                </tr>
            </table>
        </div>

        @if ($orden->estado === 'cancelada')
            <div class="note">
                <strong>Orden cancelada.</strong> Se conserva su total histórico de compra, pero no hay saldo exigible ni venta confirmada. Esta constancia no acredita un pago.
            </div>
        @elseif ($saldo > 0)
            <div class="note">
                <strong>Saldo pendiente de pago.</strong> Este documento no acredita que la orden esté pagada completamente. Solo se consideran los pagos confirmados.
            </div>
        @endif
        <div class="divider"></div>
        <div class="footer">
            <strong>SIDAN</strong> · Sistema Integrado de Datos y Analítica de Negocios<br>
            Generado a partir de operaciones registradas en el sistema.
            <p class="footer-legal">Comprobante interno no fiscal. Este documento no es una factura tributaria electrónica (DTE) ni sustituye comprobantes fiscales autorizados.</p>
        </div>
    </div>
</body>
</html>
