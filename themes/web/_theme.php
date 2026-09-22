<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?= $head; ?>
    <link rel="shortcut icon" type="image/x-icon" href="<?= theme('assets/images/favicon.ico'); ?>">
    <link rel="stylesheet" href="<?= theme("/assets/styles.css"); ?>"/>
</head>
<body>
<!--Preloader area start here-->
<div class="ajax_load">
    <div class="ajax_load_box">
        <div class="ajax_preloader">
            <img src="<?= theme('assets/images/loader.svg'); ?>">
        </div>
        <p class="ajax_load_box_title">Aguarde, carregando...</p>
    </div>
</div>
<!--Preloader area End here-->
<main>
    <?= $v->section("content"); ?>
</main>
<script src="<?= theme("/assets/scripts.js"); ?>"></script>
<?= $v->section('scripts'); ?>
</body>
</html>