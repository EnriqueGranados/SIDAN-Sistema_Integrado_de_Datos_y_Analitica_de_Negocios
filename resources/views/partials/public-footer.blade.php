<footer class="border-t border-slate-200 bg-white dark:border-white/10 dark:bg-sidan-950">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">

            {{-- SIDAN --}}
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sidan-900 text-sm font-black text-white">
                        S
                    </div>

                    <span class="text-xl font-black text-sidan-900 dark:text-white">
                        SIDAN
                    </span>
                </div>

                <p class="mt-4 max-w-sm text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Sistema Integrado de Datos y Analítica de Negocios.
                </p>

                <p class="mt-3 max-w-sm text-xs leading-5 text-slate-400">
                    Descubre actividades, eventos, experiencias, productos y oportunidades desde un solo lugar.
                </p>
            </div>


            {{-- NOSOTROS --}}
            <div>
                <h3 class="text-sm font-black uppercase tracking-wider text-sidan-900 dark:text-white">
                    Nosotros
                </h3>

                <div class="mt-4 space-y-3 text-sm font-semibold text-slate-500 dark:text-slate-400">
                    <a href="#" class="block transition hover:text-sidan-500">
                        Quiénes somos
                    </a>

                    <a href="#" class="block transition hover:text-sidan-500">
                        Misión y visión
                    </a>

                    <a href="#" class="block transition hover:text-sidan-500">
                        Nuestros valores
                    </a>

                    <a href="#" class="block transition hover:text-sidan-500">
                        Contacto
                    </a>
                </div>
            </div>


            {{-- INFORMACIÓN --}}
            <div>
                <h3 class="text-sm font-black uppercase tracking-wider text-sidan-900 dark:text-white">
                    Información
                </h3>

                <div class="mt-4 space-y-3 text-sm font-semibold text-slate-500 dark:text-slate-400">
                    <a href="#" class="block transition hover:text-sidan-500">
                        Términos y condiciones
                    </a>

                    <a href="#" class="block transition hover:text-sidan-500">
                        Política de privacidad
                    </a>

                    <a href="#" class="block transition hover:text-sidan-500">
                        Política de cookies
                    </a>

                    <a href="#" class="block transition hover:text-sidan-500">
                        Uso del sistema
                    </a>

                    <a href="#" class="block transition hover:text-sidan-500">
                        Ayuda y soporte
                    </a>
                </div>
            </div>


            {{-- EXPLORAR --}}
            <div>
                <h3 class="text-sm font-black uppercase tracking-wider text-sidan-900 dark:text-white">
                    Explora
                </h3>

                <div class="mt-4 space-y-3 text-sm font-semibold text-slate-500 dark:text-slate-400">
                    <a href="{{ url('/actividades') }}" class="block transition hover:text-sidan-500">
                        Todas las actividades
                    </a>

                    <a href="{{ route('welcome') }}#categorias" class="block transition hover:text-sidan-500">
                        Categorías y etiquetas
                    </a>

                    <a href="{{ route('welcome') }}#como-funciona" class="block transition hover:text-sidan-500">
                        Cómo funciona
                    </a>

                    @guest
                        <a href="{{ route('login') }}" class="block transition hover:text-sidan-500">
                            Iniciar sesión
                        </a>

                        <a href="{{ route('register') }}" class="block transition hover:text-sidan-500">
                            Crear cuenta
                        </a>
                    @else
                        @php
                            $usuarioFooter = request()->user();
                            $rolFooter = strtolower((string) $usuarioFooter->rol()->value('nombre'));
                            $esAdminFooter = in_array($rolFooter, ['admin', 'superadmin'], true);
                        @endphp

                        <a
                            href="{{ $esAdminFooter ? route('admin.dashboard') : route('user.dashboard') }}"
                            class="block transition hover:text-sidan-500"
                        >
                            {{ $esAdminFooter ? 'Panel administrativo' : 'Mi cuenta' }}
                        </a>
                    @endguest
                </div>
            </div>

        </div>


        <div class="mt-10 flex flex-col gap-4 border-t border-slate-200 pt-6 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">

            <p>
                © {{ date('Y') }} SIDAN. Todos los derechos reservados.
            </p>

            <div class="flex flex-wrap gap-x-5 gap-y-2 text-xs font-semibold">
                <a href="#" class="transition hover:text-sidan-500">
                    Privacidad
                </a>

                <a href="#" class="transition hover:text-sidan-500">
                    Términos
                </a>

                <a href="#" class="transition hover:text-sidan-500">
                    Cookies
                </a>
            </div>

        </div>

    </div>
</footer>