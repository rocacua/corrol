<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CorRol - Gestor de Rol')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('assets/icono_corrol_16px_opt.png') }}">
    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('assets/icono_corrol_32px_opt.png') }}">
    <link rel="apple-touch-icon" sizes="512x512"
        href="{{ asset('assets/icono_corrol_512px_opt.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-between font-sans">

    <!-- ENCABEZADO PRINCIPAL -->
    <header class="bg-slate-800 border-b border-slate-700 sticky top-0 z-50 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            
            <!-- Logo & Enlaces Principales (Escritorio) -->
            <div class="flex items-center space-x-6">
                <a href="{{ route('resources.index') }}" class="text-2xl font-bold text-indigo-400 hover:text-indigo-300 transition-colors flex items-center gap-2" aria-label="CorRol">
                    <img src="{{ asset('assets/logo_corrol_opt.png') }}" alt="CorRol" class="hidden sm:block w-[120px] h-8 object-contain">
                    <img src="{{ asset('assets/icono_corrol_opt.png') }}" alt="CorRol" class="sm:hidden w-9 h-9 object-contain">
                </a>

                <!-- Menú Navegación Escritorio -->
                <nav class="hidden lg:flex space-x-4 text-sm font-medium">
                    <a href="{{ route('resources.index') }}" class="text-slate-300 hover:text-indigo-400 transition-colors">Recursos</a>
                    <a href="{{ route('resources.create') }}" class="text-slate-300 hover:text-indigo-400 transition-colors">+ Crear</a>
                    <a href="{{ route('games.index') }}" class="text-slate-300 hover:text-indigo-400 transition-colors">Juegos</a>
                    <a href="{{ route('campaigns.index') }}" class="text-slate-300 hover:text-indigo-400 transition-colors">Campañas</a>
                    <a href="{{ route('authors.index') }}" class="text-slate-300 hover:text-indigo-400 transition-colors">Autores</a>
                    <a href="{{ route('users.index') }}" class="text-slate-300 hover:text-indigo-400 transition-colors">Usuarios</a>
                </nav>
            </div>

            <!-- Zona de Usuario (SOLO ESCRITORIO: Oculta en móvil con 'hidden lg:flex') -->
            <div class="hidden lg:flex items-center space-x-4 text-sm">
                @auth
                    <a href="{{ route('profile.edit') }}" class="text-slate-300 hover:text-indigo-400 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="font-semibold">{{ auth()->user()->name }}</span>
                    </a>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-slate-700 hover:bg-rose-600 text-slate-300 hover:text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                            Cerrar Sesión
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-slate-300 hover:text-indigo-400 font-medium">Iniciar Sesión</a>
                    <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-4 py-2 rounded-lg text-xs shadow-md">
                        Registrarse
                    </a>
                @endauth
            </div>

            <!-- Botón de Menú Hamburguesa (SOLO MÓVIL: Visibilidad controlada con 'lg:hidden') -->
            <div class="flex lg:hidden">
                <button id="mobile-menu-button" type="button" class="text-slate-300 hover:text-white focus:outline-none p-2 rounded-lg bg-slate-900 border border-slate-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Menú Desplegable Móvil -->
        <div id="mobile-menu" class="hidden lg:hidden bg-slate-800 border-t border-slate-700 p-4 space-y-4 text-sm shadow-xl">
            <!-- 1. Secciones de Navegación -->
            <div class="space-y-2 border-b border-slate-700 pb-3">
                <a href="{{ route('resources.index') }}" class="block text-slate-300 hover:text-indigo-400 py-1 font-medium">Recursos</a>
                <a href="{{ route('resources.create') }}" class="block text-slate-300 hover:text-indigo-400 py-1 font-medium">+ Crear Recurso</a>
                <a href="{{ route('games.index') }}" class="block text-slate-300 hover:text-indigo-400 py-1 font-medium">Juegos</a>
                <a href="{{ route('campaigns.index') }}" class="block text-slate-300 hover:text-indigo-400 py-1 font-medium">Campañas</a>
                <a href="{{ route('authors.index') }}" class="block text-slate-300 hover:text-indigo-400 py-1 font-medium">Autores</a>
                <a href="{{ route('users.index') }}" class="block text-slate-300 hover:text-indigo-400 py-1 font-medium">Usuarios</a>
            </div>

            <!-- 2. Zona de Usuario / Sesión al final del Menú Móvil -->
            <div class="pt-1">
                @auth
                    <div class="flex items-center gap-3 mb-3 p-2 bg-slate-900 rounded-lg border border-slate-700">
                        <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <div>
                            <span class="block text-slate-200 font-semibold text-sm">{{ auth()->user()->name }}</span>
                            <a href="{{ route('profile.edit') }}" class="text-xs text-indigo-400 hover:underline">Ver mi perfil</a>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-slate-700 hover:bg-rose-600 text-white font-bold py-2 rounded-lg text-xs transition-colors">
                            Cerrar Sesión
                        </button>
                    </form>
                @else
                    <div class="flex flex-col gap-2 pt-1">
                        <a href="{{ route('login') }}" class="text-center text-slate-300 hover:text-indigo-400 py-2.5 font-medium border border-slate-700 rounded-lg bg-slate-900">
                            Iniciar Sesión
                        </a>
                        <a href="{{ route('register') }}" class="text-center bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 rounded-lg text-xs shadow-md">
                            Registrarse
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="bg-emerald-600/20 border border-emerald-500 text-emerald-300 p-4 rounded-lg mb-6 shadow-md">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- PIE DE PÁGINA -->
    <footer class="bg-slate-800 border-t border-slate-700 py-6 text-xs text-slate-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4">
            <p>
                CorRol &copy; {{ date('Y') }} — Gestor Ágil de Campañas de Rol | Desarrollado por 
                <a href="https://ocanyaweb.es/presentacion/contacto" target="_blank" class="text-slate-300 hover:text-indigo-400 font-semibold underline">
                    Ricardo Ocaña
                </a>
            </p>
            <div class="flex items-center space-x-4">
                <a href="{{ route('legal') }}" class="text-slate-300 hover:text-indigo-400 transition-colors">
                    Aviso Legal &amp; Privacidad
                </a>
                <span>&bull;</span>
                <span class="text-slate-300">Licencia MIT</span>
            </div>
        </div>
    </footer>
    
    <script>
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
</body>
</html>