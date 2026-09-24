<?php

namespace Source\App\Admin;

use Source\Models\Category;
use Source\Models\ClientProducts;
use Source\Models\Product;
use Source\Models\Provider;
use Source\Models\SubCategory;
use Source\Support\Pager;
use Source\Support\Thumb;
use Source\Support\Upload;

/**
 * Class Products
 * @package Source\App\Admin
 */
class Products extends Admin
{
    /**
     * Products constructor.
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
            echo json_encode(["redirect" => url("/admin/products/home/{$s}/1")]);
            return;
        }

        $search = null;
        $products = (new Product())->find();

        if (!empty($data["search"]) && str_search($data["search"]) != "all") {
            $search = str_search($data["search"]);
            $products = (new Product())->find("title LIKE CONCAT('%', :s, '%')", "s={$search}");
            if (!$products->count()) {
                $this->message->info("Sua pesquisa não retornou resultados")->flash();
                redirect("/admin/products/home");
            }
        }

        $all = ($search ?? "all");
        $pager = new Pager(url("/admin/products/home/{$all}/"));
        $pager->pager($products->count(), 20, (!empty($data["page"]) ? $data["page"] : 1));

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Produtos",
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/products/home", [
            "app" => "products/home",
            "head" => $head,
            "search" => $search,
            "products" => $products->limit($pager->limit())->offset($pager->offset())->fetch(true),
            "paginator" => $pager->render()
        ]);
    }

    /**
     * @param array|null $data
     * @throws \Exception
     */
    public function product(?array $data): void
    {
        //create
        if (!empty($data["action"]) && $data["action"] == "create") {


            $products = (new Product())->find('code = :ids', "ids={$data['code']}")->fetch(true);

            if (empty($products)) {
                $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

                $productCreate = new Product();
                $productCreate->code = $data['code'];
                $productCreate->title = $data["title"];
                $productCreate->description = $data["description"];
                $productCreate->value = clean_mask($data["value"]);
                $productCreate->stock = $data["stock"];
                $productCreate->id_category = $data["id_category"];
                // $productCreate->id_subcategory = $data["id_subcategory"];
                // $productCreate->id_provider = $data["id_provider"];


                //upload photo
                if (!empty($_FILES["photo"])) {
                    $files = $_FILES["photo"];
                    $upload = new Upload();
                    $image = $upload->image($files, $productCreate->title, 600);

                    if (!$image) {
                        $json["message"] = $upload->message()->render();
                        echo json_encode($json);
                        return;
                    }

                    $productCreate->photo = $image;
                }

                //upload file_bt
                if (!empty($_FILES["file_bt"])) {
                    $files = $_FILES["file_bt"];
                    $upload = new Upload();
                    $file = $upload->file($files, $productCreate->title);

                    if (!$file) {
                        $json["message"] = $upload->message()->render();
                        echo json_encode($json);
                        return;
                    }

                    $productCreate->file_bt = $file;
                }

                //upload file_fispq
                if (!empty($_FILES["file_fispq"])) {
                    $files = $_FILES["file_fispq"];
                    $upload = new Upload();
                    $file = $upload->file($files, $productCreate->title);

                    if (!$file) {
                        $json["message"] = $upload->message()->render();
                        echo json_encode($json);
                        return;
                    }

                    $productCreate->file_fispq = $file;
                }

                if (!$productCreate->save()) {
                    $json["message"] = $productCreate->message()->render();
                    echo json_encode($json);
                    return;
                }

                $this->message->success("Produto cadastrado com sucesso...")->flash();
                $json["redirect"] = url("/admin/products/product/{$productCreate->id}");

                echo json_encode($json);
                return;
            } else {
                return;
            }
        }

        //update
        if (!empty($data["action"]) && $data["action"] == "update") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $productUpdate = (new Product())->findById($data["product_id"]);

            if (!$productUpdate) {
                $this->message->error("Você tentou gerenciar um produto que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/products/home")]);
                return;
            }

            $productUpdate->code = $data["code"];
            $productUpdate->title = $data["title"];
            $productUpdate->description = $data["description"];
            $productUpdate->value = $data["value"];
            $productUpdate->stock = $data["stock"];
            $productUpdate->id_category = $data["id_category"];
            //$productUpdate->id_subcategory = $data["id_subcategory"];
            //$productUpdate->id_provider = $data["id_provider"];

            //upload photo
            if (!empty($_FILES["photo"])) {
                if ($productUpdate->photo && file_exists(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$productUpdate->photo}")) {
                    unlink(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$productUpdate->photo}");
                    (new Thumb())->flush($productUpdate->photo);
                }

                $files = $_FILES["photo"];
                $upload = new Upload();
                $image = $upload->image($files, $productUpdate->title, 600);

                if (!$image) {
                    $json["message"] = $upload->message()->render();
                    echo json_encode($json);
                    return;
                }

                $productUpdate->photo = $image;
            }

            //upload file_bt
            if (!empty($_FILES["file_bt"])) {
                if ($productUpdate->file_bt && file_exists(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$productUpdate->file_bt}")) {
                    unlink(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$productUpdate->file_bt}");
                    (new Thumb())->flush($productUpdate->file_bt);
                }

                $files = $_FILES["file_bt"];
                $upload = new Upload();
                $file = $upload->file($files, $productUpdate->title);

                if (!$file) {
                    $json["message"] = $upload->message()->render();
                    echo json_encode($json);
                    return;
                }

                $productUpdate->file_bt = $file;
            }

            //upload file_fispq
            if (!empty($_FILES["file_fispq"])) {
                if ($productUpdate->file_fispq && file_exists(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$productUpdate->file_fispq}")) {
                    unlink(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$productUpdate->file_fispq}");
                    (new Thumb())->flush($productUpdate->file_fispq);
                }

                $files = $_FILES["file_fispq"];
                $upload = new Upload();
                $file = $upload->file($files, $productUpdate->title);

                if (!$file) {
                    $json["message"] = $upload->message()->render();
                    echo json_encode($json);
                    return;
                }

                $productUpdate->file_fispq = $file;
            }

            if (!$productUpdate->save()) {
                $json["message"] = $productUpdate->message()->render();
                echo json_encode($json);
                return;
            }

            $this->message->success("Produto atualizado com sucesso...")->flash();
            echo json_encode(["reload" => true]);
            return;
        }

        //delete
        if (!empty($data["action"]) && $data["action"] == "delete") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $productDelete = (new Product())->findById($data["product_id"]);

            if (!$productDelete) {
                $this->message->error("Você tentou deletar um produto que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/products/home")]);
                return;
            }

            if ($productDelete->photo && file_exists(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$productDelete->photo}")) {
                unlink(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$productDelete->photo}");
                (new Thumb())->flush($productDelete->photo);
            }

            if ($productDelete->file_bt && file_exists(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$productDelete->file_bt}")) {
                unlink(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$productDelete->file_bt}");
                (new Thumb())->flush($productDelete->file_bt);
            }

            if ($productDelete->file_fispq && file_exists(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$productDelete->file_fispq}")) {
                unlink(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$productDelete->file_fispq}");
                (new Thumb())->flush($productDelete->file_fispq);
            }

            $productDelete->destroy();

            $this->message->success("O produto foi excluído com sucesso...")->flash();
            echo json_encode(["redirect" => url("/admin/products/home")]);

            return;
        }

        $productEdit = null;
        if (!empty($data["product_id"])) {
            $productId = filter_var($data["product_id"], FILTER_VALIDATE_INT);
            $productEdit = (new Product())->findById($productId);
        }

        $head = $this->seo->render(
            CONF_SITE_NAME . " | " . ($productEdit ? "Perfil de {$productEdit->title}" : "Novo Produto"),
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/products/product", [
            "app" => "products/home",
            "head" => $head,
            "product" => $productEdit,
            "categories" => (new Category())->find()->fetch(true),
            "subCategories" => (new SubCategory())->find()->fetch(true),
            "providers" => (new Provider())->find()->fetch(true)
        ]);
    }

    /**
     * @param array|null $data
     * @return void
     */
    public function getProducts(?array $data)
    {
        if ($data) {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $products = (new Product())->find('id_subcategory = :ids', "ids={$data['subcategory_id']}")->fetch(true);

            if ($products) {
                $productsArr = null;
                for ($i = 0; $i < count($products); $i++) {
                    $productsArr[] = $products[$i]->data();
                }
            }

            echo json_encode($productsArr);
        }
    }

    /**
     * @param array|null $data
     * @return void
     */
    public function getProductsClient(?array $data)
    {
        if ($data) {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $products = (new ClientProducts())->find('id_client = :idc', "idc={$data['client_id']}")->fetch(true);

            if ($products) {
                $productsArr = null;
                for ($i = 0; $i < count($products); $i++) {
                    $productsArr[] = $products[$i]->data();
                }
            } else {
                $productsArr = false;
            }
            echo json_encode($productsArr);
        }
    }

    /**
     * Busca produtos por nome ou codigo (independente de categoria).
     * Usado na tela de cesta para associar produtos vindos da Omie.
     * POST /admin/products/search  { q: "texto", only_with_photo: "1"? }
     * @param array|null $data
     */
    public function searchProducts(?array $data): void
    {
        $data = filter_var_array((array)$data, FILTER_SANITIZE_STRIPPED);
        $term = trim($data["q"] ?? "");
        $onlyWithPhoto = !empty($data["only_with_photo"]);

        if (mb_strlen($term) < 2) {
            echo json_encode([]);
            return;
        }

        $terms = "(title LIKE CONCAT('%', :q, '%') OR code LIKE CONCAT('%', :q, '%'))";
        if ($onlyWithPhoto) {
            $terms .= " AND photo IS NOT NULL AND photo <> ''";
        }

        $products = (new Product())
            ->find($terms, "q={$term}")
            ->order("title ASC")
            ->limit(50)
            ->fetch(true);

        $out = [];
        if ($products) {
            foreach ($products as $p) {
                $out[] = $p->data();
            }
        }
        echo json_encode($out);
    }

    /**
     * Tela de vinculo manual de fotos: lista produtos Omie (com omie_codigo) que
     * ainda estao sem foto, para o admin vincular a foto de um produto antigo.
     * GET /admin/products/photos[/{search}/{page}]
     * @param array|null $data
     */
    public function photos(?array $data): void
    {
        if (!empty($data["s"])) {
            $s = str_search($data["s"]);
            echo json_encode(["redirect" => url("/admin/products/photos/{$s}/1")]);
            return;
        }

        $search = null;
        $terms = "omie_codigo IS NOT NULL AND (photo IS NULL OR photo = '')";
        $params = null;
        if (!empty($data["search"]) && str_search($data["search"]) != "all") {
            $search = str_search($data["search"]);
            $terms .= " AND (title LIKE CONCAT('%', :s, '%') OR code LIKE CONCAT('%', :s, '%'))";
            $params = "s={$search}";
        }

        $pending = (new Product())->find($terms, $params);

        $all = ($search ?? "all");
        $pager = new \Source\Support\Pager(url("/admin/products/photos/{$all}/"));
        $pager->pager($pending->count(), 20, (!empty($data["page"]) ? $data["page"] : 1));

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Fotos pendentes",
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/products/photos", [
            "app" => "products/photos",
            "head" => $head,
            "search" => $search,
            "products" => $pending->limit($pager->limit())->offset($pager->offset())->fetch(true),
            "paginator" => $pager->render(),
        ]);
    }

    /**
     * Vincula a foto de um produto de origem a um produto Omie de destino.
     * POST /admin/products/link-photo { id: <destino>, source_id: <origem> }
     * @param array|null $data
     */
    public function linkPhoto(?array $data): void
    {
        $data = filter_var_array((array)$data, FILTER_SANITIZE_STRIPPED);
        $destId = filter_var($data["id"] ?? null, FILTER_VALIDATE_INT);
        $srcId = filter_var($data["source_id"] ?? null, FILTER_VALIDATE_INT);

        if (!$destId || !$srcId) {
            echo json_encode(["error" => "Parametros invalidos."]);
            return;
        }

        $dest = (new Product())->findById($destId);
        $src = (new Product())->findById($srcId);

        if (!$dest || !$src) {
            echo json_encode(["error" => "Produto nao encontrado."]);
            return;
        }
        if (empty($src->photo)) {
            echo json_encode(["error" => "O produto de origem nao tem foto."]);
            return;
        }

        $dest->photo = $src->photo;
        $dest->file_bt = $src->file_bt;
        $dest->file_fispq = $src->file_fispq;
        $dest->save();

        $this->message->success("Foto vinculada com sucesso...")->flash();
        echo json_encode(["reload" => true]);
    }
}
