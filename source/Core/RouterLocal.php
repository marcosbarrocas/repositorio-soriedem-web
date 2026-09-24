<?php

namespace Source\Core;

use CoffeeCode\Router\Router;

/**
 * Router com compatibilidade para o servidor embutido do PHP (php -S).
 *
 * O Router do CoffeeCode obtem a rota de filter_input(INPUT_GET, "route"), que
 * sob Apache e preenchido pela regra de rewrite do .htaccess (index.php?route=/...).
 * O servidor embutido nao faz esse rewrite, entao aqui derivamos a rota a partir
 * do caminho do REQUEST_URI. Sob Apache/produção o comportamento fica inalterado.
 *
 * @package Source\Core
 */
class RouterLocal extends Router
{
    public function __construct(string $projectUrl, ?string $separator = ":")
    {
        parent::__construct($projectUrl, $separator);

        if (php_sapi_name() === "cli-server" && empty(filter_input(INPUT_GET, "route"))) {
            $path = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);
            $this->patch = $path ?: "/";
        }
    }
}
