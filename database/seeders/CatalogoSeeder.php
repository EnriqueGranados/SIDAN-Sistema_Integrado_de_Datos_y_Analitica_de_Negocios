<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Etiqueta;
use App\Models\Espacio;
use App\Models\Recurso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Categorías para actividades universitarias.
            $categorias = [
                'Formación académica' => 'Cursos, talleres, seminarios y capacitaciones.',
                'Investigación e innovación' => 'Jornadas científicas, presentación de investigaciones y tecnología.',
                'Extensión universitaria' => 'Actividades educativas abiertas a la comunidad.',
                'Cultura y arte' => 'Presentaciones artísticas, exposiciones y actividades culturales.',
                'Deportes y recreación' => 'Torneos, encuentros deportivos y recreación.',
                'Bienestar estudiantil' => 'Orientación, salud preventiva y acompañamiento estudiantil.',
                'Graduaciones y actos académicos' => 'Ceremonias, reconocimientos y actos institucionales.',
                'Orientación y admisiones' => 'Charlas de ingreso y orientación académica.',
                'Vinculación y servicio social' => 'Voluntariado, servicio social y trabajo comunitario.',
                'Emprendimiento y ferias' => 'Ferias, iniciativas estudiantiles y exposiciones.',
            ];

            foreach ($categorias as $nombre => $descripcion) {
                Categoria::firstOrCreate(
                    ['nombre' => $nombre],
                    [
                        'slug' => Str::slug($nombre),
                        'descripcion' => $descripcion,
                        'orden' => array_search($nombre, array_keys($categorias), true) + 1,
                        'activo' => true,
                    ]
                );
            }

            // 2. Etiquetas para filtrar y clasificar actividades.
            $etiquetas = [
                'Presencial', 'Virtual', 'Híbrida', 'Gratuita', 'Con inscripción',
                'Cupo limitado', 'Estudiantes', 'Docentes', 'Público general',
                'Congreso', 'Taller', 'Seminario', 'Conferencia',
                'Feria universitaria', 'Jornada científica', 'Voluntariado',
                'Servicio social', 'Sede Central', 'FMO San Miguel',
            ];

            foreach ($etiquetas as $nombre) {
                Etiqueta::firstOrCreate(
                    ['nombre' => $nombre],
                    ['slug' => Str::slug($nombre), 'activo' => true]
                );
            }

            // 3. Recursos: nombre, categoría, unidad, movilidad y descripción.
            $recursos = [
                ['Proyector multimedia', 'Audiovisual', 'unidad', true, 'Proyección para clases y conferencias.'],
                ['Pantalla de proyección', 'Audiovisual', 'unidad', true, 'Superficie para proyecciones.'],
                ['Sistema de sonido', 'Audio', 'equipo', true, 'Amplificación para actos académicos.'],
                ['Micrófono inalámbrico', 'Audio', 'unidad', true, 'Micrófono para ponencias.'],
                ['Bocina amplificada', 'Audio', 'unidad', true, 'Equipo para actividades y eventos.'],
                ['Computadora de escritorio', 'Tecnología', 'unidad', false, 'Equipo de laboratorio informático.'],
                ['Laptop para presentaciones', 'Tecnología', 'unidad', true, 'Equipo portátil para expositores.'],
                ['Punto de acceso Wi-Fi', 'Conectividad', 'unidad', false, 'Conectividad inalámbrica del espacio.'],
                ['Router de red', 'Conectividad', 'unidad', true, 'Equipo para prácticas de redes.'],
                ['Switch de red', 'Conectividad', 'unidad', true, 'Equipo para laboratorios de redes.'],
                ['Mesa plegable', 'Mobiliario', 'unidad', true, 'Mesa para actividades y ferias.'],
                ['Silla para asistentes', 'Mobiliario', 'unidad', true, 'Asiento para participantes.'],
                ['Atril para ponencias', 'Mobiliario', 'unidad', true, 'Soporte para intervenciones públicas.'],
                ['Pizarra acrílica', 'Presentación y señalización', 'unidad', false, 'Pizarra para docencia.'],
                ['Extensión eléctrica', 'Energía eléctrica', 'unidad', true, 'Accesorio de alimentación eléctrica.'],
                ['Banner informativo', 'Presentación y señalización', 'unidad', true, 'Señalización de una actividad.'],
                ['Carpa para feria', 'Logística', 'unidad', true, 'Cubierta temporal para eventos.'],
            ];

            $idsRecursos = [];
            foreach ($recursos as [$nombre, $categoria, $unidad, $esMovil, $descripcion]) {
                $recurso = Recurso::firstOrCreate(
                    ['nombre' => $nombre],
                    [
                        'descripcion' => $descripcion,
                        'categoria' => $categoria,
                        'unidad_medida' => $unidad,
                        'es_movil' => $esMovil,
                        'activo' => true,
                    ]
                );
                $idsRecursos[$nombre] = $recurso->id_recurso;
            }

            // 4. Espacios jerárquicos (ubicaciones y capacidades ilustrativas).
            $sedes = [
                ['Universidad de El Salvador - Sede Central', 'San Salvador, El Salvador'],
                ['UES - Facultad Multidisciplinaria Oriental', 'San Miguel, El Salvador'],
            ];

            $idsSedes = [];
            foreach ($sedes as [$nombre, $direccion]) {
                $sede = Espacio::firstOrCreate(
                    ['nombre' => $nombre, 'id_espacio_contenedor' => null],
                    [
                        'descripcion' => 'Contenedor de espacios universitarios. Datos de demostración.',
                        'direccion' => $direccion,
                        'capacidad' => null,
                        'permite_actividades' => false,
                        'activo' => true,
                        'estado_validacion' => Espacio::VALIDACION_VALIDADO,
                        'origen_registro' => Espacio::ORIGEN_ADMINISTRACION,
                        'validado_en' => now(),
                    ]
                );
                $idsSedes[$nombre] = $sede->id_espacio;
            }

            $espacios = [
                ['Laboratorio de informática FMO (demo)', 'UES - Facultad Multidisciplinaria Oriental', 30],
                ['Auditorio universitario FMO (demo)', 'UES - Facultad Multidisciplinaria Oriental', 120],
                ['Aula de conferencias FMO (demo)', 'UES - Facultad Multidisciplinaria Oriental', 45],
                ['Plaza para ferias FMO (demo)', 'UES - Facultad Multidisciplinaria Oriental', null],
                ['Laboratorio de redes Sede Central (demo)', 'Universidad de El Salvador - Sede Central', 25],
                ['Salón de usos múltiples Sede Central (demo)', 'Universidad de El Salvador - Sede Central', 80],
                ['Cancha multiusos Sede Central (demo)', 'Universidad de El Salvador - Sede Central', 150],
            ];

            $idsEspacios = [];
            foreach ($espacios as [$nombre, $sedeNombre, $capacidad]) {
                $espacio = Espacio::firstOrCreate(
                    [
                        'nombre' => $nombre,
                        'id_espacio_contenedor' => $idsSedes[$sedeNombre],
                    ],
                    [
                        'descripcion' => 'Espacio ilustrativo para pruebas de SIDAN; verificar datos antes de usar en producción.',
                        'direccion' => $sedeNombre === 'UES - Facultad Multidisciplinaria Oriental'
                            ? 'San Miguel, El Salvador'
                            : 'San Salvador, El Salvador',
                        'capacidad' => $capacidad,
                        'permite_actividades' => true,
                        'activo' => true,
                        'estado_validacion' => Espacio::VALIDACION_VALIDADO,
                        'origen_registro' => Espacio::ORIGEN_ADMINISTRACION,
                        'validado_en' => now(),
                    ]
                );
                $idsEspacios[$nombre] = $espacio;
            }

            // 5. Disponibilidad de recursos ficticia para probar las relaciones.
            $asignaciones = [
                'Laboratorio de informática FMO (demo)' => ['Computadora de escritorio' => 30, 'Proyector multimedia' => 1, 'Pizarra acrílica' => 1],
                'Auditorio universitario FMO (demo)' => ['Proyector multimedia' => 1, 'Sistema de sonido' => 1, 'Micrófono inalámbrico' => 2, 'Silla para asistentes' => 120],
                'Aula de conferencias FMO (demo)' => ['Proyector multimedia' => 1, 'Pizarra acrílica' => 1, 'Silla para asistentes' => 45],
                'Plaza para ferias FMO (demo)' => ['Carpa para feria' => 5, 'Mesa plegable' => 10, 'Banner informativo' => 4],
                'Laboratorio de redes Sede Central (demo)' => ['Computadora de escritorio' => 25, 'Router de red' => 6, 'Switch de red' => 6],
                'Salón de usos múltiples Sede Central (demo)' => ['Proyector multimedia' => 1, 'Sistema de sonido' => 1, 'Silla para asistentes' => 80],
            ];

            foreach ($asignaciones as $nombreEspacio => $items) {
                $pivot = [];
                foreach ($items as $nombreRecurso => $cantidad) {
                    $pivot[$idsRecursos[$nombreRecurso]] = [
                        'cantidad' => $cantidad,
                        'observacion' => 'Cantidad de demostración, no inventario oficial.',
                    ];
                }
                $idsEspacios[$nombreEspacio]->recursos()->syncWithoutDetaching($pivot);
            }
        });

        $this->command?->info('Catálogos UES cargados sin eliminar los datos existentes.');
    }
}
