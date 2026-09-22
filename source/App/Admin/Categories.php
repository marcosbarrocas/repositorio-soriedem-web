<?php

namespace Source\App\Admin;

use Source\Models\Category;
use Source\Models\SubCategory;
use Source\Support\Pager;
use Source\Support\Thumb;
use Source\Support\Upload;

/**
 * Class Categories
 * @package Source\App\Admin
 */
class Categories extends Admin
{
    /**
     * Categories constructor.
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
            echo json_encode(["redirect" => url("/admin/categories/home/{$s}/1")]);
            return;
        }

        $search = null;
        $categories = (new Category())->find();

        if (!empty($data["search"]) && str_search($data["search"]) != "all") {
            $search = str_search($data["search"]);
            $categories = (new Category())->find("title LIKE CONCAT('%', :s, '%')", "s={$search}");
            if (!$categories->count()) {
                $this->message->info("Sua pesquisa não retornou resultados")->flash();
                redirect("/admin/categories/home");
            }
        }

        $all = ($search ?? "all");
        $pager = new Pager(url("/admin/categories/home/{$all}/"));
        $pager->pager($categories->count(), 20, (!empty($data["page"]) ? $data["page"] : 1));

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Categorias",
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/categories/home", [
            "app" => "categories/home",
            "head" => $head,
            "search" => $search,
            "categories" => $categories->limit($pager->limit())->offset($pager->offset())->fetch(true),
            "paginator" => $pager->render()
        ]);
    }

    /**
     * @param array|null $data
     * @throws \Exception
     */
    public function category(?array $data): void
    {
        //create
        if (!empty($data["action"]) && $data["action"] == "create") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

            $categoryCreate = new Category();
            $categoryCreate->title = $data["title"];

            //upload image
            if (!empty($_FILES["image"])) {
                $files = $_FILES["image"];
                $upload = new Upload();
                $image = $upload->image($files, $categoryCreate->title, 600);

                if (!$image) {
                    $json["message"] = $upload->message()->render();
                    echo json_encode($json);
                    return;
                }

                $categoryCreate->image = $image;
            }

            if (!$categoryCreate->save()) {
                $json["message"] = $categoryCreate->message()->render();
                echo json_encode($json);
                return;
            }

            $this->message->success("Categoria cadastrada com sucesso...")->flash();
            $json["redirect"] = url("/admin/categories/category/{$categoryCreate->id}");

            echo json_encode($json);
            return;
        }

        //update
        if (!empty($data["action"]) && $data["action"] == "update") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $categoryUpdate = (new Category())->findById($data["category_id"]);

            if (!$categoryUpdate) {
                $this->message->error("Você tentou gerenciar uma categoria que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/categories/home")]);
                return;
            }

            $categoryUpdate->title = $data["title"];

            //upload image
            if (!empty($_FILES["image"])) {
                if ($categoryUpdate->image && file_exists(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$categoryUpdate->image}")) {
                    unlink(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$categoryUpdate->image}");
                    (new Thumb())->flush($categoryUpdate->image);
                }

                $files = $_FILES["image"];
                $upload = new Upload();
                $image = $upload->image($files, $categoryUpdate->title, 600);

                if (!$image) {
                    $json["message"] = $upload->message()->render();
                    echo json_encode($json);
                    return;
                }

                $categoryUpdate->image = $image;
            }

            if (!$categoryUpdate->save()) {
                $json["message"] = $categoryUpdate->message()->render();
                echo json_encode($json);
                return;
            }

            $this->message->success("Categoria atualizada com sucesso...")->flash();
            echo json_encode(["reload" => true]);
            return;
        }

        //delete
        if (!empty($data["action"]) && $data["action"] == "delete") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $categoryDelete = (new Category())->findById($data["category_id"]);

            if (!$categoryDelete) {
                $this->message->error("Você tentou deletar uma categoria que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/categories/home")]);
                return;
            }

            if ($categoryDelete->image && file_exists(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$categoryDelete->image}")) {
                unlink(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$categoryDelete->image}");
                (new Thumb())->flush($categoryDelete->image);
            }

            $categoryDelete->destroy();

            $this->message->success("A categoria foi excluída com sucesso...")->flash();
            echo json_encode(["redirect" => url("/admin/categories/home")]);

            return;
        }

        $categoryEdit = null;
        if (!empty($data["category_id"])) {
            $categoryId = filter_var($data["category_id"], FILTER_VALIDATE_INT);
            $categoryEdit = (new Category())->findById($categoryId);
        }

        $head = $this->seo->render(
            CONF_SITE_NAME . " | " . ($categoryEdit ? "Perfil de {$categoryEdit->title}" : "Nova Categoria"),
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/categories/category", [
            "app" => "categories/home",
            "head" => $head,
            "category" => $categoryEdit
        ]);
    }

    public function selectCategory(?array $data): void
    {
        if ($data) {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $subCategories = (new SubCategory())->find('id_category = :idc', "idc={$data['category_id']}")->fetch(true);
            $subCategoriesArr = null;
            if ($subCategories) {
                for ($i=0; $i < count($subCategories); $i++) { 
                    $subCategoriesArr[] = $subCategories[$i]->data();
                }
            }
            echo json_encode($subCategoriesArr);
        }
    }
}