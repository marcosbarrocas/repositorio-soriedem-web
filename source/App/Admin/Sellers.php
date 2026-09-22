<?php

namespace Source\App\Admin;

use Source\Models\Seller;
use Source\Support\Pager;

/**
 * Class Sellers
 * @package Source\App\Admin
 */
class Sellers extends Admin
{
    /**
     * Sellers constructor.
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
            echo json_encode(["redirect" => url("/admin/sellers/home/{$s}/1")]);
            return;
        }

        $search = null;
        $sellers = (new Seller())->find();

        if (!empty($data["search"]) && str_search($data["search"]) != "all") {
            $search = str_search($data["search"]);
            $sellers = (new Seller())->find("first_name LIKE CONCAT('%', :s, '%') OR last_name LIKE CONCAT('%', :s, '%') OR email LIKE CONCAT('%', :s, '%')", "s={$search}");
            if (!$sellers->count()) {
                $this->message->info("Sua pesquisa não retornou resultados")->flash();
                redirect("/admin/sellers/home");
            }
        }

        $all = ($search ?? "all");
        $pager = new Pager(url("/admin/sellers/home/{$all}/"));
        $pager->pager($sellers->count(), 20, (!empty($data["page"]) ? $data["page"] : 1));

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Vendedores",
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/sellers/home", [
            "app" => "sellers/home",
            "head" => $head,
            "search" => $search,
            "sellers" => $sellers->limit($pager->limit())->offset($pager->offset())->fetch(true),
            "paginator" => $pager->render()
        ]);
    }

    /**
     * @param array|null $data
     * @throws \Exception
     */
    public function seller(?array $data): void
    {
        //create
        if (!empty($data["action"]) && $data["action"] == "create") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

            $sellerCreate = new Seller();
            $sellerCreate->first_name = $data["first_name"];
            $sellerCreate->last_name = $data["last_name"];
            $sellerCreate->phone = clean_mask($data['phone']);
            $sellerCreate->document = clean_mask($data['document']);
            $sellerCreate->email = $data["email"];
            $sellerCreate->password = $data["password"];

            if (!$sellerCreate->save()) {
                $json["message"] = $sellerCreate->message()->render();
                echo json_encode($json);
                return;
            }

            $this->message->success("Vendedor cadastrado com sucesso...")->flash();
            $json["redirect"] = url("/admin/sellers/seller/{$sellerCreate->id}");

            echo json_encode($json);
            return;
        }

        //update
        if (!empty($data["action"]) && $data["action"] == "update") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $sellerUpdate = (new Seller())->findById($data["seller_id"]);

            if (!$sellerUpdate) {
                $this->message->error("Você tentou gerenciar um vendedor que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/sellers/home")]);
                return;
            }

            $sellerUpdate->first_name = $data["first_name"];
            $sellerUpdate->last_name = $data["last_name"];
            $sellerUpdate->phone = clean_mask($data['phone']);
            $sellerUpdate->document = clean_mask($data['document']);
            $sellerUpdate->email = $data["email"];
            $sellerUpdate->password = $data["password"];

            if (!$sellerUpdate->save()) {
                $json["message"] = $sellerUpdate->message()->render();
                echo json_encode($json);
                return;
            }

            $this->message->success("Vendedor atualizado com sucesso...")->flash();
            echo json_encode(["reload" => true]);
            return;
        }

        //delete
        if (!empty($data["action"]) && $data["action"] == "delete") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $sellerDelete = (new Seller())->findById($data["seller_id"]);

            if (!$sellerDelete) {
                $this->message->error("Você tentnou deletar um vendedor que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/sellers/home")]);
                return;
            }

            $sellerDelete->destroy();

            $this->message->success("O vendedor foi excluído com sucesso...")->flash();
            echo json_encode(["redirect" => url("/admin/sellers/home")]);

            return;
        }

        $sellerEdit = null;
        if (!empty($data["seller_id"])) {
            $sellerId = filter_var($data["seller_id"], FILTER_VALIDATE_INT);
            $sellerEdit = (new Seller())->findById($sellerId);
        }

        $head = $this->seo->render(
            CONF_SITE_NAME . " | " . ($sellerEdit ? "Perfil de {$sellerEdit->fullName()}" : "Novo Vendedor"),
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/sellers/seller", [
            "app" => "sellers/home",
            "head" => $head,
            "seller" => $sellerEdit
        ]);
    }
}