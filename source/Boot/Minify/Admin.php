<?php
if (CONF_MINIFY_ADMIN) {
    /**
     * CSS
     */
    $minCSS = new MatthiasMullie\Minify\CSS();

    //theme CSS

    $minCSS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/plugins/bootstrap-4.3.1/css/bootstrap.min.css");
    $minCSS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/css/style.css");
    $minCSS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/css/admin-custom.css");
    $minCSS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/plugins/sidemenu/sidemenu.css");
    $minCSS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/plugins/scroll-bar/jquery.mCustomScrollbar.css");
    $minCSS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/css/icons.css");
    $minCSS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/color-skins/color13.css");
    $minCSS->add(__DIR__ . "/../../../shared/styles/load.css");
    $minCSS->add(__DIR__ . "/../../../shared/styles/boot.css");
    $minCSS->add(__DIR__ . "/../../../shared/styles/styles.css");
    $minCSS->add(__DIR__ . "/../../../shared/styles/message.css");

    //Minify CSS
    $minCSS->minify(__DIR__ . "/../../../themes/" . CONF_VIEW_ADMIN . "/assets/styles.css");

    /**
     * JS
     */
    $minJS = new MatthiasMullie\Minify\JS();

    //theme JS

    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/js/jquery-3.2.1.min.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/js/apexcharts.min.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/plugins/bootstrap-4.3.1/js/popper.min.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/plugins/bootstrap-4.3.1/js/bootstrap.min.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/js/jquery.sparkline.min.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/js/circle-progress.min.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/plugins/rating/jquery.rating-stars.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/plugins/counters/counterup.min.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/plugins/counters/waypoints.min.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/plugins/sidemenu/sidemenu.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/plugins/chart/chart.bundle.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/plugins/chart/utils.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/plugins/scroll-bar/jquery.mCustomScrollbar.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/plugins/echarts/echarts.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/plugins/echarts/echarts.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/js/index1.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_ADMIN ."/assets/js/admin-custom.js");
    $minJS->add(__DIR__ . "/../../../shared/scripts/axios.js");
    $minJS->add(__DIR__ . "/../../../shared/scripts/jquery.mask.js");
    $minJS->add(__DIR__ . "/../../../shared/scripts/mask.js");
    $minJS->add(__DIR__ . "/../../../shared/scripts/jquery.form.js");
    $minJS->add(__DIR__ . "/../../../shared/scripts/scripts.js");
    $minJS->add(__DIR__ . "/../../../shared/scripts/Ajax.js");

    //Minify JS
    $minJS->minify(__DIR__ . "/../../../themes/" . CONF_VIEW_ADMIN . "/assets/scripts.js");
}