<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CompraActividadController;
use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Orden;
use App\Models\User;
use App\Services\CuentaAutomaticaService;
use App\Services\GestionOrdenService;
use App\Services\DisponibilidadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VentaPresencialController extends Controller
{
    public function index(Request $request)
    {
        $ahora = now('UTC');
        $actividades = Actividad::query()
            ->where(function ($q) use ($ahora) {
                $q->where('realizacion_hasta', '>=', $ahora)
                  ->orWhere(function ($sinFinal) use ($ahora) {
                      $sinFinal->whereNull('realizacion_hasta')
                          ->where(function ($inicio) use ($ahora) {
                              $inicio->whereNull('realizacion_desde')
                                  ->orWhere('realizacion_desde', '>=', $ahora);
                          });
                  });
            })
            ->where('estado_publicacion', 'publicada')
            ->where('estado_operativo', 'normal')
            ->whereHas('items', fn ($q) => $q->where('activo', true))
            // Datos de presentación para las tarjetas, igual que en Administrar Welcome.
            ->with(['categoria', 'portada'])
            ->orderBy('nombre')
            ->get([
                'id_actividad', 'nombre', 'slug', 'id_categoria',
                'visible_desde', 'visible_hasta', 'realizacion_desde', 'realizacion_hasta',
            ]);

        $actividad = null;
        $items = collect();
        $campos = [];
        $stockItems = [];
        $stockVariantes = [];
        $reglasPrecio = [];
        $busqueda = trim((string)$request->query('buscar_usuario', ''));
        $usuarios = collect();

        if ($request->filled('actividad')) {
            $actividad = $actividades->firstWhere('id_actividad', (int)$request->query('actividad'));
            if (!$actividad) abort(404, 'La actividad no está disponible para ventas.');
            $items = $actividad->items()->where('activo', true)
                ->with(['medios', 'variantes' => fn($q) => $q->where('activo', true)])
                ->orderBy('orden')->get();
            $disponibilidad = app(DisponibilidadService::class);
            foreach ($items as $item) {
                $stockItems[(int)$item->id_item_actividad] = $disponibilidad->stockDisponibleItem((int)$item->id_item_actividad);
                foreach ($item->variantes as $variante) {
                    $stockVariantes[(int)$variante->id_variante_item] = $disponibilidad->stockDisponibleVariante((int)$variante->id_variante_item);
                }
            }
            // Reutilizar la configuración de opciones del catálogo público.
            $contenido = DB::connection('mongodb')->table('activity_content')
                ->where('id_actividad_pg', (int)$actividad->id_actividad)->first();
            $config = $contenido->purchase_fields ?? [];
            if (is_string($config)) $config = json_decode($config, true);
            foreach (is_array($config) ? $config : [] as $conf) {
                if (is_array($conf) && !empty($conf['enabled']) && !empty($conf['id_item_pg'])) {
                    $campos[(int)$conf['id_item_pg']] = $conf['fields'] ?? [];
                }
            }
            // Misma fuente de reglas de precio que usa el catálogo público.
            $precios = $contenido->pricing_configuration ?? [];
            if (is_string($precios)) $precios = json_decode($precios, true);
            foreach (is_array($precios) ? $precios : [] as $conf) {
                if (!is_array($conf) || empty($conf['enabled']) || empty($conf['id_item_pg'])) continue;
                $id = (int)$conf['id_item_pg'];
                $definiciones = collect($campos[$id] ?? [])->filter(fn($campo) => is_array($campo) && !empty($campo['key']))->keyBy('key');
                $habilitados = $conf['pricing_fields'] ?? [];
                $reglasPrecio[$id] = collect($conf['rules'] ?? [])->filter(function ($regla) use ($definiciones, $habilitados) {
                    if (!is_array($regla)) return false;
                    $clave = (string)($regla['field_key'] ?? '');
                    $def = $definiciones->get($clave);
                    return in_array($clave, $habilitados, true)
                        && ($def['type'] ?? '') === 'lista'
                        && in_array((string)($regla['option'] ?? ''), array_map('strval', $def['options'] ?? []), true);
                })->values()->all();
            }
        }

        if (mb_strlen($busqueda) >= 2) {
            $usuarios = User::with('informacion_personal')
                ->where('estado_activo', true)->where('eliminado', false)
                ->whereHas('rol', fn ($q) => $q->where('nombre', 'usuario'))
                ->where(function ($q) use ($busqueda) {
                    $q->where('correo', 'ilike', '%'.$busqueda.'%')
                        ->orWhereHas('informacion_personal', function ($q) use ($busqueda) {
                            $q->where('nombres', 'ilike', '%'.$busqueda.'%')
                                ->orWhere('apellidos', 'ilike', '%'.$busqueda.'%');
                        });
                })->orderBy('id_usuario')->limit(20)->get();
        }

        return view('admin.ventas-presenciales.index', compact('actividades','actividad','items','campos','stockItems','stockVariantes','reglasPrecio','usuarios','busqueda'));
    }

    public function store(Request $request, CompraActividadController $compras,
                          GestionOrdenService $gestion, CuentaAutomaticaService $cuentas)
    {
        $datos = $request->validate([
            'id_actividad' => ['required', 'integer', 'exists:tbl_actividades,id_actividad'],
            'comprador_tipo' => ['required', 'in:existente,nuevo'],
            'id_usuario' => ['required_if:comprador_tipo,existente','nullable','integer'],
            'nombre' => ['required_if:comprador_tipo,nuevo','nullable','string','max:200','regex:/^\S+\s+\S+/u'],
            'correo' => ['required_if:comprador_tipo,nuevo','nullable','email','max:150'],
            'telefono' => ['nullable','string','max:25'],
            'carrito' => ['required','string','max:65535'],
            'metodo' => ['required','in:efectivo,transferencia'],
            'modalidad' => ['required','in:completo,anticipo'],
            'monto_recibido' => ['required','numeric','decimal:0,2','min:0'],
            'transferencia_verificada' => ['nullable','boolean'],
            'comprobante' => ['nullable','required_if:metodo,transferencia', 'file','image','mimes:jpg,jpeg,png,webp','max:5120'],
        ], [
            'id_usuario.required_if' => 'Seleccioná al comprador antes de crear la orden.',
            'id_usuario.integer' => 'Seleccioná un comprador válido.',
            'comprador_tipo.required' => 'Indicá si utilizarás una cuenta existente o una nueva.',
            'nombre.required_if' => 'Ingresá el nombre completo del nuevo comprador.',
            'correo.required_if' => 'Ingresá el correo electrónico del nuevo comprador.',
        ]);
        $selecciones = json_decode($datos['carrito'], true);
        if (!is_array($selecciones) || !array_is_list($selecciones) || count($selecciones) === 0 || count($selecciones) > 50) {
            throw ValidationException::withMessages(['carrito' => 'Agregá al menos un producto válido al carrito (máximo 50 configuraciones).']);
        }
        $selecciones = \Illuminate\Support\Facades\Validator::make(
            ['selecciones' => $selecciones],
            [
                'selecciones' => ['required','array','min:1','max:50'],
                'selecciones.*.id_item' => ['required','integer','min:1'],
                'selecciones.*.id_variante' => ['nullable','integer','min:1'],
                'selecciones.*.cantidad' => ['required','integer','min:1','max:1000'],
                'selecciones.*.campos' => ['nullable','array','max:20'],
            ]
        )->validate()['selecciones'];

        $actividad = Actividad::whereKey($datos['id_actividad'])->firstOrFail();
        $ahora = now('UTC');
        if (($actividad->realizacion_hasta && $actividad->realizacion_hasta->lt($ahora))
            || (!$actividad->realizacion_hasta && $actividad->realizacion_desde && $actividad->realizacion_desde->lt($ahora))) {
            throw ValidationException::withMessages(['id_actividad' => 'La actividad ya finalizó.']);
        }
        if ($actividad->estado_publicacion !== 'publicada' || $actividad->estado_operativo !== 'normal') {
            throw ValidationException::withMessages(['id_actividad'=>'La actividad no admite ventas.']);
        }
        $recibido = (int)round((float)$datos['monto_recibido'] * 100);
        $verificada = $datos['metodo']==='efectivo' || $request->boolean('transferencia_verificada');
        $archivo = null;
        $nuevo = null;
        $ordenId = null;
        try {
            if ($datos['metodo']==='transferencia') {
                $archivo = $request->file('comprobante')?->store('comprobantes-transferencias','local');
                if (!$archivo) throw ValidationException::withMessages(['comprobante'=>'Adjuntá el comprobante de la transferencia.']);
            }
            // Una sola transacción para cuenta, orden, pago y acreditación PostgreSQL.
            $ordenId = DB::transaction(function () use ($request,$datos,$actividad,$selecciones,
                $compras,$gestion,$cuentas,$recibido,$verificada,$archivo,&$nuevo) {
                if ($datos['comprador_tipo']==='existente') {
                    $usuario = User::with('rol')->whereKey($datos['id_usuario'])->lockForUpdate()->first();
                    if (!$usuario || !$usuario->estado_activo || $usuario->eliminado || $usuario->rol?->nombre!=='usuario') {
                        throw ValidationException::withMessages(['id_usuario'=>'Seleccioná una cuenta de comprador válida.']);
                    }
                } else {
                    $nuevo = $cuentas->crear($datos['nombre'],$datos['correo'],$datos['telefono'] ?? null);
                    $usuario = $nuevo;
                }
                // No se confía en los precios del navegador: el controlador normal
                // comprueba tarifas, stock, promociones, variantes y campos MongoDB.
                $request->merge(['selecciones'=>json_encode($selecciones)]);
                $id = $compras->crearPresencial($request,$actividad,$usuario);
                $orden = Orden::whereKey($id)->firstOrFail();
                if ($recibido > $gestion->centavos($orden->total)) {
                    throw ValidationException::withMessages(['monto_recibido'=>
                        'El dinero recibido supera el total de la compra.']);
                }
                $gestion->ejecutar($orden, function (Orden $actual) use ($gestion,$datos,$archivo) {
                    $gestion->registrarManual($actual,$datos['metodo'],$archivo,$datos['modalidad']);
                });
                $gestion->ejecutar($orden,function (Orden $actual) use ($gestion,$request,$recibido,$verificada) {
                    $pago = $actual->pago;
                    $abono = $pago->abonos()->where('estado','pendiente')->lockForUpdate()->firstOrFail();
                    $abono->update(['monto_reportado'=>$gestion->dinero($recibido)]);
                    if ($verificada && $recibido > 0) {
                        $gestion->acreditarAbono($actual,$pago,$abono,$recibido,
                            (int)$request->user()->id_usuario,'Cobro registrado por caja presencial.');
                    }
                });
                return $id;
            },3);
        } catch (\Throwable $e) {
            if ($archivo) Storage::disk('local')->delete($archivo);
            throw $e;
        }

        $mensaje = 'Venta presencial generada. Revisá el dinero acreditado y el saldo pendiente.';
        if ($nuevo) {
            try {
                $cuentas->enviarEnlace($nuevo);
                $mensaje .= ' Se envió al comprador un enlace para establecer su contraseña.';
            } catch (\Throwable $e) {
                report($e);
                $mensaje .= ' No se pudo enviar el correo: indicá al comprador que utilice «Olvidé mi contraseña».';
            }
        }
        return redirect()->route('admin.ventas-presenciales.show',$ordenId)->with('success',$mensaje);
    }

    public function show(Orden $orden)
    {
        abort_unless($orden->canal_venta==='presencial',404);
        return view('admin.ventas-presenciales.show', [
            'orden'=>$orden->load(['actividad','detalles','pago.abonos']),
        ]);
    }
}
