<?php
    $uri = current_url(true);
    $appSection = '';
    $segments = $uri->getSegments();

    if ( count($segments) == 2 ) $appSection =  $uri->getSegment(2, '');
    if ( count($segments) == 3 ) $appSection =  $uri->getSegment(2, '') . '/' . $uri->getSegment(3, '');
    if ( count($segments) >= 4 ) $appSection =  $uri->getSegment(2, '') . '/' . $uri->getSegment(3, '') . '/' . $uri->getSegment(4, '');
    
?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $headTitle ?></title>
    <link rel="shortcut icon" href="<?= URL_BRAND ?>favicon.png" type="image/png"/>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>

    <!-- Bootstrap CSS 5.1.3 -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/f45fca298e.js" crossorigin="anonymous"></script>

    <!-- Vue.js -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.0.0-alpha.1/axios.min.js" integrity="sha512-xIPqqrfvUAc/Cspuj7Bq0UtHNo/5qkdyngx6Vwt+tmbvTLDszzXM0G6c91LXmGrRx8KEPulT+AfOOez+TeVylg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>const {createApp} = Vue;</script>

    <!-- Alertas y notificaciones -->
    <link href="<?= URL_RESOURCES ?>templates/easypml/toastr.css" rel="stylesheet" type="text/css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="<?= URL_RESOURCES ?>config/easypml/toastr-options.js"></script>

    <!--Icons font-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">

    <!-- Animaciones CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- Moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.js" integrity="sha256-H9jAz//QLkDOy/nzE9G4aYijQtkLt9FvGmdUTwBk6gs=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/locale/es.js" integrity="sha256-bETP3ndSBCorObibq37vsT+l/vwScuAc9LRJIQyb068=" crossorigin="anonymous"></script>

    <!-- PML Tools -->
    <link rel="stylesheet" href="<?= URL_RESOURCES ?>css/pacarina.css"></link>
    <script src="<?= URL_RESOURCES . 'js/pcrn.js' ?>"></script>
    <script src="<?= URL_RESOURCES . 'js/items_list.js' ?>"></script>
    <script src="<?= URL_RESOURCES . 'js/items.js' ?>"></script>

    <link rel="stylesheet" href="<?= URL_RESOURCES ?>css/sidebars.css"></link>

    <!-- Styles -->
    <link rel="stylesheet" href="<?= URL_RESOURCES ?>templates/easypml/theme.css"></link>    
    <link rel="stylesheet" href="<?= URL_RESOURCES ?>templates/easypml/style.css"></link>    

    <script>
        const URL_MOD = '<?= URL_APP ?>'; const URL_API = '<?= URL_API ?>';
        var appSection = '<?= $appSection ?>';

        <?php if ( isset($_SESSION['logged']) ) : ?>
            const APP_RID = <?= $_SESSION['role'] ?>; const APP_UID = <?= $_SESSION['user_id'] ?>;
        <?php else: ?>
            const APP_RID = 0; const APP_UID = 0;
        <?php endif; ?>
    </script>
    <script src="<?= URL_RESOURCES ?>js/bs5_routing.js"></script>

    <!-- navbar elements -->
    <script src="<?= URL_RESOURCES ?>config/easypml/menus/nav_1_elements_1.js"></script>
