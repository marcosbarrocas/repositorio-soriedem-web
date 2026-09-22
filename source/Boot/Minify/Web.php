<?php
if (CONF_MINIFY_THEME) {
    /**
     * CSS
     */
    $minCSS = new MatthiasMullie\Minify\CSS();

    //theme CSS

    $minCSS->add(__DIR__ . "/../../../themes/". CONF_VIEW_THEME ."/assets/css/bootstrap.min.css");
    $minCSS->add(__DIR__ . "/../../../shared/styles/boot.css");
    $minCSS->add(__DIR__ . "/../../../shared/styles/load.css");
    $minCSS->add(__DIR__ . "/../../../shared/styles/message.css");
    $minCSS->add(__DIR__ . "/../../../shared/styles/styles.css");

    //Minify CSS
    $minCSS->minify(__DIR__ . "/../../../themes/" . CONF_VIEW_THEME . "/assets/styles.css");

    /**
     * JS
     */
    $minJS = new MatthiasMullie\Minify\JS();

    //theme JS

    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_THEME ."/assets/js/jquery-3.6.0.min.js");
    $minJS->add(__DIR__ . "/../../../themes/". CONF_VIEW_THEME ."/assets/js/bootstrap.bundle.min.js");
    $minJS->add(__DIR__ . "/../../../shared/scripts/jquery.mask.js");
    $minJS->add(__DIR__ . "/../../../shared/scripts/mask.js");
    $minJS->add(__DIR__ . "/../../../shared/scripts/jquery.form.js");
    $minJS->add(__DIR__ . "/../../../shared/scripts/scripts.js");
    $minJS->add(__DIR__ . "/../../../shared/scripts/Ajax.js");

    //Minify JS
    $minJS->minify(__DIR__ . "/../../../themes/" . CONF_VIEW_THEME . "/assets/scripts.js");
}
