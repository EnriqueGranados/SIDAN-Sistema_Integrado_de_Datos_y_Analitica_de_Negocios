<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GEMMA - {{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ===== LOADER ===== */
        #loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: #0a0a0a;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 1.2s ease, visibility 1.2s ease;
        }
        #loader.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        .loader-letter {
            display: inline-block;
            font-family: 'Playfair Display', serif;
            font-size: clamp(4rem, 12vw, 10rem);
            font-weight: 700;
            opacity: 0;
            transform: translateY(30px);
            animation: letterIn 0.6s ease forwards;
        }
        .loader-letter:nth-child(1) { animation-delay: 0.1s; color: #f472b6; }
        .loader-letter:nth-child(2) { animation-delay: 0.25s; color: #60a5fa; }
        .loader-letter:nth-child(3) { animation-delay: 0.4s; color: #34d399; }
        .loader-letter:nth-child(4) { animation-delay: 0.55s; color: #fbbf24; }
        .loader-letter:nth-child(5) { animation-delay: 0.7s; color: #f87171; }

        @keyframes letterIn {
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== MAIN ===== */
        .main-content {
            opacity: 0;
            transition: opacity 1.5s ease 0.3s;
        }
        .main-content.visible { opacity: 1; }

        /* ===== SCROLL INVISIBLE PERO FUNCIONAL ===== */
        .scroll-hidden {
            overflow-y: auto;
            scrollbar-width: none;           /* Firefox */
            -ms-overflow-style: none;        /* IE/Edge */
        }
        .scroll-hidden::-webkit-scrollbar {
            display: none;                   /* Chrome/Safari */
        }

        /* ===== FONDO ANIMADO ===== */
        .animated-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            background: #0a0a0a;
        }
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.4;
            animation: blobMove 25s ease-in-out infinite;
        }
        .blob-1 {
            width: 600px; height: 600px;
            background: #7c3aed;
            top: -20%; left: 20%;
            animation-delay: 0s;
        }
        .blob-2 {
            width: 500px; height: 500px;
            background: #06b6d4;
            bottom: -20%; right: 10%;
            animation-delay: -7s;
        }
        .blob-3 {
            width: 400px; height: 400px;
            background: #ec4899;
            top: 30%; left: 50%;
            animation-delay: -14s;
        }

        @keyframes blobMove {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(80px, -60px) scale(1.1); }
            66% { transform: translate(-60px, 80px) scale(0.9); }
        }

        /* ===== GLASS CARD ===== */
        .glass-card {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }

        /* ===== SLIDER TOGGLE ===== */
        .toggle-slider {
            position: absolute;
            top: 0;
            left: 0;
            width: 50%;
            height: 100%;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 9999px;
            transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
            backdrop-filter: blur(10px);
        }
        .toggle-slider.right {
            transform: translateX(100%);
        }

        /* ===== FORM PANELS ===== */
        .form-container {
            position: relative;
            overflow: hidden;
        }
        .form-panel {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            max-height: 480px;
            overflow-y: auto;
            padding-right: 4px;
            scrollbar-width: none;
            -ms-overflow-style: none;
            transition: transform 0.5s ease, opacity 0.5s ease;
        }
        .form-panel::-webkit-scrollbar {
            display: none;
        }
        .form-panel.login {
            transform: translateX(0);
            opacity: 1;
        }
        .form-panel.login.hidden-form {
            transform: translateX(-100%);
            opacity: 0;
            pointer-events: none;
        }
        .form-panel.register {
            transform: translateX(100%);
            opacity: 0;
            pointer-events: none;
        }
        .form-panel.register.visible-form {
            transform: translateX(0);
            opacity: 1;
            pointer-events: auto;
        }

        /* ===== INPUTS GLASS ===== */
        .glass-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
            outline: none;
        }
        .glass-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        /* ===== BOTÓN PRIMARIO ===== */
        .glass-btn {
            background: rgba(255, 255, 255, 0.9);
            color: #0a0a0a;
            transition: all 0.3s ease;
        }
        .glass-btn:hover {
            background: white;
            box-shadow: 0 4px 20px rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }

        /* ===== SOCIAL BUTTONS ===== */
        .social-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .social-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
        }

        /* ===== ANIMACIONES DE ENTRADA ===== */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease forwards;
        }
        .fade-in-delay-1 { animation-delay: 0.2s; }
        .fade-in-delay-2 { animation-delay: 0.4s; }
        .fade-in-delay-3 { animation-delay: 0.6s; }
        .fade-in-delay-4 { animation-delay: 0.8s; }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .counter {
            font-variant-numeric: tabular-nums;
        }
    </style>
