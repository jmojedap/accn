<!DOCTYPE html>
<html lang="es">
<head>
    <?= view('templates/easypml/main/head') ?>
</head>
    <body>
        <?= view('templates/easypml/main/navbar') ?>
        <div class="container">
            <div class="header">
                <h2 id="page-title">
                    <?php if ( isset($backLink) && isset($pageTitle) ) : ?>
                        <a class="btn-back-link only-lg" href="<?= base_url($backLink) ?>">
                            <i class="fa fa-arrow-left"></i>
                        </a>
                        <?= $pageTitle ?>
                    <?php endif; ?>
                </h1>
                <?php if ( isset($nav2) ): ?>
                    <div id="nav2">
                        <?= view($nav2) ?>
                    </div>
                <?php endif ?>
            </div>
            <div class="text-center my-3" id="loadingIndicator" style="display: none;">
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div id="viewA">
                <?= view($viewA); ?>
            </div>
        </div>
    </body>
</html>