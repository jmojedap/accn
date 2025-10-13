<style>
/* Definición de colores personalizados */
:root {
    --bs-main-color: #F43547;
    /* NUEVO COLOR PRINCIPAL: Crimson Vibrante */
    --bs-amber-500: #F59E0B;
    /* Color de acento (Ámbar) - Mantenido */
    /* Sobrescribiendo variables de Bootstrap para usar los colores específicos */
    --bs-primary: var(--bs-main-color);
    --bs-warning: var(--bs-amber-500);
}

/* Estilo del Hero con gradiente (ajustado a tonos de rosa/rojo claro) */
.hero-bg {
    /*background: linear-gradient(135deg, #FDE7E8 0%, #FCCCD0 100%);*/
}

/* Color de fondo para la sección de confianza (neutral para resaltar el rojo) */
.bg-light-neutral {
    background-color: #F5F5F5;
}

/* Estilos y transiciones para las tarjetas de características */
.feature-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Clase para animaciones de entrada */
.fade-in-up {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.fade-in-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Ajuste para el color de los botones primarios al hacer hover */
.btn-primary:hover {
    background-color: #D32F2F !important;
    /* Un rojo un poco más oscuro */
    border-color: #D32F2F !important;
}
</style>

<!-- Sección Hero (Destacada) -->
<section class="hero-bg py-5 py-md-7">
    <div class="container text-center">
        <!-- El texto "¡Simple y Poderosa!" ahora es rojo/primary -->
        <h1 class="display-4 fw-bolder text-dark mb-3 fade-in-up">
            <span class="d-block">Tu Presencia Profesional <span class="text-primary">¡Simple y Poderosa!</span></span>
        </h1>
        <p class="h5 text-secondary mb-4 mx-auto fade-in-up" style="max-width: 600px; transition-delay: 0.2s;">
            MultiSits te da el sitio web de una página que necesitas para que tus clientes te encuentren, agenden y
            paguen, sin códigos ni complicaciones.
        </p>
        <!-- Call to Action Principal - Ahora es un botón rojo/primary -->
        <button
            class="btn btn-primary text-white fw-bolder btn-lg py-3 px-5 rounded-3 shadow-lg transition-transform hover-scale-105 fade-in-up"
            style="transition-delay: 0.4s;">
            Crea tu MultiSit en 5 Minutos
        </button>
        <p class="mt-3 text-sm text-secondary fade-in-up" style="transition-delay: 0.6s;">
            Perfecto para asesores, profesores, tutores y profesionales independientes.
        </p>

        <!-- Mockup de Pantalla (Placeholder) - El placeholder ahora usa el color rojo -->
        <div class="mt-5 fade-in-up" style="transition-delay: 0.8s;">
            <img src="https://placehold.co/1000x550/F43547/FFFFFF?text=Mockup+Web+MultiSits"
                alt="Captura de pantalla del sitio web de MultiSits"
                class="img-fluid rounded-3 shadow-lg border border-5 border-white transition-transform hover-scale-102" />
        </div>
    </div>
</section>

<!-- Sección de Servicios (¿Qué incluye?) -->
<section id="servicios" class="py-5 py-md-7 bg-white">
    <div class="container">
        <h2 class="display-6 fw-bolder text-center text-dark mb-5 fade-in-up">
            Todo lo que tu cliente busca, <span class="text-warning">en un solo lugar</span>.
        </h2>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">

            <!-- Las cartas de servicio ahora tienen iconos rojos/primary -->
            <!-- Servicio 1: Reservas y Agenda -->
            <div class="col">
                <div class="feature-card bg-white p-4 rounded-3 shadow-sm border border-light h-100 fade-in-up">
                    <div class="fs-2 text-primary mb-3">
                        📅
                    </div>
                    <h3 class="h5 fw-bold mb-2 text-dark">Reserva de Atenciones</h3>
                    <p class="text-secondary">Deja que tus clientes reserven citas o clases directamente en tu sitio.
                        Sincroniza tu agenda y olvídate de los mensajes de ida y vuelta.</p>
                </div>
            </div>

            <!-- Servicio 2: Catálogo y Servicios -->
            <div class="col">
                <div class="feature-card bg-white p-4 rounded-3 shadow-sm border border-light h-100 fade-in-up"
                    style="transition-delay: 0.1s;">
                    <div class="fs-2 text-primary mb-3">
                        📚
                    </div>
                    <h3 class="h5 fw-bold mb-2 text-dark">Catálogo de Servicios</h3>
                    <p class="text-secondary">Muestra tus servicios, tarifas y portafolio de trabajo de forma elegante.
                        Impacta con descripciones claras y fotos profesionales.</p>
                </div>
            </div>

            <!-- Servicio 3: Información Completa -->
            <div class="col">
                <div class="feature-card bg-white p-4 rounded-3 shadow-sm border border-light h-100 fade-in-up"
                    style="transition-delay: 0.2s;">
                    <div class="fs-2 text-primary mb-3">
                        👤
                    </div>
                    <h3 class="h5 fw-bold mb-2 text-dark">Perfil de Experto</h3>
                    <p class="text-secondary">Una página donde tu experiencia y trayectoria brillan. Descripción,
                        horarios de atención visibles y tus datos de contacto al instante.</p>
                </div>
            </div>

            <!-- Servicio 4: Carga de Fotos -->
            <div class="col">
                <div class="feature-card bg-white p-4 rounded-3 shadow-sm border border-light h-100 fade-in-up"
                    style="transition-delay: 0.3s;">
                    <div class="fs-2 text-primary mb-3">
                        📸
                    </div>
                    <h3 class="h5 fw-bold mb-2 text-dark">Galería Impecable</h3>
                    <p class="text-secondary">Sube tus mejores fotos de trabajos, instalaciones o clases. Una imagen
                        vale más que mil palabras para inspirar confianza.</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Sección de Testimonios/Confianza - Ahora con fondo neutral -->
<section id="por-que" class="py-5 py-md-7 bg-light-neutral">
    <div class="container">
        <h2 class="display-6 fw-bolder text-dark text-center mb-5 fade-in-up">
            ¿Por qué MultiSits es tu <span class="text-primary">mejor herramienta</span>?
        </h2>

        <div class="row g-5 align-items-center">

            <!-- Bloque de Texto -->
            <div class="col-md-6 text-start space-y-4 fade-in-up">

                <!-- Las líneas de borde destacadas siguen siendo Ámbar/Warning -->
                <div class="p-3 bg-white rounded-3 shadow-sm border-start border-warning border-4 mb-2">
                    <h4 class="h5 fw-bold text-dark">Cero Complicaciones Técnicas</h4>
                    <p class="text-secondary mb-0">No necesitas saber de código. Nuestro editor drag-and-drop es tan
                        fácil como escribir un correo.</p>
                </div>

                <div class="p-3 bg-white rounded-3 shadow-sm border-start border-warning border-4 mb-2 fade-in-up"
                    style="transition-delay: 0.1s;">
                    <h4 class="h5 fw-bold text-dark">Enfocado en Convertir</h4>
                    <p class="text-secondary mb-0">Cada sección está diseñada para guiar a tu cliente a una acción:
                        reservar, contactar o ver tu trabajo.</p>
                </div>

                <div class="p-3 bg-white rounded-3 shadow-sm border-start border-warning border-4 mb-2 fade-in-up"
                    style="transition-delay: 0.2s;">
                    <h4 class="h5 fw-bold text-dark">Compartible en Segundos</h4>
                    <p class="text-secondary mb-0">Obtén un link único y compártelo en WhatsApp, redes sociales o tu
                        tarjeta de presentación. ¡Listo para brillar!</p>
                </div>
            </div>

            <!-- Bloque de Imagen/Gráfico - Ahora con el color rojo principal -->
            <div class="col-md-6 text-center fade-in-up" style="transition-delay: 0.3s;">
                <img src="https://placehold.co/400x400/F43547/FFFFFF?text=Foco+en+Tu+Profesión"
                    alt="Gráfico de enfoque profesional"
                    class="img-fluid rounded-circle shadow-lg transition-transform hover-rotate-2" />
            </div>

        </div>
    </div>
</section>

<!-- Sección de Llamada a la Acción Final - Fondo ahora es rojo/primary -->
<section id="precios" class="py-5 py-md-7 bg-primary">
    <div class="container text-center">
        <h2 class="display-5 fw-bolder text-white mb-3">
            ¡No más tarjetas de presentación aburridas!
        </h2>
        <p class="h4 text-light mb-4 mx-auto" style="max-width: 600px;">
            Lleva tu práctica profesional al siguiente nivel hoy mismo.
        </p>

        <!-- Call to Action Principal - Botón de acento Ámbar/Warning -->
        <button
            class="btn btn-warning text-dark fw-bolder display-6 py-3 px-5 rounded-3 shadow-lg transition-transform hover-scale-105">
            Empezar Mi MultiSit GRATIS
        </button>
    </div>
</section>

<!-- JavaScript para animaciones de Scroll (Mantenido) -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const elements = document.querySelectorAll('.fade-in-up');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-visible');
                observer.unobserve(entry.target); // Dejar de observar después de animar
            }
        });
    }, {
        rootMargin: '0px',
        threshold: 0.1 // Activar cuando el 10% del elemento es visible
    });

    elements.forEach(element => {
        observer.observe(element);
    });
});
</script>