</head>
<body class="bg-black text-white/90 antialiased h-screen overflow-hidden">

    {{-- ===== LOADER ===== --}}
    <div id="loader">
        <div class="flex gap-2">
            <span class="loader-letter">G</span>
            <span class="loader-letter">E</span>
            <span class="loader-letter">M</span>
            <span class="loader-letter">M</span>
            <span class="loader-letter">A</span>
        </div>
    </div>

    {{-- ===== FONDO ANIMADO ===== --}}
    <div class="animated-bg">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    {{-- ===== CONTENIDO PRINCIPAL ===== --}}
    <div class="main-content h-screen flex flex-col lg:flex-row relative z-10">

        {{-- ========== LADO IZQUIERDO: CON SCROLL INVISIBLE ========== --}}
        <section class="lg:w-3/5 w-full lg:h-screen h-auto scroll-hidden">
            <div class="px-8 lg:px-16 py-12 lg:py-20">

                {{-- HERO --}}
                <div class="min-h-screen flex flex-col justify-center">
                    <p class="text-xs tracking-[0.4em] text-gray-400 mb-4 fade-in fade-in-delay-1">
                        BIENVENIDO A
                    </p>
                    <h1 class="font-serif text-7xl lg:text-8xl xl:text-9xl font-bold leading-none mb-6 fade-in fade-in-delay-2">
                        GEMMA
                    </h1>
                    <p class="text-base lg:text-lg text-gray-300 max-w-lg leading-relaxed mb-10 fade-in fade-in-delay-3">
                        Una plataforma inteligente que transforma la manera en que gestionas,
                        analizas y haces crecer tu negocio.
                    </p>
                    
                </div>

                {{-- QUIÉNES SOMOS --}}
                <div class="py-24 border-t border-white/10">
                    <p class="text-xs tracking-[0.4em] text-gray-400 mb-4">01 — QUIÉNES SOMOS</p>
                    <h2 class="font-serif text-4xl lg:text-6xl font-bold mb-8">
                        No somos solo una herramienta.<br>
                        <span class="text-gray-400">Somos tu aliado estratégico.</span>
                    </h2>
                    <p class="text-gray-300 max-w-2xl leading-relaxed">
                        GEMMA nació de la necesidad de simplificar lo complejo. Somos un equipo
                        de desarrolladores, diseñadores y estrategas apasionados por crear
                        experiencias digitales que realmente marquen la diferencia.
                    </p>
                </div>

                {{-- QUÉ HACEMOS --}}
                <div class="py-24 border-t border-white/10">
                    <p class="text-xs tracking-[0.4em] text-gray-400 mb-4">02 — QUÉ HACEMOS</p>
                    <h2 class="font-serif text-4xl lg:text-6xl font-bold mb-12">
                        Soluciones que<br>
                        <span class="text-gray-400">impulsan resultados.</span>
                    </h2>
                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="p-8 border border-white/10 rounded-lg hover:border-white/30 transition">
                            <div class="text-3xl mb-4"></div>
                            <h3 class="font-serif text-2xl font-bold mb-3">Análisis Inteligente</h3>
                            <p class="text-gray-300 text-sm">Datos convertidos en decisiones. Dashboards en tiempo real.</p>
                        </div>
                        <div class="p-8 border border-white/10 rounded-lg hover:border-white/30 transition">
                            <div class="text-3xl mb-4"></div>
                            <h3 class="font-serif text-2xl font-bold mb-3">Automatización</h3>
                            <p class="text-gray-300 text-sm">Flujos de trabajo que se ejecutan solos mientras tú creces.</p>
                        </div>
                        <div class="p-8 border border-white/10 rounded-lg hover:border-white/30 transition">
                            <div class="text-3xl mb-4"></div>
                            <h3 class="font-serif text-2xl font-bold mb-3">Seguridad Total</h3>
                            <p class="text-gray-300 text-sm">Tus datos protegidos con los más altos estándares.</p>
                        </div>
                        <div class="p-8 border border-white/10 rounded-lg hover:border-white/30 transition">
                            <div class="text-3xl mb-4"></div>
                            <h3 class="font-serif text-2xl font-bold mb-3">Soporte 24/7</h3>
                            <p class="text-gray-300 text-sm">Un equipo humano siempre disponible para ayudarte.</p>
                        </div>
                    </div>
                </div>

                {{-- CTA FINAL --}}
                <div class="py-32 border-t border-white/10 text-center">
                    <h2 class="font-serif text-5xl lg:text-7xl font-bold mb-6">
                        ¿Listo para empezar?
                    </h2>
                    <p class="text-gray-300 mb-10">Únete a miles de usuarios que ya confían en GEMMA.</p>
                    <button onclick="document.getElementById('auth-card').scrollIntoView({behavior:'smooth'})"
                            class="glass-btn px-10 py-4 font-semibold rounded-lg">
                        Crear mi cuenta →
                    </button>
                </div>

                <footer class="py-8 border-t border-white/10 text-xs text-gray-500 text-center">
                    © 2026 GEMMA. Todos los derechos reservados.
                </footer>
            </div>
        </section>

        {{-- ========== LADO DERECHO: FORMULARIO FIJO ========== --}}
        <aside class="lg:w-2/5 w-full lg:h-screen lg:sticky lg:top-0 flex items-center justify-center p-6 lg:p-12">

            <div id="auth-card" class="glass-card w-full max-w-md rounded-2xl p-8 lg:p-10 fade-in fade-in-delay-3">

                <div class="mb-8">
                    <span class="font-serif text-3xl font-bold tracking-wider">GEMMA</span>
                    <p class="text-xs text-gray-400 mt-2">Accede a tu espacio</p>
                </div>

                <div class="relative flex bg-white/5 rounded-full p-1 mb-8">
                    <div id="toggle-slider" class="toggle-slider"></div>
                    <button id="tab-login"
                            onclick="switchForm('login')"
                            class="relative z-10 flex-1 py-2.5 text-sm font-medium text-white transition">
                        Iniciar Sesión
                    </button>
                    <button id="tab-register"
                            onclick="switchForm('register')"
                            class="relative z-10 flex-1 py-2.5 text-sm font-medium text-gray-400 transition">
                        Registrarse
                    </button>
                </div>

                <div class="form-container" style="min-height: 400px;">

                    {{-- LOGIN --}}
                    <div id="form-login" class="form-panel login">
                        <form method="POST" action="{{ route('login') }}" class="space-y-5">
                            @csrf
                            <div>
                                <label for="email" class="block text-xs font-medium text-gray-300 mb-2">Correo electrónico</label>
                                <input id="email" type="email" name="email" required autofocus
                                       class="glass-input w-full rounded-lg px-4 py-3 text-sm"
                                       placeholder="tu@email.com">
                                @error('email')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="password" class="block text-xs font-medium text-gray-300 mb-2">Contraseña</label>
                                <input id="password" type="password" name="password" required
                                       class="glass-input w-full rounded-lg px-4 py-3 text-sm"
                                       placeholder="••••••••">
                                @error('password')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <label class="flex items-center gap-2 text-gray-300">
                                    <input type="checkbox" name="remember" class="rounded bg-white/5 border-white/20">
                                    Recuérdame
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-gray-300 hover:text-white">
                                        ¿Olvidaste la contraseña?
                                    </a>
                                @endif
                            </div>
                            <button type="submit" class="glass-btn w-full font-semibold py-3 rounded-lg">
                                Iniciar Sesión
                            </button>
                        </form>
                        <div class="mt-6">
                            <p class="text-center text-xs text-gray-400 mb-3">O usa tu cuenta</p>
                            <div class="flex justify-center gap-3">
                                <button type="button" class="social-btn w-10 h-10 rounded-lg">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </button>
                                <button type="button" class="social-btn w-10 h-10 rounded-lg">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/></svg>
                                </button>
                                <button type="button" class="social-btn w-10 h-10 rounded-lg">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- REGISTRO --}}
                    <div id="form-register" class="form-panel register">
                        <form method="POST" action="{{ route('register') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="reg-name" class="block text-xs font-medium text-gray-300 mb-2">Nombre</label>
                                <input id="reg-name" type="text" name="name" required autofocus
                                       class="glass-input w-full rounded-lg px-4 py-3 text-sm"
                                       placeholder="Tu nombre">
                                @error('name')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="reg-email" class="block text-xs font-medium text-gray-300 mb-2">Correo electrónico</label>
                                <input id="reg-email" type="email" name="email" required
                                       class="glass-input w-full rounded-lg px-4 py-3 text-sm"
                                       placeholder="tu@email.com">
                                @error('email')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="reg-password" class="block text-xs font-medium text-gray-300 mb-2">Contraseña</label>
                                <input id="reg-password" type="password" name="password" required
                                       class="glass-input w-full rounded-lg px-4 py-3 text-sm"
                                       placeholder="Mínimo 8 caracteres">
                                @error('password')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="reg-password-confirm" class="block text-xs font-medium text-gray-300 mb-2">Confirmar contraseña</label>
                                <input id="reg-password-confirm" type="password" name="password_confirmation" required
                                       class="glass-input w-full rounded-lg px-4 py-3 text-sm"
                                       placeholder="Repite la contraseña">
                            </div>
                            <button type="submit" class="glass-btn w-full font-semibold py-3 rounded-lg mt-2">
                                Registrarse
                            </button>
                        </form>
                        <div class="mt-6">
                            <p class="text-center text-xs text-gray-400 mb-3">O usa tu cuenta</p>
                            <div class="flex justify-center gap-3">
                                <button type="button" class="social-btn w-10 h-10 rounded-lg">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </button>
                                <button type="button" class="social-btn w-10 h-10 rounded-lg">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/></svg>
                                </button>
                                <button type="button" class="social-btn w-10 h-10 rounded-lg">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loader').classList.add('hidden');
                document.querySelector('.main-content').classList.add('visible');
                initCounters();
            }, 2200);
        });

        function switchForm(type) {
            const slider = document.getElementById('toggle-slider');
            const loginForm = document.getElementById('form-login');
            const registerForm = document.getElementById('form-register');
            const loginTab = document.getElementById('tab-login');
            const registerTab = document.getElementById('tab-register');

            if (type === 'login') {
                slider.classList.remove('right');
                loginForm.classList.remove('hidden-form');
                registerForm.classList.remove('visible-form');
                loginTab.classList.add('text-white');
                loginTab.classList.remove('text-gray-400');
                registerTab.classList.add('text-gray-400');
                registerTab.classList.remove('text-white');
            } else {
                slider.classList.add('right');
                loginForm.classList.add('hidden-form');
                registerForm.classList.add('visible-form');
                registerTab.classList.add('text-white');
                registerTab.classList.remove('text-gray-400');
                loginTab.classList.add('text-gray-400');
                loginTab.classList.remove('text-white');
            }
        }

        function initCounters() {
            document.querySelectorAll('.counter').forEach(counter => {
                const target = +counter.dataset.target;
                const duration = 2000;
                const start = performance.now();

                function update(now) {
                    const elapsed = now - start;
                    const progress = Math.min(elapsed / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    counter.textContent = Math.floor(eased * target).toLocaleString() + (target === 98 ? '%' : '+');
                    if (progress < 1) requestAnimationFrame(update);
                }
                requestAnimationFrame(update);
            });
        }
    </script>
</body>
</html>