<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Imprimir consolidado de pedidos | SIDAN</title>
    <style>
        :root {
            font-family: Arial, Helvetica, sans-serif;
            color: #142238;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0 auto;
            padding: 28px;
            max-width: 1100px;
            font-size: 12px;
            line-height: 1.5;
        }

        h1 {
            font-size: 24px;
            margin: 2px 0 6px;
        }

        h2 {
            font-size: 16px;
            margin: 26px 0 12px;
        }

        .muted {
            color: #5d6b7b;
        }

        .header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            border-bottom: 2px solid #099268;
            padding-bottom: 18px;
        }

        .mark {
            color: #087f5b;
            font-weight: 800;
            font-size: 13px;
            letter-spacing: 1px;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .actions button,
        .actions a {
            font: inherit;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #ccd3db;
            background: #fff;
            text-decoration: none;
            color: #142238;
            cursor: pointer;
        }

        .actions button {
            background: #099268;
            color: #fff;
            border-color: #099268;
            font-weight: 700;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 20px;
            padding: 12px 0;
            border-bottom: 1px solid #d9dfe6;
        }

        .filters span {
            white-space: normal;
        }

        .metrics {
            display: flex;
            gap: 14px;
            margin: 18px 0;
            flex-wrap: wrap;
        }

        .metric {
            flex: 1 1 140px;
            padding: 12px;
            border: 1px solid #d9dfe6;
            border-radius: 8px;
        }

        .metric strong {
            display: block;
            margin-top: 3px;
            font-size: 19px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            font-size: 10px;
            text-transform: uppercase;
            background: #eef3f5;
            color: #4a596b;
            text-align: left;
        }

        th,
        td {
            padding: 9px;
            border-bottom: 1px solid #d9dfe6;
            vertical-align: top;
        }

        .num {
            text-align: right;
            white-space: nowrap;
        }

        .center {
            text-align: center;
        }

        .empty {
            text-align: center;
            padding: 25px;
            color: #64748b;
        }

        .order {
            margin: 12px 0;
            padding: 14px;
            border: 1px solid #d9dfe6;
            border-radius: 8px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .orderhead {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            border-bottom: 1px solid #d9dfe6;
            margin-bottom: 8px;
            padding-bottom: 8px;
        }

        .orderhead strong {
            font-size: 14px;
        }

        .options {
            color: #556577;
            font-size: 11px;
        }

        .footer {
            margin-top: 22px;
            font-size: 10px;
            color: #64748b;
        }

        @page {
            size: A4;
            margin: 14mm;
        }

        @media print {
            body {
                max-width: none;
                padding: 0;
                font-size: 10px;
            }

            .no-print {
                display: none !important;
            }

            h1 {
                font-size: 21px;
            }

            th {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            thead {
                display: table-header-group;
            }
        }
    </style>
</head>

<body>
    @php
        $titulos = [
            'productos' => 'Lista de productos por preparar',
            'variaciones' => 'Lista de personalizaciones',
            'compradores' => 'Lista de pedidos por comprador',
        ];
        $nombreActividad = $actividadSeleccionada ?: 'Todas las actividades';
        $unidades = $productos->sum('unidades');
        $importe = $productos->sum('importe');
    @endphp

    <header class="header">
        <div>
            <div class="mark">SIDAN · CONTROL DE COMPRAS</div>
            <h1>{{ $titulos[$vista] }}</h1>
            <div class="muted">{{ ($filtros['cobertura'] ?? 'pagadas') === 'preparables' ? 'Pagadas y anticipos mínimos cubiertos' : 'Solo órdenes pagadas' }} · Generado el {{ now()->format('d/m/Y H:i') }}</div>
        </div>
        <div class="actions no-print">
            <button type="button" onclick="window.print()">Imprimir / Guardar PDF</button>
            <a href="{{ route('admin.control.consolidado', array_merge($filtros, ['vista' => $vista])) }}">Volver al
                consolidado</a>
        </div>
    </header>

    <div class="filters">
        <span><strong>Actividad:</strong> {{ $nombreActividad }}</span>
        <span><strong>Filtro:</strong> {{ ($filtros['cobertura'] ?? 'pagadas') === 'preparables' ? 'Pagadas y anticipos cubiertos' : 'Solo pagadas' }}</span>
        @if (!empty($filtros['buscar']))
            <span><strong>Búsqueda:</strong> {{ $filtros['buscar'] }}</span>
        @endif
    </div>

    <div class="metrics">
        <div class="metric"><span class="muted">Unidades para preparar</span><strong>{{ number_format($unidades) }}</strong>
        </div>
        <div class="metric"><span class="muted">Productos
                distintos</span><strong>{{ number_format($productos->count()) }}</strong></div>
        <div class="metric"><span class="muted">Importe neto de
                artículos</span><strong>${{ number_format((float) $importe, 2) }}</strong></div>
    </div>

    @if ($vista === 'productos')
        <h2>Cantidades a preparar</h2>
        @if(($filtros['cobertura'] ?? 'pagadas') === 'preparables')
            <p><strong>Modalidad:</strong> pagadas + órdenes con anticipo mínimo acreditado. Los anticipos no equivalen a pago completo.</p>
        @endif
        <table>
            <thead>
                <tr>
                    <th>Actividad</th>
                    <th>Producto</th>
                    <th class="center">Unidades</th>
                    @if(($filtros['cobertura'] ?? 'pagadas') === 'preparables')<th class="center">Pagadas</th><th class="center">Con anticipo</th>@endif
                    <th class="num">Valor neto</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productos as $producto)
                    <tr>
                        <td>{{ $producto->actividad_nombre }}</td>
                        <td><strong>{{ $producto->nombre_concepto }}</strong></td>
                        <td class="center">{{ $producto->unidades }}</td>
                        @if(($filtros['cobertura'] ?? 'pagadas') === 'preparables')
                        <td class="center">{{ $producto->unidades_pagadas }}</td><td class="center">{{ $producto->unidades_anticipo }}</td>
                        @endif
                        <td class="num">${{ number_format((float) $producto->importe, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($filtros['cobertura'] ?? 'pagadas') === 'preparables' ? 6 : 4 }}" class="empty">No hay artículos que cumplan los filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif ($vista === 'variaciones')
        <h2>Artículos y combinaciones de personalización</h2>
        <table>
            <thead>
                <tr>
                    <th>Actividad</th>
                    <th>Artículo</th>
                    <th>Personalización</th>
                    <th class="center">Unidades</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($variaciones as $variacion)
                    @php
                        $opciones = is_string($variacion->configuracion)
                            ? json_decode($variacion->configuracion, true)
                            : $variacion->configuracion;
                        $opciones = is_array($opciones) ? $opciones : [];
                    @endphp
                    <tr>
                        <td>{{ $variacion->actividad_nombre }}</td>
                        <td><strong>{{ $variacion->nombre_concepto }}</strong></td>
                        <td>
                            @forelse ($opciones as $clave => $valor)
                                <div>{{ ucfirst(str_replace('_', ' ', $clave)) }}:
                                    {{ is_scalar($valor) ? $valor : json_encode($valor, JSON_UNESCAPED_UNICODE) }}
                                </div>
                            @empty
                                <span class="muted">Sin personalización</span>
                            @endforelse
                        </td>
                        <td class="center"><strong>{{ $variacion->unidades }}</strong>
                        @if(($filtros['cobertura'] ?? 'pagadas') === 'preparables')<div class="muted">{{ $variacion->unidades_anticipo }} con anticipo</div>@endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty">No hay personalizaciones para imprimir.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <h2>Pedidos individuales ({{ $ordenes->count() }} órdenes)</h2>
        @forelse ($ordenes as $orden)
            @php
                $lineas = $pedidosPorOrden->get($orden->id_orden, collect());
            @endphp
            <section class="order">
                <div class="orderhead">
                    <div>
                        <strong>Orden #{{ $orden->id_orden }} ·
                            {{ $orden->nombre_participante ?: $orden->correo_contacto }}</strong>
                        <div class="muted">{{ $orden->correo_contacto }} · {{ $orden->actividad_nombre }}</div>
                        <div><strong>{{ $orden->estado_orden === 'pagada' ? 'Pago completo' : 'Anticipo mínimo cubierto (saldo pendiente)' }}</strong></div>
                    </div>
                    <span
                        class="muted">{{ \Illuminate\Support\Carbon::parse($orden->fecha_orden)->format('d/m/Y H:i') }}</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Artículo</th>
                            <th>Personalización</th>
                            <th class="center">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lineas as $linea)
                            @php
                                $opciones = is_string($linea->configuracion)
                                    ? json_decode($linea->configuracion, true)
                                    : $linea->configuracion;
                                $opciones = is_array($opciones) ? $opciones : [];
                            @endphp
                            <tr>
                                <td><strong>{{ $linea->nombre_concepto }}</strong></td>
                                <td class="options">
                                    @forelse ($opciones as $clave => $valor)
                                        <div>{{ ucfirst(str_replace('_', ' ', $clave)) }}:
                                            {{ is_scalar($valor) ? $valor : json_encode($valor, JSON_UNESCAPED_UNICODE) }}
                                        </div>
                                    @empty
                                        Sin personalización
                                    @endforelse
                                </td>
                                <td class="center"><strong>{{ $linea->cantidad }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
            @empty
                <p class="empty">No hay órdenes para imprimir con estos filtros.</p>
            @endforelse
        @endif

        <p class="footer">Documento generado desde SIDAN. Los importes descuentan promociones aplicadas a los artículos. Las
            comprobantes sin confirmar no están incluidos. Los valores de las órdenes con anticipo representan el valor de los productos a preparar, no ingresos realizados.</p>
    </body>

    </html>
