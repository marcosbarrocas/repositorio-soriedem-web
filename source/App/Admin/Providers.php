<?php

namespace Source\App\Admin;

use Source\Models\Provider;
use Source\Support\Pager;
use Source\Support\Thumb;
use Source\Support\Upload;

/**
 * Class Providers
 * @package Source\App\Admin
 */
class Providers extends Admin
{
    /**
     * Providers constructor.
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
            echo json_encode(["redirect" => url("/admin/providers/home/{$s}/1")]);
            return;
        }

        $search = null;
        $providers = (new Provider())->find();

        if (!empty($data["search"]) && str_search($data["search"]) != "all") {
            $search = str_search($data["search"]);
            $providers = (new Provider())->find("title LIKE CONCAT('%', :s, '%')", "s={$search}");
            if (!$providers->count()) {
                $this->message->info("Sua pesquisa não retornou resultados")->flash();
                redirect("/admin/providers/home");
            }
        }

        $all = ($search ?? "all");
        $pager = new Pager(url("/admin/providers/home/{$all}/"));
        $pager->pager($providers->count(), 20, (!empty($data["page"]) ? $data["page"] : 1));

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Fornecedores",
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/providers/home", [
            "app" => "providers/home",
            "head" => $head,
            "search" => $search,
            "providers" => $providers->limit($pager->limit())->offset($pager->offset())->fetch(true),
            "paginator" => $pager->render()
        ]);
    }

    /**
     * @param array|null $data
     * @throws \Exception
     */
    public function provider(?array $data): void
    {
        //create
        if (!empty($data["action"]) && $data["action"] == "create") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

            $providerCreate = new Provider();
            $providerCreate->title = $data["title"];

            //upload image
            if (!empty($_FILES["image"])) {
                $files = $_FILES["image"];
                $upload = new Upload();
                $image = $upload->image($files, $providerCreate->title, 600);

                if (!$image) {
                    $json["message"] = $upload->message()->render();
                    echo json_encode($json);
                    return;
                }

                $providerCreate->image = $image;
            }

            if (!$providerCreate->save()) {
                $json["message"] = $providerCreate->message()->render();
                echo json_encode($json);
                return;
            }

            $this->message->success("Fornecedor cadastrado com sucesso...")->flash();
            $json["redirect"] = url("/admin/providers/provider/{$providerCreate->id}");

            echo json_encode($json);
            return;
        }

        //update
        if (!empty($data["action"]) && $data["action"] == "update") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $providerUpdate = (new Provider())->findById($data["provider_id"]);

            if (!$providerUpdate) {
                $this->message->error("Você tentou gerenciar um fornecedor que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/providers/home")]);
                return;
            }

            $providerUpdate->title = $data["title"];

            //upload image
            if (!empty($_FILES["image"])) {
                if ($providerUpdate->image && file_exists(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$providerUpdate->image}")) {
                    unlink(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$providerUpdate->image}");
                    (new Thumb())->flush($providerUpdate->image);
                }

                $files = $_FILES["image"];
                $upload = new Upload();
                $image = $upload->image($files, $providerUpdate->title, 600);

                if (!$image) {
                    $json["message"] = $upload->message()->render();
                    echo json_encode($json);
                    return;
                }

                $providerUpdate->image = $image;
            }

            if (!$providerUpdate->save()) {
                $json["message"] = $providerUpdate->message()->render();
                echo json_encode($json);
                return;
            }

            $this->message->success("Fornecedor atualizado com sucesso...")->flash();
            echo json_encode(["reload" => true]);
            return;
        }

        //delete
        if (!empty($data["action"]) && $data["action"] == "delete") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $providerDelete = (new Provider())->findById($data["provider_id"]);

            if (!$providerDelete) {
                $this->message->error("Você tentnou deletar um fornecedor que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/providers/home")]);
                return;
            }

            if ($providerDelete->image && file_exists(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$providerDelete->image}")) {
                unlink(__DIR__ . "/../../../" . CONF_UPLOAD_DIR . "/{$providerDelete->image}");
                (new Thumb())->flush($providerDelete->image);
            }

            $providerDelete->destroy();

            $this->message->success("O fornecedor foi excluído com sucesso...")->flash();
            echo json_encode(["redirect" => url("/admin/providers/home")]);

            return;
        }

        $providerEdit = null;
        if (!empty($data["provider_id"])) {
            $providerId = filter_var($data["provider_id"], FILTER_VALIDATE_INT);
            $providerEdit = (new Provider())->findById($providerId);
        }

        $head = $this->seo->render(
            CONF_SITE_NAME . " | " . ($providerEdit ? "Perfil de {$providerEdit->title}" : "Novo Fornecedor"),
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/providers/provider", [
            "app" => "providers/home",
            "head" => $head,
            "provider" => $providerEdit
        ]);
    }
}