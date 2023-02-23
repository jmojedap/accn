<?php
//Evitar errores de definición de variables e índices de arrays, 2013-12-07
    ini_set('display_errors', 1);
    ini_set('error_reporting', E_ERROR);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?= view('templates/easypml/main/head') ?>
</head>

<body>
    <main>
        <div class="sidebar only-lg">
            <?= view('templates/easypml/main/sidebar') ?>
        </div>
        <div class="main-content">
            <?php if ( isset($backLink) ) : ?>
            <div id="backLink" class="only-sm mb-2">
                <a class="btn btn-light" href="<?= base_url($backLink) ?>">
                    <i class="fa fa-arrow-left"></i>
                </a>
            </div>
            <?php endif; ?>
            <div class="header">
                <?php if ( isset($nav2) ): ?>
                <div id="nav2">
                    <?= view($nav2) ?>
                </div>
                <?php endif ?>
            </div>
            <?= view($viewA); ?>
        </div>
    </main>
</body>

</html>