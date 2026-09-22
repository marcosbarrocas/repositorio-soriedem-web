<?php

namespace Source\App\Admin;

use Source\Models\Category;
use Source\Models\SubCategory;
use Source\Support\Pager;
use Source\Support\Thumb;
use Source\Support\Upload;

/**
 * Class SubCategories
 * @package Source\App\Admin
 */
class SubCategories extends Admin
{
    /**
     * SubCategories constructor.
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
            echo json_encode(["redirect" => url("/admin/sub-categories/home/{$s}/1")]);
            return;
        }

        $search = null;
        $subCategories = (new SubCategory())->find();

        if (!empty($data["search"]) && str_search($data["search"]) != "all") {
            $search = str_search($data["search"]);
            $subCategories = (new SubCategory())->find("title LIKE CONCAT('%', :s, '%')", "s={$search}");
            if (!$subCategories->count()) {
                $this->message->info("Sua pesquisa não retornou resultados")->flash();
                redirect("/admin/sub-categories/home");
            }
        }

        $all = ($search ?? "all");
        $pager = new Pager(url("/admin/sub-categories/home/{$all}/"));
        $pager->pager($subCategories->count(), 20, (!empty($data["page"]) ? $data["page"] : 1));

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Sub Categorias",
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/sub-categories/home", [
            "app" => "sub-categories/home",
            "head" => $head,
            "search" => $search,
            "subCategories" => $subCategories->limit($pager->limit())->offset($pager->offset())->fetch(true),
            "paginator" => $pager->render()
        ]);
    }

    /**
     * @param array|null $data
     * @throws \Exception
     */
    public function subCategory(?array $data): void
    {
        //create
        if (!empty($data["action"]) && $data["action"] == "create") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

            $subCategoryCreate = new SubCategory();
            $subCategoryCreate->title = $data["title"];
            $subCategoryCreate->id_category = $data["id_category"];

            //upload image
            if (!empty($_FILES["image"])) {
                $files = $_FILES["image"];
                $upload = new Upload();
                $image = $upload->image($files, $subCategoryCreate->title, 600);

                if (!$image) {
                    $json["message"] = $upload->message()->render();
                    echo json_encode($json);
                    return;
                }

                $subCategoryCreate->image = $image;
            }

            if (!$subCategoryCreate->save()) {
                $json["message"] = $subCategoryCreate->message()->render();
                echo json_encode($json);
                return;
            }

            $this->message->success("Sub Categoria cadastrada com sucesso...")->flash();
            $json["redirect"] = url("/admin/sub-categories/sub-category/{$subCategoryCreate->id}");

            echo json_encode($json);
            return;
        }

        //update
        if (!empty($data["action"]) && $data["action"] == "update") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $subCategoryUpdate = (new SubCategory())->findById($data["sub-category_id"]);

            if (!$subCategoryUpdate) {
                $this->message->error("Você tentou gerenciar uma sub categoria que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/sub-categories/home")]);
                return;
            }

            $subCategoryUpdate->title = $data["title"];
            $subCategoryUpdate->id_category = $data["id_category"];

            //upload image
            if (!empty($_FILES["image"])) {
                if ($subCategoryUpdate->image && file_exists(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$subCategoryUpdate->image}")) {
                    unlink(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$subCategoryUpdate->image}");
                    (new Thumb())->flush($subCategoryUpdate->image);
                }

                $files = $_FILES["image"];
                $upload = new Upload();
                $image = $upload->image($files, $subCategoryUpdate->title, 600);

                if (!$image) {
                    $json["message"] = $upload->message()->render();
                    echo json_encode($json);
                    return;
                }

                $subCategoryUpdate->image = $image;
            }

            if (!$subCategoryUpdate->save()) {
                $json["message"] = $subCategoryUpdate->message()->render();
                echo json_encode($json);
                return;
            }

            $this->message->success("Sub Categoria atualizada com sucesso...")->flash();
            echo json_encode(["reload" => true]);
            return;
        }

        //delete
        if (!empty($data["action"]) && $data["action"] == "delete") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $subCategoryDelete = (new SubCategory())->findById($data["sub-category_id"]);

            if (!$subCategoryDelete) {
                $this->message->error("Você tentou deletar uma sub categoria que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/sub-categories/home")]);
                return;
            }

            if ($subCategoryDelete->image && file_exists(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$subCategoryDelete->image}")) {
                unlink(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$subCategoryDelete->image}");
                (new Thumb())->flush($subCategoryDelete->image);
            }

            $subCategoryDelete->destroy();

            $this->message->success("A sub categoria foi excluída com sucesso...")->flash();
            echo json_encode(["redirect" => url("/admin/sub-categories/home")]);

            return;
        }

        $subCategoryEdit = null;
        if (!empty($data["sub-category_id"])) {
            $subCategoryId = filter_var($data["sub-category_id"], FILTER_VALIDATE_INT);
            $subCategoryEdit = (new SubCategory())->findById($subCategoryId);
        }

        $head = $this->seo->render(
            CONF_SITE_NAME . " | " . ($subCategoryEdit ? "Perfil de {$subCategoryEdit->title}" : "Nova Sub Categoria"),
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/sub-categories/sub-category", [
            "app" => "sub-categories/home",
            "head" => $head,
            "subCategory" => $subCategoryEdit,
            "categories" => (new Category())->find()->fetch(true)
        ]);
    }
}