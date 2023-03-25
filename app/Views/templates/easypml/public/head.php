<?php
    $uri = current_url(true);
    $appSection = '';
    $segments = $uri->getSegments();
    //$appSection = '';
    //echo count($segments);
    if ( count($segments) == 2 ) $appSection =  $uri->getSegment(2, '');
    if ( count($segments) == 3 ) $appSection =  $uri->getSegment(2, '') . '/' . $uri->getSegment(3, '');
    if ( count($segments) >= 4 ) $appSection =  $uri->getSegment(2, '') . '/' . $uri->getSegment(3, '') . '/' . $uri->getSegment(4, '');
    
?>

    <script>
        const loggedStatus = localStorage.getItem("logged");
        console.log('voy a redireccionar');
        /*if ( loggedStatus ) {
            console.log('voy a redireccionar');
        }*/
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $headTitle ?></title>
    <link rel="shortcut icon" href="<?= URL_IMG ?>app/favicon.png" type="image/png"/>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>

    <!-- Bootstrap CSS 5.1.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/f45fca298e.js" crossorigin="anonymous"></script>

    <!-- Vue.js -->
    <script src="https://unpkg.com/vue@next"></script>
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

    <!-- Styles -->
    <link rel="stylesheet" href="<?= URL_RESOURCES ?>templates/easypml/style.css"></link>    

    <script>
        const URL_MOD = '<?= URL_APP ?>'; const URL_API = '<?= URL_API ?>';
        var appSection = '<?= $appSection; ?>';

        <?php if ( isset($_SESSION['logged']) ) : ?>
            const APP_RID = <?= $_SESSION['role'] ?>; const APP_UID = <?= $_SESSION['user_id'] ?>;
        <?php else: ?>
            const APP_RID = 0; const APP_UID = 0;
        <?php endif; ?>
    </script>
    <script src="<?= URL_RESOURCES ?>js/bs5_routing.js"></script>

    <!-- navbar elements -->
    <script src="<?= URL_RESOURCES ?>config/easypml/menus/nav_1_elements_1.js"></script>
