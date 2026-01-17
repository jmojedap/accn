<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<?= view('m/info/inicio/style'); ?>

<div class="sits-wrapper">
    <div class="relative-content">
        
        <!-- HERO -->
        <section class="container pt-5 pb-5 text-center">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h1 class="minimal-title large mb-4 fade-in-up">
                        Un solo lugar para crear,<br>
                        <span>publicar y acceder a contenido digital</span>
                    </h1>
                    <p class="section-subtitle fade-in-up" style="transition-delay: 0.1s;">
                        Multisits conecta creadores y usuarios a través de micro-sitios donde el conocimiento, los archivos y los artículos tienen valor.
                    </p>
                    <div class="d-flex justify-content-center gap-3 mb-5 fade-in-up" style="transition-delay: 0.2s;">
                        <a href="<?= base_url('m/accounts/signup'); ?>" class="btn-firm-primary">Crear cuenta</a>
                        <a href="<?= base_url('m/info/sits'); ?>" class="btn-firm-secondary">Explorar contenidos</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- QUÉ ES MULTISITS -->
        <section class="bg-subtle py-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 fade-in-up">
                        <h2 class="minimal-title mb-3">¿Qué es Multisits?</h2>
                        <div class="separator-line" style="margin: 0 0 2rem 0;"></div>
                        <p class="mb-3 text-secondary">
                            Multisits es una plataforma donde profesionales, creadores y especialistas pueden
                            publicar contenido digital y monetizarlo de forma simple.
                        </p>
                        <p class="text-secondary">
                            Cada creador cuenta con su propio espacio dentro de la plataforma, llamado <strong class="text-brand">Sit</strong>,
                            desde el cual presenta su perfil y comparte contenidos con su audiencia.
                        </p>
                    </div>
                    <div class="col-md-6 text-center fade-in-up" style="transition-delay: 0.2s;">
                        <i class="bi bi-diagram-3" style="font-size: 8rem; color: #F2403D; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </section>

        <!-- QUÉ ES UN SIT -->
        <section class="container py-section">
            <div class="text-center mb-5 fade-in-up">
                <h2 class="minimal-title">Cada creador tiene su propio Sit</h2>
                <div class="separator-line"></div>
                <p class="text-secondary">Un micro-sitio profesional dentro de Multisits.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-3 fade-in-up" style="transition-delay: 0.1s;">
                    <div class="minimal-card">
                        <i class="bi bi-person-badge"></i>
                        <h6 class="h6 fw-bold mt-3">Perfil profesional</h6>
                        <p class="text-secondary small mb-0">Presenta quién eres, tu experiencia y tu enfoque.</p>
                    </div>
                </div>

                <div class="col-md-3 fade-in-up" style="transition-delay: 0.2s;">
                    <div class="minimal-card">
                        <i class="bi bi-link-45deg"></i>
                        <h6 class="h6 fw-bold mt-3">Links relevantes</h6>
                        <p class="text-secondary small mb-0">Comparte enlaces a redes, portafolios o proyectos externos.</p>
                    </div>
                </div>

                <div class="col-md-3 fade-in-up" style="transition-delay: 0.3s;">
                    <div class="minimal-card">
                        <i class="bi bi-image"></i>
                        <h6 class="h6 fw-bold mt-3">Imagen y presentación</h6>
                        <p class="text-secondary small mb-0">Incluye foto, portada y una identidad clara.</p>
                    </div>
                </div>

                <div class="col-md-3 fade-in-up" style="transition-delay: 0.4s;">
                    <div class="minimal-card">
                        <i class="bi bi-collection"></i>
                        <h6 class="h6 fw-bold mt-3">Catálogo de contenidos</h6>
                        <p class="text-secondary small mb-0">Organiza artículos y archivos publicados.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CREAR Y GANAR -->
        <section class="bg-subtle py-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center fade-in-up">
                        <i class="bi bi-currency-dollar" style="font-size: 8rem; color: #F2403D; opacity: 0.8;"></i>
                    </div>
                    <div class="col-md-6 fade-in-up" style="transition-delay: 0.2s;">
                        <h2 class="minimal-title mb-3">Crea contenido y genera ingresos</h2>
                        <div class="separator-line" style="margin: 0 0 2rem 0;"></div>
                        <p class="mb-4 text-secondary">
                            En Multisits puedes publicar:
                        </p>
                        <ul class="list-unstyled">
                            <li class="mb-3 d-flex align-items-center"><i class="bi bi-check-circle-fill text-brand me-3 fs-5"></i>Artículos digitales</li>
                            <li class="mb-3 d-flex align-items-center"><i class="bi bi-check-circle-fill text-brand me-3 fs-5"></i>Archivos descargables</li>
                            <li class="mb-3 d-flex align-items-center"><i class="bi bi-check-circle-fill text-brand me-3 fs-5"></i>Documentos y libros digitales</li>
                        </ul>
                        <p class="text-secondary">
                            Tú defines qué contenido es público y cuál requiere desbloqueo mediante créditos.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- POR QUÉ REGISTRARSE -->
        <section class="container py-section">
            <div class="text-center mb-5 fade-in-up">
                <h2 class="minimal-title">¿Por qué registrarse en Multisits?</h2>
                <div class="separator-line"></div>
            </div>

            <div class="row g-4">
                <!-- CREADORES -->
                <div class="col-md-6 fade-in-up" style="transition-delay: 0.1s;">
                    <div class="minimal-card align-items-start text-start p-5">
                        <h4 class="fw-bold mb-4 d-flex align-items-center">
                            <i class="bi bi-pencil-square me-3 text-brand" style="font-size: 2rem; margin-bottom: 0;"></i>
                            Para creadores
                        </h4>
                        <ul class="list-unstyled text-secondary">
                            <li class="mb-3">✔ Publica contenido sin desarrollar tu propia plataforma</li>
                            <li class="mb-3">✔ Control total sobre precios y accesos</li>
                            <li class="mb-3">✔ Gana dinero por cada contenido desbloqueado</li>
                            <li class="mb-3">✔ Un Sit profesional listo para compartir</li>
                        </ul>
                    </div>
                </div>

                <!-- COMPRADORES -->
                <div class="col-md-6 fade-in-up" style="transition-delay: 0.2s;">
                    <div class="minimal-card align-items-start text-start p-5">
                        <h4 class="fw-bold mb-4 d-flex align-items-center">
                            <i class="bi bi-person-check me-3 text-brand" style="font-size: 2rem; margin-bottom: 0;"></i>
                            Para compradores
                        </h4>
                        <ul class="list-unstyled text-secondary">
                            <li class="mb-3">✔ Acceso a múltiples creadores en un solo lugar</li>
                            <li class="mb-3">✔ Sistema de crédito sin vencimiento</li>
                            <li class="mb-3">✔ Acceso permanente a contenidos desbloqueados</li>
                            <li class="mb-3">✔ Sin suscripciones ni pagos automáticos</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA FINAL -->
        <section class="py-5 mt-5">
            <div class="container">
                <div class="text-center py-5 border rounded-3 bg-white shadow-sm position-relative overflow-hidden fade-in-up">
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 6px; background: #F2403D;"></div>
                    
                    <h2 class="h2 fw-bold mb-3 text-dark">Empieza hoy en Multisits</h2>
                    <p class="section-subtitle mb-4">Regístrate gratis y decide si quieres crear contenido o acceder a él.</p>
                    <a href="<?= base_url('m/accounts/signup'); ?>" class="btn-firm-primary">
                        Crear cuenta
                    </a>
                </div>
            </div>
        </section>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const elements = document.querySelectorAll('.fade-in-up');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    elements.forEach(el => observer.observe(el));
});
</script>
