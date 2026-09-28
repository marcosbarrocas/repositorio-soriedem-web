<?php

namespace Source\App;

use Source\Core\Controller;

/**
 * Web Controller
 * @package Source\App
 */
class Web extends Controller
{
    /**
     * Web constructor.
     */
    public function __construct()
    {
        parent::__construct(__DIR__ . "/../../themes/" . CONF_VIEW_THEME . "/");
    }

    /**
     * Abre a entrada do sistema na tela de login do admin.
     *
     * A rota publica "/" nao tem area de visitante. Este metodo redireciona
     * para /admin/login, que e a tela inicial de acesso.
     *
     * @return void
     */
    public function home(): void
    {
        redirect("/admin/login");
    }
}