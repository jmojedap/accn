<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<?= view('m/info/suscriptores/style'); ?>

<div class="sits-wrapper">
    <div class="relative-content">
        
        <!-- HERO -->
        <section class="container pt-5 pb-5 text-center">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h1 class="minimal-title large mb-4 fade-in-up">
                        Accede a contenido digital<br>
                        <span>de calidad en multiSits</span>
                    </h1>
                    <p class="section-subtitle fade-in-up" style="transition-delay: 0.1s;">
                        Descubre artículos y archivos creados por expertos. Compra crédito, desbloquea contenido y accede a él cuando lo necesites.
                    </p>
                    <div class="d-flex justify-content-center mb-5 fade-in-up" style="transition-delay: 0.2s;">
                        <a href="#" class="btn-firm-primary">
                            Crear cuenta
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- CÓMO FUNCIONA -->
        <section class="container py-section">
            <div class="text-center mb-5">
                <h2 class="minimal-title fade-in-up">¿Cómo funciona Multisits?</h2>
                <div class="separator-line fade-in-up"></div>
                <p class="text-secondary fade-in-up">Un modelo simple, transparente y sin complicaciones.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4 fade-in-up" style="transition-delay: 0.1s;">
                    <div class="minimal-card">
                        <i class="bi bi-search icon-circle"></i>
                        <h5 class="h5 fw-bold mb-3">Explora contenidos</h5>
                        <p class="text-secondary small mb-0">
                            Navega por distintos <strong>Sits</strong> y descubre artículos y archivos públicos o exclusivos.
                        </p>
                    </div>
                </div>

                <div class="col-md-4 fade-in-up" style="transition-delay: 0.2s;">
                    <div class="minimal-card">
                        <i class="bi bi-wallet2 icon-circle"></i>
                        <h5 class="h5 fw-bold mb-3">Compra crédito</h5>
                        <p class="text-secondary small mb-0">
                            Recarga crédito desde $19.000 COP. Tu saldo no vence y lo usas cuando quieras.
                        </p>
                    </div>
                </div>

                <div class="col-md-4 fade-in-up" style="transition-delay: 0.3s;">
                    <div class="minimal-card">
                        <i class="bi bi-unlock icon-circle"></i>
                        <h5 class="h5 fw-bold mb-3">Desbloquea contenido</h5>
                        <p class="text-secondary small mb-0">
                            Usa tu crédito para acceder a contenidos exclusivos y guardarlos en tu cuenta.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CRÉDITOS -->
        <section class="bg-subtle py-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 fade-in-up">
                        <h2 class="minimal-title mb-3">¿Qué son los créditos?</h2>
                        <div style="height: 4px; width: 60px; background: #F23F3E; margin-bottom: 2rem; border-radius: 2px;"></div>
                        <p class="mb-4 text-secondary">
                            Los créditos son un saldo prepago que te permite desbloquear contenidos digitales en Multisits sin realizar pagos repetitivos.
                        </p>
                        <ul class="list-unstyled">
                            <li class="mb-3 d-flex align-items-center"><i class="bi bi-check-circle-fill text-brand me-3 fs-5"></i>No tienen fecha de vencimiento</li>
                            <li class="mb-3 d-flex align-items-center"><i class="bi bi-check-circle-fill text-brand me-3 fs-5"></i>Se usan en cualquier Sit</li>
                            <li class="mb-3 d-flex align-items-center"><i class="bi bi-check-circle-fill text-brand me-3 fs-5"></i>Compra mínima: $19.000 COP</li>
                        </ul>
                    </div>

                    <div class="col-md-6 text-center fade-in-up" style="transition-delay: 0.2s;">
                        <i class="bi bi-credit-card" style="font-size: 8rem; color: #F23F3E; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </section>

        <!-- ACCESO A CONTENIDOS -->
        <section class="container py-section">
            <div class="text-center mb-5 fade-in-up">
                <h2 class="minimal-title">Acceso a los contenidos</h2>
                <div class="separator-line"></div>
                <p class="text-secondary">Claridad total sobre qué obtienes al desbloquear un contenido.</p>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-4 fade-in-up" style="transition-delay: 0.1s;">
                    <div class="minimal-card">
                        <i class="bi bi-globe icon-circle"></i>
                        <h5 class="h5 fw-bold mb-3">Acceso público</h5>
                        <p class="text-secondary small mb-0">
                            Contenidos disponibles para cualquier visitante, sin registro ni pago.
                        </p>
                    </div>
                </div>

                <div class="col-md-4 fade-in-up" style="transition-delay: 0.2s;">
                    <div class="minimal-card">
                        <i class="bi bi-person-check icon-circle"></i>
                        <h5 class="h5 fw-bold mb-3">Acceso registrado</h5>
                        <p class="text-secondary small mb-0">
                            Contenidos disponibles solo para usuarios con cuenta en Multisits.
                        </p>
                    </div>
                </div>

                <div class="col-md-4 fade-in-up" style="transition-delay: 0.3s;">
                    <div class="minimal-card">
                        <i class="bi bi-lock icon-circle"></i>
                        <h5 class="h5 fw-bold mb-3">Acceso exclusivo</h5>
                        <p class="text-secondary small mb-0">
                            Contenidos que se desbloquean usando créditos y quedan asociados a tu cuenta.
                        </p>
                    </div>
                </div>
            </div>

            <div class="alert fade-in-up" style="background-color: #fff5f5; border: 1px solid #F23F3E; color: #1a202c; border-radius: 8px;">
                <i class="bi bi-info-circle-fill text-brand me-2"></i>
                <strong>Nota:</strong> Al desbloquear un contenido adquieres un <strong>derecho de acceso personal y permanente</strong>.
                No implica propiedad ni derechos de redistribución.
            </div>
        </section>

        <!-- BENEFICIOS -->
        <section class="bg-subtle py-section">
            <div class="container">
                <div class="text-center mb-5 fade-in-up">
                    <h2 class="minimal-title">¿Por qué usar Multisits?</h2>
                    <div class="separator-line"></div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6 fade-in-up" style="transition-delay: 0.1s;">
                        <div class="bg-white p-4 rounded-3 border border-light shadow-sm h-100 d-flex align-items-center">
                            <i class="bi bi-lightning-charge-fill text-brand fs-3 me-3"></i>
                            <span class="fw-bold text-dark">Acceso rápido y centralizado a múltiples creadores</span>
                        </div>
                    </div>
                    <div class="col-md-6 fade-in-up" style="transition-delay: 0.2s;">
                        <div class="bg-white p-4 rounded-3 border border-light shadow-sm h-100 d-flex align-items-center">
                            <i class="bi bi-shield-check-fill text-brand fs-3 me-3"></i>
                            <span class="fw-bold text-dark">Pagos simples y seguros</span>
                        </div>
                    </div>
                    <div class="col-md-6 fade-in-up" style="transition-delay: 0.3s;">
                        <div class="bg-white p-4 rounded-3 border border-light shadow-sm h-100 d-flex align-items-center">
                            <i class="bi bi-infinity text-brand fs-3 me-3"></i>
                            <span class="fw-bold text-dark">Acceso permanente a contenidos desbloqueados</span>
                        </div>
                    </div>
                    <div class="col-md-6 fade-in-up" style="transition-delay: 0.4s;">
                        <div class="bg-white p-4 rounded-3 border border-light shadow-sm h-100 d-flex align-items-center">
                            <i class="bi bi-ui-checks text-brand fs-3 me-3"></i>
                            <span class="fw-bold text-dark">Sin suscripciones ni cobros automáticos</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA FINAL -->
        <section class="py-5 mt-5">
            <div class="container">
                <div class="text-center py-5 border rounded-3 bg-white shadow-sm position-relative overflow-hidden fade-in-up">
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 6px; background: #F23F3E;"></div>
                    
                    <h2 class="h2 fw-bold mb-3 text-dark">Empieza hoy en Multisits</h2>
                    <p class="section-subtitle mb-4">Crea tu cuenta, compra crédito y accede a contenido digital creado por expertos.</p>
                    <a href="#" class="btn-firm-primary">
                        Crear cuenta gratis
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
