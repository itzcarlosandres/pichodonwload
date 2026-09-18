@extends('layouts.web')

@section('title', 'Aviso Legal DMCA & Propiedad Intelectual — ' . \App\Models\Setting::get('site_name', 'ROMHUB'))

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-8 space-y-8">

    <!-- Header Banner -->
    <div class="bg-white border-2 border-[#1E1E1E] rounded-3xl p-6 sm:p-10 relative overflow-hidden shadow-sm">
        <div class="relative z-10 max-w-3xl space-y-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FDF2F2] border border-[#FCA5A5] text-[#CE2D2D] text-xs font-mono font-bold">
                <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                <span>POLÍTICA DE PROPIEDAD INTELECTUAL</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-[#18181B] tracking-tight font-sans">
                Digital Millennium Copyright Act (DMCA)
            </h1>
            <p class="text-sm text-gray-600 leading-relaxed font-sans">
                Aviso sobre preservación digital, derechos de autor y procedimiento para la solicitud de retirada de contenidos protegidos.
            </p>
        </div>
    </div>

    <!-- Content Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Detailed Legal Statements -->
        <div class="lg:col-span-2 space-y-6 text-xs text-gray-700 leading-relaxed font-sans">
            
            <!-- Section 1: Declaración de Preservación -->
            <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-6 space-y-3 shadow-sm">
                <h2 class="text-sm font-bold text-[#18181B] font-mono uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="archive" class="w-4 h-4 text-[#CE2D2D]"></i>
                    1. Finalidad de Archivo Histórico y Preservación
                </h2>
                <p>
                    Este portal opera sin fines comerciales con el propósito primordial de la preservación de software y patrimonio cultural digital de plataformas de entretenimiento discontinuadas, siguiendo estándares de catalogación de comunidades como <em>No-Intro</em>, <em>Redump</em> y <em>TOSEC</em>.
                </p>
                <p>
                    Todos los nombres de productos, marcas registradas, carátulas y logotipos pertenecen a sus respectivos desarrolladores y editores (*publishers*). La mención de estas marcas se realiza únicamente con fines informativos y de identificación histórica.
                </p>
            </div>

            <!-- Section 2: Cumplimiento de la DMCA -->
            <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-6 space-y-3 shadow-sm">
                <h2 class="text-sm font-bold text-[#18181B] font-mono uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="scale" class="w-4 h-4 text-amber-600"></i>
                    2. Cumplimiento con la Ley DMCA (17 U.S.C. § 512)
                </h2>
                <p>
                    Es nuestra política estricta responder con celeridad ante cualquier notificación formal de infracción de derechos de autor que cumpla con los lineamientos del <em>Title 17, United States Code, Section 512(c)(3)</em>.
                </p>
                <p>
                    Si usted es titular legítimo de derechos de autor o un agente debidamente autorizado y considera que algún contenido indexado vulnera su propiedad intelectual, procederemos a inhabilitar el acceso o retirar los enlaces en un plazo no mayor a <strong>24 a 48 horas hábiles</strong> tras la recepción formal de su reclamo.
                </p>
            </div>

            <!-- Section 3: Requisitos para una Notificación Válida -->
            <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-6 space-y-3 shadow-sm">
                <h2 class="text-sm font-bold text-[#18181B] font-mono uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="file-check" class="w-4 h-4 text-emerald-600"></i>
                    3. Requisitos de la Solicitud de Retirada (Takedown Notice)
                </h2>
                <p class="text-gray-500">Para procesar su solicitud de inmediato, asegúrese de incluir:</p>
                <ul class="space-y-2 list-disc list-inside text-gray-700 pl-2">
                    <li>Firma física o electrónica de la persona autorizada para actuar en nombre del titular del copyright.</li>
                    <li>Identificación clara de la obra u obras protegidas presuntamente infringidas.</li>
                    <li>Enlace exacto (URL completa) del contenido o ficha en nuestro sitio web que desea sea retirado.</li>
                    <li>Información de contacto directa: nombre completo, cargo/organización, dirección de correo electrónico institucional y número telefónico.</li>
                    <li>Una declaración formal de buena fe que certifique que el uso del material denunciado no está autorizado por el titular, su agente o la legislación vigente.</li>
                    <li>Una declaración jurada, bajo pena de perjurio, de que los datos provistos en la notificación son fidedignos y que cuenta con la debida autorización.</li>
                </ul>
            </div>

        </div>

        <!-- Right 1 Col: Direct Action / Contact Info -->
        <div class="space-y-6">
            
            <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-6 space-y-4 shadow-sm">
                <h3 class="text-xs font-mono font-bold uppercase text-[#18181B] tracking-wider flex items-center gap-2 border-b border-[#E5E0D8] pb-3">
                    <i data-lucide="mail" class="w-4 h-4 text-[#CE2D2D]"></i>
                    Canal Oficial de Reclamaciones
                </h3>
                <p class="text-xs text-gray-600 leading-relaxed">
                    Remita sus solicitudes oficiales firmadas al buzón designado para tramitación de DMCA:
                </p>

                <div class="p-3.5 rounded-xl bg-[#FAF7F2] border border-[#DDD6CB] space-y-1">
                    <span class="text-[10px] font-mono uppercase text-gray-500 block font-bold">Correo de Notificaciones</span>
                    <a href="mailto:{{ $contactEmail }}" class="text-xs font-mono font-bold text-[#CE2D2D] hover:underline block truncate">
                        {{ $contactEmail }}
                    </a>
                </div>

                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-sans space-y-1">
                    <div class="font-bold flex items-center gap-1.5 font-mono text-[11px]">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Tiempo de Respuesta</span>
                    </div>
                    <p class="text-[11px] text-amber-900/90 leading-normal">
                        Las solicitudes que incluyan las URLs exactas y documentación son procesadas prioritariamente en un plazo de 24 a 48 horas.
                    </p>
                </div>

                <a href="{{ route('contact') }}" class="w-full py-2.5 px-4 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white text-xs font-bold font-sans text-center block transition-colors shadow-sm">
                    Ir al Formulario de Contacto
                </a>
            </div>

            <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-6 space-y-3 shadow-sm">
                <h3 class="text-xs font-mono font-bold uppercase text-[#18181B] tracking-wider border-b border-[#E5E0D8] pb-2">
                    Aviso a Usuarios
                </h3>
                <p class="text-xs text-gray-600 leading-relaxed">
                    Los usuarios del sitio reconocen que las copias de seguridad aquí documentadas corresponden a proyectos de preservación histórica y software que ya no se comercializa activamente en tiendas de primera mano.
                </p>
            </div>

        </div>

    </div>

</main>
@endsection
