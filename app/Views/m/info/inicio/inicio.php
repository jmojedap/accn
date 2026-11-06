<main class="multisits-home">
    <!-- Presentación -->
    <section class="home-hero text-center" aria-labelledby="home-title">
        <p class="home-eyebrow">Multisits · Crea y descubre</p>
        <h1 id="home-title" class="home-title">
            Tu conocimiento merece
            <span>su propio espacio.</span>
        </h1>
        <p class="home-intro mx-auto">
            Publica artículos y archivos en tu propio Sit, o descubre el contenido
            de otros creadores. Todo en Multisits.
        </p>
        <div class="home-actions d-flex flex-wrap justify-content-center gap-3">
            <a href="<?= base_url('m/accounts/signup'); ?>" class="btn home-btn-primary rounded-0">Crear cuenta</a>
            <a href="<?= base_url('m/info/sits'); ?>" class="btn home-btn-secondary rounded-0">Explorar Sits <span aria-hidden="true">&rarr;</span></a>
        </div>
    </section>

    <!-- El espacio de cada creador -->
    <section class="home-section home-section-bordered" aria-labelledby="home-sit-title">
        <div class="row g-4 align-items-start mb-4 mb-lg-5">
            <div class="col-lg-5">
                <p class="home-eyebrow">Tu propio Sit</p>
                <h2 id="home-sit-title" class="home-section-title">Lo que haces,<br>en un solo lugar.</h2>
            </div>
            <div class="col-lg-6 offset-lg-1">
                <p class="home-copy mb-0">
                    Un <strong>Sit</strong> es tu micrositio dentro de Multisits: un espacio para
                    presentar tu trabajo, reunir tus enlaces y compartir lo que sabes.
                    Un perfil propio para conectar con tu audiencia.
                </p>
            </div>
        </div>
        <div class="row g-3 g-lg-4">
            <div class="col-sm-6 col-lg-3">
                <div class="home-feature h-100">
                    <i class="far fa-user home-icon" aria-hidden="true"></i>
                    <h3 class="home-card-title">Tu perfil</h3>
                    <p>Cuenta quién eres, tu experiencia y lo que te mueve.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="home-feature h-100">
                    <i class="fas fa-link home-icon" aria-hidden="true"></i>
                    <h3 class="home-card-title">Tus enlaces</h3>
                    <p>Reúne tus redes, tu portafolio y tus proyectos.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="home-feature h-100">
                    <i class="far fa-image home-icon" aria-hidden="true"></i>
                    <h3 class="home-card-title">Tu identidad</h3>
                    <p>Dale tu estilo con una foto y una portada.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="home-feature h-100">
                    <i class="far fa-folder-open home-icon" aria-hidden="true"></i>
                    <h3 class="home-card-title">Tu contenido</h3>
                    <p>Organiza tus artículos y archivos en un catálogo.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Publicación y acceso -->
    <section class="home-section home-publishing" aria-labelledby="home-publish-title">
        <div class="row g-4 g-lg-5 align-items-center">
            <div class="col-lg-6">
                <p class="home-eyebrow">Comparte a tu manera</p>
                <h2 id="home-publish-title" class="home-section-title">Publica tu contenido.<br>Dale valor a lo que sabes.</h2>
                <p class="home-copy">
                    Comparte contenido público o elige qué publicaciones se desbloquean
                    con créditos. Tú decides cómo dar acceso a tu trabajo.
                </p>
                <a href="<?= base_url('m/info/suscriptores'); ?>" class="home-text-link">Cómo funcionan los créditos <span aria-hidden="true">&rarr;</span></a>
            </div>
            <div class="col-lg-5 offset-lg-1">
                <ul class="home-formats list-unstyled mb-0">
                    <li>
                        <span class="home-format-number" aria-hidden="true">01</span>
                        <div>
                            <h3 class="home-card-title">Artículos</h3>
                            <p>Ideas, guías y conocimiento para leer.</p>
                        </div>
                    </li>
                    <li>
                        <span class="home-format-number" aria-hidden="true">02</span>
                        <div>
                            <h3 class="home-card-title">Archivos descargables</h3>
                            <p>Recursos para llevar de la idea a la práctica.</p>
                        </div>
                    </li>
                    <li>
                        <span class="home-format-number" aria-hidden="true">03</span>
                        <div>
                            <h3 class="home-card-title">Documentos y libros</h3>
                            <p>Contenido para explorar un tema a fondo.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Dos formas de participar -->
    <section class="home-section" aria-labelledby="home-audience-title">
        <div class="home-section-heading text-center mx-auto">
            <p class="home-eyebrow">Un espacio compartido</p>
            <h2 id="home-audience-title" class="home-section-title">Para compartir. Para descubrir.</h2>
            <p class="home-copy mb-0">Acerca lo que sabes a otras personas y encuentra nuevas ideas.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="home-audience h-100">
                    <h3 class="home-audience-title">Si creas contenido</h3>
                    <p class="home-copy">Enfócate en lo que quieres compartir.</p>
                    <ul class="home-benefits list-unstyled mb-0">
                        <li>Publica desde tu Sit, sin desarrollar una plataforma.</li>
                        <li>Decide los precios y el acceso a tus publicaciones.</li>
                        <li>Genera ingresos cuando desbloquean tu contenido.</li>
                        <li>Comparte tu perfil y tu catálogo con tu audiencia.</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="home-audience h-100">
                    <h3 class="home-audience-title">Si buscas contenido</h3>
                    <p class="home-copy">Elige lo que te interesa, a tu ritmo.</p>
                    <ul class="home-benefits list-unstyled mb-0">
                        <li>Descubre distintos creadores en un mismo lugar.</li>
                        <li>Usa tus créditos cuando quieras: no vencen.</li>
                        <li>Conserva el acceso al contenido que desbloqueas.</li>
                        <li>Sin suscripciones ni cobros automáticos.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Invitación final -->
    <section class="home-cta text-center" aria-labelledby="home-start-title">
        <p class="home-eyebrow">Empieza en Multisits</p>
        <h2 id="home-start-title" class="home-section-title">Tu próxima idea tiene un lugar aquí.</h2>
        <p class="home-copy mx-auto">Crea tu cuenta para publicar lo que sabes o descubrir algo nuevo.</p>
        <a href="<?= base_url('m/accounts/signup'); ?>" class="btn home-btn-primary rounded-0">Crear cuenta</a>
    </section>
</main>
