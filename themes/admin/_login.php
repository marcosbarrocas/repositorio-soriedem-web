<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="msapplication-TileColor" content="#162946">
    <meta name="theme-color" content="#e67605">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <link rel="icon" href="<?= theme('/assets/images/favicon.ico', CONF_VIEW_ADMIN); ?>" type="image/x-icon"/>
    <link rel="shortcut icon" type="image/x-icon" href="<?= theme('/assets/images/favicon.ico', CONF_VIEW_ADMIN); ?>" />

    <?= $head; ?>

    <!-- Bootstrap Css -->
    <link href="<?= theme('/assets/plugins/bootstrap-4.3.1/css/bootstrap.min.css', CONF_VIEW_ADMIN); ?>" rel="stylesheet" />

    <!-- Sidemenu Css -->
    <link href="<?= theme('/assets/plugins/sidemenu/sidemenu.css', CONF_VIEW_ADMIN); ?>" rel="stylesheet" />

    <!-- Dashboard css -->
    <link href="<?= theme('/assets/css/style.css', CONF_VIEW_ADMIN); ?>" rel="stylesheet" />
    <link href="<?= theme('/assets/css/admin-custom.css', CONF_VIEW_ADMIN); ?>" rel="stylesheet" />

    <!-- c3.js Charts Plugin -->
    <link href="<?= theme('/assets/plugins/charts-c3/c3-chart.css', CONF_VIEW_ADMIN); ?>" rel="stylesheet" />

    <!---Font icons-->
    <link href="<?= theme('/assets/css/icons.css', CONF_VIEW_ADMIN); ?>" rel="stylesheet"/>

    <!-- Color-Skins -->
    <link id="theme" rel="stylesheet" type="text/css" media="all" href="<?= theme('/assets/color-skins/color13.css', CONF_VIEW_ADMIN); ?>" />
    
    <link rel="stylesheet" type="text/css" href="<?= url('/shared/styles/load.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?= url('/shared/styles/boot.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?= url('/shared/styles/styles.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?= url('/shared/styles/login.css'); ?>" />

</head>
<body class="construction-image">

<!--Loader-->
<div id="global-loader">
    <img src="<?= theme('/assets/images/loader.svg', CONF_VIEW_ADMIN); ?>" class="loader-img " alt="">
</div>
<!--/Loader-->

<div class="ajax_load">
    <div class="ajax_load_box">
        <div class="ajax_preloader">
            <img src="<?= theme('assets/images/preloader.gif', CONF_VIEW_ADMIN); ?>">
        </div>
        <p class="ajax_load_box_title">Aguarde, carregando...</p>
    </div>
</div>

<!--Page-->
<div class="page page-h">
    <div class="page-content z-index-10">
        <div class="container">
            <div class="row">
                <div class="col-xl-4 col-md-12 col-md-12 d-block mx-auto">
                    <?= $v->section("content"); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/Page-->

<!-- JQuery js-->
<script src="<?= theme('/assets/js/jquery-3.2.1.min.js', CONF_VIEW_ADMIN); ?>"></script>

<!-- Bootstrap js -->
<script src="<?= theme('/assets/plugins/bootstrap-4.3.1/js/popper.min.js', CONF_VIEW_ADMIN); ?>"></script>
<script src="<?= theme('/assets/plugins/bootstrap-4.3.1/js/bootstrap.min.js', CONF_VIEW_ADMIN); ?>"></script>

<!--JQueryVehiclerkline Js-->
<script src="<?= theme('/assets/js/jquery.sparkline.min.js', CONF_VIEW_ADMIN); ?>"></script>

<!-- Circle Progress Js-->
<script src="<?= theme('/assets/js/circle-progress.min.js', CONF_VIEW_ADMIN); ?>"></script>

<!-- Star Rating Js-->
<script src="<?= theme('/assets/plugins/rating/jquery.rating-stars.js', CONF_VIEW_ADMIN); ?>"></script>

<!-- Custom scroll bar Js-->
<script src="<?= theme('/assets/plugins/scroll-bar/jquery.mCustomScrollbar.js', CONF_VIEW_ADMIN); ?>"></script>

<!-- Fullside-menu Js-->
<script src="<?= theme('/assets/plugins/sidemenu/sidemenu.js', CONF_VIEW_ADMIN); ?>"></script>

<!--Counters -->
<script src="<?= theme('/assets/plugins/counters/counterup.min.js', CONF_VIEW_ADMIN); ?>"></script>
<script src="<?= theme('/assets/plugins/counters/waypoints.min.js', CONF_VIEW_ADMIN); ?>"></script>

<!-- Custom Js-->
<script src="<?= theme('/assets/js/admin-custom.js', CONF_VIEW_ADMIN); ?>"></script>

<script src="<?= theme('/assets/js/login.js', CONF_VIEW_ADMIN); ?>"></script>

</body>
</html>