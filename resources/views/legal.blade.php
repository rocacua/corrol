@extends('layouts.app')

@section('title', 'Información Legal y Privacidad - CorRol')

@section('content')
<div class="max-w-4xl mx-auto bg-slate-800 p-8 rounded-xl border border-slate-700 shadow-2xl space-y-8 text-slate-300 text-sm leading-relaxed">
    
    <div>
        <h1 class="text-3xl font-bold text-indigo-400 mb-2">⚖️ Términos de Uso y Aviso Legal</h1>
        <p class="text-xs text-slate-300">Última actualización: {{ date('Y') }}</p>
    </div>

    <!-- 1. Naturaleza de la Plataforma -->
    <section class="space-y-3">
        <h2 class="text-xl font-semibold text-slate-100">1. Naturaleza y Propósito</h2>
        <p>
            <strong>CorRol</strong> es una plataforma web desarrollada sin ánimo de lucro orientada a facilitar la organización y consulta de campañas y recursos para la comunidad de juegos de rol.
        </p>
    </section>

    <!-- 2. Propiedad Intelectual y Contenido de Usuarios -->
    <section class="space-y-3">
        <h2 class="text-xl font-semibold text-slate-100">2. Propiedad Intelectual y Recursos Subidos</h2>
        <p>
            Los materiales, enlaces y documentos publicados por los usuarios pertenecen a sus respectivos autores y editoriales. CorRol actúa únicamente como intermediario técnico de almacenamiento y organización.
        </p>
        <p>
            Si usted es titular de derechos de autor de algún material indexado o subido sin su consentimiento, puede solicitar su retirada inmediata a través de los canales de contacto del sitio web.
        </p>
    </section>

    <!-- 3. Privacidad y Protección de Datos (RGPD) -->
    <section class="space-y-3">
        <h2 class="text-xl font-semibold text-slate-100">3. Privacidad y Protección de Datos</h2>
        <p>
            En cumplimiento del Reglamento General de Protección de Datos (RGPD), se informa que los datos recabados en el registro (nombre de usuario y correo electrónico) son utilizados exclusivamente para la autenticación y gestión de sus recursos en la plataforma.
        </p>
        <p>
            No se ceden datos a terceros con fines comerciales ni se utilizan cookies de seguimiento o publicidad.
        </p>
    </section>

    <!-- 4. Licencia del Software -->
    <section class="space-y-3 border-t border-slate-700 pt-6">
        <h2 class="text-xl font-semibold text-slate-100">4. Licencia del Código Fuente</h2>
        <p>
            El código fuente de esta aplicación está publicado bajo la licencia de código abierto <strong>MIT License</strong> &copy; {{ date('Y') }} Ricardo Ocaña Gasco.
        </p>
    </section>

    <div class="pt-4 text-center">
        <a href="{{ route('resources.index') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-2.5 rounded-lg text-xs shadow transition-colors">
            ← Volver a CorRol
        </a>
    </div>

</div>
@endsection