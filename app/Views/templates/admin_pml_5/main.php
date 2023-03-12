<?php
    //Evitar errores de definición de variables e índices de arrays, 2013-12-07
        ini_set('display_errors', 1);
        ini_set('error_reporting', E_ERROR);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?= view('templates/admin_pml_5/main/head') ?>
    </head>
    <body class="skin-admin">
        <div class="wrapper">
            <?= view('templates/admin_pml_5/main/header'); ?>
            <?= view('templates/admin_pml_5/main/sidebar'); ?>

            <div class="content-wrapper">
                <section class="content">
                    <div id="nav2">
                        <?php if ( ! is_null($nav2) ) { ?>
                            <?= view($nav2); ?>
                        <?php } ?>
                    </div>

                    <div id="nav3">
                        <?php if ( ! is_null($nav_3) ) { ?>
                            <?= view($nav_3); ?>
                        <?php } ?>
                    </div>
                    
                    <div class="text-center my-3" id="loadingIndicator" style="display: none;">
                        <div class="spinner-border text-secondary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                    </div>
                    <div id="viewA">
                        <?= view($viewA); ?>
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>