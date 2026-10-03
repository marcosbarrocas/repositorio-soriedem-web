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

            $idClient = filter_var($data["id_client"] ?? null, FILTER_VALIDATE_INT);
            $products = $data["products"] ?? [];
            $prices = $data["prices"] ?? []; // valor especial chaveado por id do produto: prices[<id>]
            $required = $data["required"] ?? []; // required chaveado por id do produto: required[<id>]

            if (!$idClient || empty($products) || !is_array($products)) {
                $this->message->warning("Selecione um cliente e ao menos um produto")->flash();
                redirect($idClient
                    ? "/admin/clients-products/add/{$idClient}"
                    : "/admin/clients-products/client-products");
                return;
            }

            $pdo = Connect::getInstance();
            $insert = $pdo->prepare(
                "INSERT INTO clients_products (id_client, id_product, price, required) VALUES (:id_client, :id_product, :price, :required)"
            );
            // evita associacao duplicada do mesmo produto para o mesmo cliente
            $exists = $pdo->prepare(
                "SELECT id FROM clients_products WHERE id_client = :id_client AND id_product = :id_product LIMIT 1"
            );

            $saved = 0;
            foreach ($products as $idProduct) {
                $idProduct = filter_var($idProduct, FILTER_VALIDATE_INT);
                if (!$idProduct) {
                    continue;
                }

                $exists->execute([":id_client" => $idClient, ":id_product" => $idProduct]);
                if ($exists->fetch()) {
                    continue; // ja associado
                }

                $price = $this->normalizePrice($prices[$idProduct] ?? "0");
                $isRequired = !empty($required[$idProduct]) ? 1 : 0;
                $insert->execute([
                    ":id_client" => $idClient,
                    ":id_product" => $idProduct,
                    ":price" => $price,
                    ":required" => $isRequired
                ]);
                $saved++;
            }

            $this->message->success("{$saved} produto(s) associado(s) com sucesso...")->flash();
            redirect("/admin/clients-products/list-products/{$idClient}");
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

            $price = $this->normalizePrice($data["price"] ?? "0");
            $isRequired = !empty($data["required"]) ? 1 : 0;

            $update = Connect::getInstance()->prepare(
                "UPDATE clients_products SET price = :price, `required` = :required WHERE id = :id"
            );
            $update->execute([
                ":price" => $price,
                ":required" => $isRequired,
                ":id" => $clientProductsUpdate->id
            ]);

            $this->message->success("Valor e obrigatoriedade atualizados com sucesso...")->flash();
            echo json_encode(["redirect" => url("/admin/clients-products/list-products/{$clientProductsUpdate->id_client}")]);
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

            $idClient = $categoryDelete->id_client;
            $categoryDelete->destroy();

            $this->message->success("A associação foi excluída com sucesso...")->flash();
            echo json_encode(["redirect" => url("/admin/clients-products/list-products/{$idClient}")]);

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

        $fixedClient = null;
        $basketProducts = null;
        if (!empty($data["client_id"])) {
            $clientId = filter_var($data["client_id"], FILTER_VALIDATE_INT);
            $fixedClient = $clientId ? (new Client())->findById($clientId) : null;
            if ($fixedClient) {
                $basketProducts = (new ClientProducts())->find("id_client = :idc", "idc={$fixedClient->id}")->fetch(true);
            }
        }

        echo $this->view->render("widgets/clients-products/client-products", [
            "app" => "clients-products/home",
            "head" => $head,
            "clientProducts" => $clientProductsEdit,
            "fixedClient" => $fixedClient,
            "basketProducts" => $basketProducts,
            "clients" => (new Client())->find()->fetch(true),
            "categories" => (new Category())->find()->fetch(true),
            "subCategories" => (new SubCategory())->find()->fetch(true)
        ]);
    }

    /**
     * Converte um preco vindo da mascara BR ("1.234,56") para decimal ("1234.56").
     * @param string $value
     * @return string
     */
    private function normalizePrice(string $value): string
    {
        $value = trim($value);
        if ($value === "") {
            return "0.00";
        }
        // remove separador de milhar (.) e troca virgula decimal por ponto
        $value = str_replace(".", "", $value);
        $value = str_replace(",", ".", $value);
        $number = (float) $value;
        return number_format($number, 2, ".", "");
    }

    /**
     * @param array|null $data
     */
    public function listProducts(?array $data): void
    {
        $client = null;
        $clientProducts = null;

        if ($data) {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $clientId = filter_var($data["clientProducts_id"] ?? null, FILTER_VALIDATE_INT);
            if ($clientId) {
                $client = (new Client())->findById($clientId);
                $clientProducts = (new ClientProducts())->find("id_client = :idc", "idc={$clientId}")->fetch(true);
            }
        }

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Detalhes da cesta",
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/clients-products/products", [
            "app" => "clients-products/home",
            "head" => $head,
            "client" => $client,
            "clientsProducts" => $clientProducts
        ]);
    }
}
