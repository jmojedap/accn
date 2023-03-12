<?php
    //Evitar errores de definición de variables e índices de arrays, 2013-12-07
        ini_set('display_errors', 1);
        ini_set('error_reporting', E_ERROR);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?= view('templaes/admin_pml_5/main/head') ?>
    </head>

    <body class="start_body">
        <div class="start_container">
            <a href="<?= URL_APP ?>"><img class="w240p mb-2" src="<?= URL_BRAND ?>logo-start.png" alt="Logo aplicación"></a>
            <?= view($view_a); ?>
            <a href="<?= base_url('start') ?>" class="btn btn-light w120p">
                <i class="fa fa-home"></i> Inicio
            </a>
        </div>
        <footer class="fixed-bottom start_footer">
            © 2022 &middot; jmojedap &middot; Colombia
        </footer>    
    </body>
</html>