<header class="main-header">
    <!-- Logo -->
    <a href="<?= URL_FRONT ?>info/inicio/" class="logo fixed-top">
        <img src="<?= URL_BRAND ?>logo-admin.png" alt="Logo app" style="height: 40px;">
    </a>
    <nav class="navbar fixed-top" role="navigation">
        <div class="">
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <i class="fa fa-bars"></i><span class="sr-only">Toggle navigation</span>
            </a>
        </div>
        <h1 id="head_title"><?= substr($headTitle, 0, 50) ?></h1>

        <div class="">
            <div class="dropdown only-lg">
                <a href="#" data-bs-toggle="dropdown" title="<?= $_SESSION['display_name'] ?>">
                    <img src="<?= $_SESSION['picture'] ?>" class="navbar-user-image" alt="User Image"
                        onerror="this.src='<?= URL_IMG ?>users/sm_user.png'">
                </a>
                <ul class="dropdown-menu dropdown-menu-end mr-1">
                    <a class="dropdown-item" href="<?= URL_APP . 'accounts/profile' ?>">Mi cuenta</a>
                    <a class="dropdown-item" href="<?= URL_APP . 'accounts/logout' ?>">Cerrar sesión</a>
                </ul>
            </div>
        </div>
    </nav>
</header>