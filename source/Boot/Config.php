<?php

/**

 * DATABASE

 * PROJECT URLs

 */

// Configuracao especifica do ambiente, fora do git (cada servidor tem a sua).
// Pode definir CONF_DB_*, CONF_URL_BASE e CONF_OMIE_* daquele host. Os blocos
// abaixo so preenchem o que o config.local nao tiver definido.
$__localConfig = __DIR__ . "/config.local.php";
if (is_file($__localConfig)) {
    require $__localConfig;
}

if (!defined("CONF_DB_HOST")) {

    if (getenv('SORIEDEM_LOCAL') === '1' || ($_SERVER['HTTP_HOST'] ?? '') == "www.localhost") {

        define("CONF_DB_HOST", "127.0.0.1");
        define("CONF_DB_USER", "soriedem_app");
        define("CONF_DB_PASS", getenv("DB_PASS") ?: "localdev");
        define("CONF_DB_NAME", "soriedem_app");

        define("CONF_URL_TEST", "http://localhost:8080");
        define("CONF_URL_BASE", "http://localhost:8080");

    } else {

        // Servidores reais definem estes valores em config.local.php.
        define("CONF_DB_HOST", getenv("DB_HOST") ?: "localhost");
        define("CONF_DB_USER", getenv("DB_USER") ?: "soriedem_app");
        define("CONF_DB_PASS", getenv("DB_PASS") ?: "");
        define("CONF_DB_NAME", getenv("DB_NAME") ?: "soriedem_app");

        define("CONF_URL_BASE", getenv("URL_BASE") ?: "https://www.soriedem.com.br/sistema");

    }

}

/**
 * OMIE API
 * Credenciais lidas do config.local.php (producao) ou de variaveis de ambiente
 * (local via SORIEDEM_LOCAL). O segredo nunca e versionado.
 */
if (!defined("CONF_OMIE_BASE")) {
    define("CONF_OMIE_BASE", getenv("OMIE_BASE") ?: "https://app.omie.com.br/api/v1");
}
if (!defined("CONF_OMIE_APP_KEY")) {
    define("CONF_OMIE_APP_KEY", getenv("OMIE_APP_KEY") ?: "");
}
if (!defined("CONF_OMIE_APP_SECRET")) {
    define("CONF_OMIE_APP_SECRET", getenv("OMIE_APP_SECRET") ?: "");
}



/**

 * SITE

 */

define("CONF_SITE_NAME", "SORIEDEM");

define("CONF_SITE_TITLE", "O melhor sistema de gestão!");

define("CONF_SITE_DESC", "O SORIEDEM é um gerenciador poderoso e gratuito.");

define("CONF_SITE_LANG", "pt_BR");

define("CONF_SITE_DOMAIN", "");

define("CONF_SITE_ADDR_STREET", "");

define("CONF_SITE_ADDR_NUMBER", "");

define("CONF_SITE_ADDR_COMPLEMENT", "");

define("CONF_SITE_ADDR_CITY", "");

define("CONF_SITE_ADDR_STATE", "");

define("CONF_SITE_ADDR_ZIPCODE", "");



/**

 * SOCIAL

 */

define("CONF_SOCIAL_TWITTER_CREATOR", "@creator");

define("CONF_SOCIAL_TWITTER_PUBLISHER", "@creator");

define("CONF_SOCIAL_FACEBOOK_APP", "5555555555");

define("CONF_SOCIAL_FACEBOOK_PAGE", "pagename");

define("CONF_SOCIAL_FACEBOOK_AUTHOR", "author");

define("CONF_SOCIAL_GOOGLE_PAGE", "5555555555");

define("CONF_SOCIAL_GOOGLE_AUTHOR", "5555555555");

define("CONF_SOCIAL_INSTAGRAM_PAGE", "insta");

define("CONF_SOCIAL_YOUTUBE_PAGE", "youtube");



/**

 * DATES

 */

define("CONF_DATE_BR", "d/m/Y H:i:s");

define("CONF_DATE_APP", "Y-m-d H:i:s");



/**

 * PASSWORD

 */

define("CONF_PASSWD_MIN_LEN", 8);

define("CONF_PASSWD_MAX_LEN", 40);

define("CONF_PASSWD_ALGO", PASSWORD_DEFAULT);

define("CONF_PASSWD_OPTION", ["cost" => 10]);



/**

 * VIEW

 */

define("CONF_VIEW_PATH", __DIR__ . "/../../shared/views");

define("CONF_VIEW_EXT", "php");

define("CONF_VIEW_THEME", "web");

define("CONF_VIEW_APP", "app");

define("CONF_VIEW_ADMIN", "admin");



/**

 * MINIFY

 */

define("CONF_MINIFY_THEME", false);

define("CONF_MINIFY_APP", false);

define("CONF_MINIFY_ADMIN", false);



/**

 * UPLOAD

 */

define("CONF_UPLOAD_DIR", "storage");

define("CONF_UPLOAD_IMAGE_DIR", "images");

define("CONF_UPLOAD_FILE_DIR", "files");

define("CONF_UPLOAD_MEDIA_DIR", "medias");



/**

 * IMAGES

 */

define("CONF_IMAGE_CACHE", CONF_UPLOAD_DIR . "/" . CONF_UPLOAD_IMAGE_DIR . "/cache");

define("CONF_IMAGE_SIZE", 2000);

define("CONF_IMAGE_QUALITY", ["jpg" => 75, "png" => 5]);



/**

 * MAIL

 */

define("CONF_MAIL_HOST", getenv("MAIL_HOST") ?: "smtp.gmail.com");

define("CONF_MAIL_PORT", getenv("MAIL_PORT") ?: "587");

define("CONF_MAIL_USER", getenv("MAIL_USER") ?: "sendemaildeveloper@gmail.com");

define("CONF_MAIL_PASS", getenv("MAIL_PASS") ?: "");

define("CONF_MAIL_SENDER", ["name" => "SORIEDEM", "address" => "sendemaildeveloper@gmail.com"]);

define("CONF_MAIL_SUPPORT", "sendemaildeveloper@gmail.com");

define("CONF_MAIL_OPTION_LANG", "br");

define("CONF_MAIL_OPTION_HTML", true);

define("CONF_MAIL_OPTION_AUTH", true);

define("CONF_MAIL_OPTION_SECURE", "tls");

define("CONF_MAIL_OPTION_CHARSET", "utf-8");
