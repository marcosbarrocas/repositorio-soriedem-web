<?php

namespace Source\App\Admin;

use Source\Core\Connect;
use Source\Models\Category;
use Source\Models\Client;
use Source\Models\ClientProducts;
use Source\Models\SubCategory;
use Source\Support\Pager;


/**
 * Class ClientsProducts
 * @package Source\App\Admin
 */
class ClientsProducts extends Admin
{
    /**
     * ClientsProducts constructor.
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
            echo json_encode(["redirect" => url("/admin/clients-products/home/{$s}/1")]);
            return;
        }

        $search = null;
        $clientsProducts = (new ClientProducts())->find()->group('id_client');

        if (!empty($data["search"]) && str_search($data["search"]) != "all") {
            $search = str_search($data["search"]);
            $clientsProducts = (new ClientProducts())->find("title LIKE CONCAT('%', :s, '%')", "s={$search}")->group('id_client');
            if (!$clientsProducts->count()) {
                $this->message->info("Sua pesquisa não retornou resultados")->flash();
                redirect("/admin/clients-products/home");
            }
        }

        $all = ($search ?? "all");
        $pager = new Pager(url("/admin/clients-products/home/{$all}/"));
        $pager->pager($clientsProducts->count(), 20, (!empty($data["page"]) ? $data["page"] : 1));

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Associar Produtos do Cliente",
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/clients-products/home", [
            "app" => "clients-products/home",
            "head" => $head,
            "search" => $search,
            "clientsProducts" => $clientsProducts->limit($pager->limit())->offset($pager->offset())->fetch(true),
            "paginator" => $pager->render()
        ]);
    }

    /**
     * @param array|null $data
     * @throws \Exception
     */
    public function clientProducts(?array $data): void
    {
        //create
        if (!empty($data["action"]) && $data["action"] == "create") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $products = $data['products'];
            $prices = null;

            for ($i=0; $i < count($data['prices']); $i++) { 
                if ($data['prices'][$i] > 0) {
                    $prices[] = number_format($data['prices'][$i], 2, '.', ',');
                }
            }

            $clientProductsCreate = new ClientProducts();

            $test = count($products) + 1;
            for ($i = 0; $i < $test; $i++) {
                if ($i < count($products)) {
                    $stmt = Connect::getInstance()->prepare("INSERT INTO clients_products (id_client, id_product, price) VALUES (:id_client, :id_product, :price)");
                    $stmt->bindParam(':id_client', $data['id_client']);
                    $stmt->bindParam(':id_product', $products[$i]);
                    $stmt->bindParam(':price', $prices[$i]);
                    $stmt->execute();
                } else {
                    $this->message->success("Associação cadastrada com sucesso...")->flash();
                    redirect('/admin/clients-products/home');
                }
            }


            $this->message->success("Associação cadastrada com sucesso...")->flash();
            $json["redirect"] = url("/admin/clients-products/client-products/{$clientProductsCreate->id}");

            echo json_encode($json);
            return;
        }

        //update
        if (!empty($data["action"]) && $data["action"] == "update") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $clientProductsUpdate = (new ClientProducts())->findById($data["clientProducts_id"]);

            if (!$clientProductsUpdate) {
                $this->message->error("Você tentou gerenciar uma associação que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/clients-products/home")]);
                return;
            }

            $clientProductsUpdate->title = $data["title"];

            if (!$clientProductsUpdate->save()) {
                $json["message"] = $clientProductsUpdate->message()->render();
                echo json_encode($json);
                return;
            }

            $this->message->success("Associação atualizada com sucesso...")->flash();
            echo json_encode(["reload" => true]);
            return;
        }

        //delete
        if (!empty($data["action"]) && $data["action"] == "delete") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $categoryDelete = (new ClientProducts())->findById($data["clientProducts_id"]);

            if (!$categoryDelete) {
                $this->message->error("Você tentou deletar uma associação que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/clients-products/home")]);
                return;
            }

            $categoryDelete->destroy();

            $this->message->success("A associação foi excluída com sucesso...")->flash();
            echo json_encode(["redirect" => url("/admin/clients-products/home")]);

            return;
        }

        $clientProductsEdit = null;
        if (!empty($data["clientProducts_id"])) {
            $clientProductsId = filter_var($data["clientProducts_id"], FILTER_VALIDATE_INT);
            $clientProductsEdit = (new ClientProducts())->findById($clientProductsId);
        }

        $head = $this->seo->render(
            CONF_SITE_NAME . " | " . ($clientProductsEdit ? "Perfil de {$clientProductsEdit->title}" : "Nova Associação"),
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/clients-products/client-products", [
            "app" => "clients-products/home",
            "head" => $head,
            "clientProducts" => $clientProductsEdit,
            "clients" => (new Client())->find()->fetch(true),
            "categories" => (new Category())->find()->fetch(true),
            "subCategories" => (new SubCategory())->find()->fetch(true)
        ]);
    }

    /**
     * @param array|null $data
     */
    public function listProducts(?array $data): void
    {
        if ($data) {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $clientProducts = (new ClientProducts())->find('id_client = :idc', "idc={$data['clientProducts_id']}")->fetch(true);
        }

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Associar Produtos do Cliente",
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/clients-products/products", [
            "app" => "clients-products/home",
            "head" => $head,
            "clientsProducts" => $clientProducts
        ]);
    }
}
