<?php

namespace Source\App\Admin;

use Source\Models\Category;
use Source\Models\ClientProducts;
use Source\Models\Product;
use Source\Models\Provider;
use Source\Models\SubCategory;
use Source\Support\OmieSync;
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
     * Lista os produtos e filtra por código ou título.
     *
     * O campo de busca envia o termo em "s". A listagem filtrada usa o caminho
     * /admin/products/home/{termo}/{página}. O termo compara código e título.
     * Só entram produtos ativos (status = 1). Os cadastros antigos, sem o
     * prefixo PRD, ficam inativos e não aparecem nesta tela.
     *
     * O botão Atualizar da Omie envia action=sync em segundo plano. A lista
     * permanece na tela, com o aviso de carregamento, enquanto ListarProdutos
     * percorre a API e grava código, título, valor, foto, estoque e situação.
     * Ao terminar, a resposta pede para recarregar esta mesma lista, onde a
     * mensagem de sucesso ou de erro aparece.
     *
     * @param array|null $data Dados da rota e do POST. "action" = "sync" atualiza a Omie. "s" é o texto digitado. "search" e "page" vêm da URL da listagem filtrada.
     * @return void Não devolve valor. Mostra a listagem, devolve o recarregamento em JSON depois da Omie, ou redireciona a busca.
     */
    public function home(?array $data): void
    {
        if (!empty($data["action"]) && $data["action"] === "sync") {
            set_time_limit(300);
            $sync = new OmieSync();
            if (!$sync->syncProdutos()) {
                $this->message->error("Erro ao consultar a Omie: " . $sync->error())->flash();
            } else {
                $stats = $sync->stats();
                $inseridos = (int) ($stats["produtos_inseridos"] ?? 0);
                $atualizados = (int) ($stats["produtos_atualizados"] ?? 0);
                $this->message->success("Produtos atualizados a partir da Omie. {$inseridos} novos e {$atualizados} atualizados.")->flash();
            }
            echo json_encode(["redirect" => url("/admin/products/home")]);
            return;
        }

        if (!empty($data["s"])) {
            $s = $this->productSearchSlug($this->productSearchTerm($data["s"]));
            echo json_encode(["redirect" => url("/admin/products/home/{$s}/1")]);
            return;
        }

        $search = null;
        $products = (new Product())->find("status = :status", "status=1");

        $term = $this->productSearchTerm($data["search"] ?? null);
        if ($term !== "all") {
            $search = $term;
            $products = (new Product())->find(
                "status = :status AND (title LIKE CONCAT('%', :s, '%') OR code LIKE CONCAT('%', :s, '%'))",
                "status=1&s=" . rawurlencode($search)
            );
            if (!$products->count()) {
                $this->message->info("Sua pesquisa não retornou resultados")->flash();
                redirect("/admin/products/home");
            }
        }

        $all = ($search ? $this->productSearchSlug($search) : "all");
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
     * Busca produtos por nome ou código, independente de categoria.
     *
     * Usada na cesta para associar produtos vindos da Omie e na tela de fotos
     * pendentes. O valor unitário da Omie vem com ponto decimal e casas extras,
     * como "137.445". money_br() corta na segunda casa e devolve "137,44",
     * no formato que a máscara do campo de preço da cesta espera.
     *
     * @param array|null $data POST com "q" (nome ou código, mínimo de 2 caracteres) e, na tela de fotos, "only_with_photo" = "1" para trazer só produtos que já têm imagem.
     * @return void Não devolve valor. Imprime um JSON com até 50 produtos. Cada item traz os dados do cadastro e o campo value já em reais brasileiros. Lista vazia quando o termo é curto ou não há resultado.
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
                $row = $p->data();
                $row->value = money_br(isset($row->value) ? (string) $row->value : null);
                $out[] = $row;
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

    /**
     * Normaliza o texto da busca de produtos para a consulta no banco.
     *
     * O termo pode chegar do campo "s" ou do trecho {search} da URL. Na URL o
     * espaço é trocado por "+", porque um espaço no caminho quebra a rota no
     * Apache. Aqui o "+" (e um eventual %20) volta a ser espaço. Em seguida o
     * texto passa por str_search, que mantém letras, números, @ e espaço.
     * Sem texto, o retorno é "all", o mesmo valor da listagem sem filtro.
     *
     * @param string|null $raw Texto digitado ou vindo da URL. Null quando a listagem abre sem busca.
     * @return string Termo usado no LIKE de código e título, ou "all" quando a busca está vazia.
     */
    private function productSearchTerm(?string $raw): string
    {
        $raw = str_replace(["+", "%20"], " ", (string) $raw);
        return str_search($raw);
    }

    /**
     * Monta o termo da busca no formato seguro para o caminho da listagem.
     *
     * Troca cada espaço por "+" para a URL /admin/products/home/{termo}/{página}
     * continuar inteira. productSearchTerm() desfaz essa troca na consulta.
     *
     * @param string $term Termo já normalizado por productSearchTerm().
     * @return string O mesmo termo, com espaços substituídos por "+".
     */
    private function productSearchSlug(string $term): string
    {
        return str_replace(" ", "+", $term);
    }
}
