<?php

namespace Source\App\Admin;

use Source\Models\Request;
use Source\Support\Pager;

/**
 * Class Requests
 * @package Source\App\Admin
 */
class Requests extends Admin
{
    /**
     * Requests constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array|null $data
     */
    public function home(?array $data): void
    {
        //search redirect
        if (!empty($data["s"])) {
            $s = str_search($data["s"]);
            echo json_encode(["redirect" => url("/admin/requests/home/{$s}/1")]);
            return;
        }

        $search = null;
        $requests = (new Request())->find()->group('request_number');

        if (!empty($data["search"]) && str_search($data["search"]) != "all") {
            $search = str_search($data["search"]);
            $requests = (new Request())->find("corporate_name LIKE CONCAT('%', :s, '%') OR contact_name LIKE CONCAT('%', :s, '%') OR email LIKE CONCAT('%', :s, '%')", "s={$search}")->group('request_number');
            if (!$requests->count()) {
                $this->message->info("Sua pesquisa não retornou resultados")->flash();
                redirect("/admin/requests/home");
            }
        }

        $all = ($search ?? "all");
        $pager = new Pager(url("/admin/requests/home/{$all}/"));




        $pager->pager($requests->count(), 20, (!empty($data["page"]) ? $data["page"] : 1));

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Pedidos",
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/requests/home", [
            "app" => "requests/home",
            "head" => $head,
            "search" => $search,
            "requests" => $requests->limit($pager->limit())->offset($pager->offset())->group('request_number')->order('id DESC')->fetch(true),
            "paginator" => $pager->render()
        ]);
    }

    /**
     * @param array|null $data
     * @throws \Exception
     */
    public function request(?array $data): void
    {
        //create
        if (!empty($data["action"]) && $data["action"] == "create") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

            $requestCreate = new Request();
            $requestCreate->title = $data["title"];

            if (!$requestCreate->save()) {
                $json["message"] = $requestCreate->message()->render();
                echo json_encode($json);
                return;
            }

            $this->message->success("Pedido cadastrado com sucesso...")->flash();
            $json["redirect"] = url("/admin/requests/request/{$requestCreate->id}");

            echo json_encode($json);
            return;
        }

        //update
        if (!empty($data["action"]) && $data["action"] == "update") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $requestUpdate = (new Request())->findById($data["request_id"]);

            if (!$requestUpdate) {
                $this->message->error("Você tentou gerenciar um pedido que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/requests/home")]);
                return;
            }

            $requestUpdate->request_number = $requestUpdate->request_number;
            $requestUpdate->id_seller = $requestUpdate->id_seller;
            $requestUpdate->id_client = $requestUpdate->id_client;
            $requestUpdate->status = $data['status'];

            if (!$requestUpdate->save()) {
                $json["message"] = $requestUpdate->message()->render();
                echo json_encode($json);
                return;
            }

            $this->message->success("Pedido atualizado com sucesso...")->flash();
            echo json_encode(["reload" => true]);
            return;
        }

        //delete
        if (!empty($data["action"]) && $data["action"] == "delete") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $requestDelete = (new Request())->findById($data["request_id"]);

            if (!$requestDelete) {
                $this->message->error("Você tentou deletar um pedido que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/requests/home")]);
                return;
            }

            $requestDelete->destroy();

            $this->message->success("O pedido foi excluído com sucesso...")->flash();
            echo json_encode(["redirect" => url("/admin/requests/home")]);

            return;
        }

        $requestEdit = null;
        if (!empty($data["request_id"])) {
            $requestId = filter_var($data["request_id"], FILTER_VALIDATE_INT);
            $requestEdit = (new Request())->findById($requestId);
        }

        $head = $this->seo->render(
            CONF_SITE_NAME . " | " . ($requestEdit ? "Perfil de {$requestEdit->title}" : "Nova Pedido"),
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/requests/request", [
            "app" => "requests/home",
            "head" => $head,
            "request" => $requestEdit
        ]);
    }
}
