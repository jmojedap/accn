<?php
    $uri = current_url(true);
?>

<meta charset="UTF-8">
        <title><?= $headTitle ?></title>
        <link rel="shortcut icon" href="<?= URL_BRAND ?>favicon.png" type="image/png"/>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        
        <!-- Font Awesome -->
        <script src="https://kit.fontawesome.com/f45fca298e.js" crossorigin="anonymous"></script>

        <!--JQuery-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <!-- <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script> -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>

        <!-- Bootstrap CSS 5.1.3 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

        <!-- Vue.js -->
        <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.0.0-alpha.1/axios.min.js" integrity="sha512-xIPqqrfvUAc/Cspuj7Bq0UtHNo/5qkdyngx6Vwt+tmbvTLDszzXM0G6c91LXmGrRx8KEPulT+AfOOez+TeVylg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script>const {createApp} = Vue;</script>

        <!-- Toastr: Alertas y Notificaciones -->
        <link href="<?= URL_RESOURCES ?>templates/admin_pml_5/css/skins/skin-blue-toastr.css" rel="stylesheet" type="text/css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
        <script src="<?= URL_RESOURCES ?>templates/admin_pml_5/js/toastr-options.js"></script>

        <!-- Moment.js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.js" integrity="sha256-H9jAz//QLkDOy/nzE9G4aYijQtkLt9FvGmdUTwBk6gs=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/locale/es.js" integrity="sha256-bETP3ndSBCorObibq37vsT+l/vwScuAc9LRJIQyb068=" crossorigin="anonymous"></script>

        <!-- Tema AdminPML -->
        <link href="<?= URL_RESOURCES ?>templates/admin_pml_5/css/admin-pml.css" rel="stylesheet" type="text/css" />
        <link href="<?= URL_RESOURCES ?>templates/admin_pml_5/css/mobile.css" rel="stylesheet" type="text/css" />
        <link href="<?= URL_RESOURCES ?>templates/admin_pml_5/css/skins/skin-dekinder.css" rel="stylesheet" type="text/css" />
        <script src="<?= URL_RESOURCES ?>templates/admin_pml_5/js/app.js"></script>
        <script src="<?= URL_RESOURCES ?>templates/admin_pml_5/js/routing.js"></script>

        <!-- Recursos PML -->
        <link type="text/css" rel="stylesheet" href="<?= URL_RESOURCES ?>css/pacarina.css">
        <link type="text/css" rel="stylesheet" href="<?= URL_RESOURCES ?>css/items-values.css">
        <script src="<?= URL_RESOURCES . 'js/pcrn.js' ?>"></script>
        <script src="<?= URL_RESOURCES . 'js/items_list.js' ?>"></script>
        <script src="<?= URL_RESOURCES . 'js/items.js' ?>"></script>
        
        <script>
            const URL_MOD = '<?= URL_ADMIN ?>'; const URL_API = '<?= URL_API ?>'; const URL_FRONT = '<?= URL_FRONT ?>';
            const URL_BASE = '<?= base_url() ?>';
            var appSection = '<?= $uri->getSegment(3) . '/' . $uri->getSegment(4); ?>';
        </script>

        <!-- Usuario con sesión iniciada -->
        <?php if ( $_SESSION['logged'] ) : ?>
            <!-- Elementos del menú -->
            <script src="<?= URL_RESOURCES ?>config/admin_pml_5/menus/nav_1_elements_1.js"></script>

            <script>
                const APP_RID = <?= $_SESSION['role'] ?>;
                const APP_UID = <?= $_SESSION['user_id'] ?>;
            </script>
        <?php endif; ?>