<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->check()) {
            $rol = auth()->user()->rol?->nombre;

            if (in_array($rol, ['superadmin', 'admin'], true)) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('user.dashboard');
        }

        $categorias = ['Todas', 'Congresos', 'Talleres', 'Excursiones', 'Ventas', 'Académicas', 'Recreativas'];
        $actividades = $this->actividades();
        $destacada = collect($actividades)->firstWhere('destacada', true) ?? $actividades[0];

        return view('welcome', compact('categorias', 'actividades', 'destacada'));
    }

    public function show(string $slug): View
    {
        $actividad = collect($this->actividades())->firstWhere('slug', $slug);

        abort_unless($actividad, 404);

        return view('activities.show', compact('actividad'));
    }

    private function actividades(): array
    {
        return [
            [
                'slug' => 'congreso-innovacion-digital-2026',
                'titulo' => 'Congreso de Innovación Digital 2026',
                'categoria' => 'Congresos',
                'fecha' => '18 OCT',
                'hora' => '8:00 a. m.',
                'lugar' => 'San Miguel, El Salvador',
                'organizacion' => 'Comunidad tecnológica',
                'precio' => 'Gratis',
                'estado' => 'Inscripción abierta',
                'descripcion' => 'Ponencias, tendencias y experiencias sobre innovación y transformación digital.',
                'imagen' => 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=1200&q=85',
                'destacada' => true,
            ],
            [
                'slug' => 'taller-cocina-creativa',
                'titulo' => 'Taller de Cocina Creativa',
                'categoria' => 'Talleres',
                'fecha' => '20 OCT',
                'hora' => '2:00 p. m.',
                'lugar' => 'San Miguel, El Salvador',
                'organizacion' => 'Sabores Locales',
                'precio' => '$8.00',
                'estado' => '12 cupos',
                'descripcion' => 'Una experiencia práctica para aprender nuevas técnicas y compartir sabores.',
                'imagen' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=1200&q=85',
                'destacada' => false,
            ],
            [
                'slug' => 'excursion-ruta-de-montana',
                'titulo' => 'Excursión: Ruta de Montaña',
                'categoria' => 'Excursiones',
                'fecha' => '25 OCT',
                'hora' => '5:30 a. m.',
                'lugar' => 'Morazán, El Salvador',
                'organizacion' => 'Explora SV',
                'precio' => '$15.00',
                'estado' => 'Disponible',
                'descripcion' => 'Un día fuera de la rutina entre naturaleza, aventura y nuevas experiencias.',
                'imagen' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=85',
                'destacada' => false,
            ],
            [
                'slug' => 'feria-de-emprendimientos',
                'titulo' => 'Feria de Emprendimientos',
                'categoria' => 'Ventas',
                'fecha' => '28 OCT',
                'hora' => '9:00 a. m.',
                'lugar' => 'San Miguel, El Salvador',
                'organizacion' => 'Emprende Oriente',
                'precio' => 'Entrada libre',
                'estado' => 'Disponible',
                'descripcion' => 'Productos, marcas y propuestas creadas por emprendedores de distintos rubros.',
                'imagen' => 'https://images.unsplash.com/photo-1488459716781-31db52582fe9?auto=format&fit=crop&w=1200&q=85',
                'destacada' => false,
            ],
            [
                'slug' => 'festival-musica-en-vivo',
                'titulo' => 'Festival de Música en Vivo',
                'categoria' => 'Recreativas',
                'fecha' => '02 NOV',
                'hora' => '6:00 p. m.',
                'lugar' => 'San Miguel, El Salvador',
                'organizacion' => 'Colectivo Cultural',
                'precio' => '$5.00',
                'estado' => 'Inscripción abierta',
                'descripcion' => 'Una noche para descubrir artistas, compartir y disfrutar música en vivo.',
                'imagen' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=85',
                'destacada' => false,
            ],
            [
                'slug' => 'bootcamp-inteligencia-artificial',
                'titulo' => 'Bootcamp de Inteligencia Artificial',
                'categoria' => 'Académicas',
                'fecha' => '05 NOV',
                'hora' => '1:00 p. m.',
                'lugar' => 'Modalidad híbrida',
                'organizacion' => 'Tech Learning',
                'precio' => 'Gratis',
                'estado' => '20 cupos',
                'descripcion' => 'Una introducción práctica a herramientas y conceptos actuales de inteligencia artificial.',
                'imagen' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1200&q=85',
                'destacada' => false,
            ],
        ];
    }
}
