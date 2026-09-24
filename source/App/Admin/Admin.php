<?php

namespace Source\App\Admin;

use Source\Core\Controller;
use Source\Models\Auth;

/**
 * Class Admin
 * @package Source\App\Admin
 */
class Admin extends Controller
{
    /**
     * @var \Source\Models\User|null
     */
    protected $user;

    /**
     * Admin constructor.
     */
    public function __construct()
    {
        parent::__construct(__DIR__ . "/../../../themes/" . CONF_VIEW_ADMIN . "/");

        $this->user = Auth::user();

        if (!$this->user || $this->user->level < 2) {
            $this->message->error("Para acessar é preciso logar-se")->flash();
            redirect("/admin/login");
        }
    }

    /**
     * Garante que o usuário logado possua o nível mínimo informado.
     * Perfis do painel: Administrador (5) tem acesso total e
     * Operador (3) tem acesso a tudo, exceto a gestão de usuários.
     *
     * @param int $min nível mínimo exigido para acessar o recurso
     */
    protected function requireLevel(int $min): void
    {
        if (!$this->user || $this->user->level < $min) {
            $this->message->error("Você não tem permissão para acessar essa área")->flash();
            redirect("/admin/dash");
        }
    }
}