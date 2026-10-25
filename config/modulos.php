<?php

return [
    'catalogo' => [
        'dashboard' => [
            'nombre' => 'Dashboard',
            'descripcion' => 'Consultar el panel administrativo.',
            'ruta' => 'admin.dashboard',
        ],
        'actividades' => [
            'nombre' => 'Actividades',
            'descripcion' => 'Crear, configurar, editar y publicar actividades.',
            'ruta' => 'admin.actividades.index',
        ],
        'revision_actividades' => [
            'nombre' => 'Revisión de actividades',
            'descripcion' => 'Revisar actividades, agregar observaciones, aprobar y rechazar.',
            'ruta' => 'admin.actividades.revision.index',
        ],
        'categorias' => [
            'nombre' => 'Categorías',
            'descripcion' => 'Administrar las categorías de actividades.',
            'ruta' => 'admin.categorias.index',
        ],
        'etiquetas' => [
            'nombre' => 'Etiquetas',
            'descripcion' => 'Administrar las etiquetas del sistema.',
            'ruta' => 'admin.etiquetas.index',
        ],
        'recursos' => [
            'nombre' => 'Recursos',
            'descripcion' => 'Administrar los recursos disponibles.',
            'ruta' => 'admin.recursos.index',
        ],
        'espacios' => [
            'nombre' => 'Espacios',
            'descripcion' => 'Administrar espacios y ubicaciones.',
            'ruta' => 'admin.espacios.index',
        ],
        'inscripciones' => [
            'nombre' => 'Inscripciones',
            'descripcion' => 'Consultar y gestionar las inscripciones disponibles en el módulo.',
            'ruta' => 'admin.control.inscripciones',
        ],
        'ordenes' => [
            'nombre' => 'Órdenes y compras',
            'descripcion' => 'Consultar las órdenes y compras.',
            'ruta' => 'admin.control.ordenes',
        ],
        'consolidado' => [
            'nombre' => 'Consolidado de pedidos',
            'descripcion' => 'Consultar e imprimir el consolidado de pedidos.',
            'ruta' => 'admin.control.consolidado',
        ],
        'pagos' => [
            'nombre' => 'Pagos',
            'descripcion' => 'Gestionar pagos, abonos, entregas y órdenes vencidas.',
            'ruta' => 'admin.pagos.index',
        ],
        'ventas_presenciales' => [
            'nombre' => 'Generar orden',
            'descripcion' => 'Generar órdenes de compra y registrar cobros presenciales.',
            'ruta' => 'admin.ventas-presenciales.index',
        ],
        'usuarios' => [
            'nombre' => 'Usuarios',
            'descripcion' => 'Gestionar usuarios y asignar roles dentro de los accesos autorizados.',
            'ruta' => 'admin.users.index',
        ],
        'welcome' => [
            'nombre' => 'Administrar Welcome',
            'descripcion' => 'Administrar los espacios y actividades de la página de inicio.',
            'ruta' => 'admin.welcome.index',
        ],
        'roles' => [
            'nombre' => 'Roles del Sistema',
            'descripcion' => 'Administrar roles y permisos. Reservado al superadministrador.',
            'ruta' => 'admin.roles.index',
        ],
    ],

    // Se revisan primero las rutas específicas y después las generales.
    'rutas' => [
        'admin.actividades.revision.*' => ['revision_actividades'],

        // Funciones auxiliares necesarias al configurar actividades.
        'admin.espacios.buscar' => ['espacios', 'actividades'],
        'admin.espacios.descendientes' => ['espacios', 'actividades'],
        'admin.espacios.crear-rapido' => ['espacios', 'actividades'],

        'admin.dashboard' => ['dashboard'],
        'admin.actividades.*' => ['actividades'],
        'admin.categorias.*' => ['categorias'],
        'admin.etiquetas.*' => ['etiquetas'],
        'admin.recursos.*' => ['recursos'],
        'admin.espacios.*' => ['espacios'],
        'admin.control.inscripciones' => ['inscripciones'],
        'admin.control.ordenes' => ['ordenes'],
        'admin.control.consolidado' => ['consolidado'],
        'admin.control.consolidado.imprimir' => ['consolidado'],
        'admin.pagos.*' => ['pagos'],
        'admin.ventas-presenciales.*' => ['ventas_presenciales'],
        'admin.ordenes.vencidas' => ['pagos'],
        'admin.ordenes.conciliar' => ['pagos'],
        'admin.ordenes.abonar' => ['pagos'],
        'admin.ordenes.cerrar' => ['pagos'],
        'admin.ordenes.entregar' => ['pagos'],
        'admin.users.*' => ['usuarios'],
        'admin.welcome.*' => ['welcome'],
        'admin.roles.*' => ['roles'],
    ],
];
