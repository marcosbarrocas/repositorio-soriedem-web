<?php

namespace Source\App\Admin;

use Source\Core\Connect;
use Source\Models\Seller;
use Source\Support\Omie;
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
     * Página de gerenciamento de LOGIN dos vendedores.
     *
     * A Omie é a fonte dos vendedores, mas não tem autenticação de vendedor —
     * o login (email/senha) vive na tabela `sellers`, mapeado por `omie_codigo`.
     * Aqui listamos os vendedores da Omie e mostramos quem já tem login, com
     * ação para criar/gerenciar o acesso ao app.
     *
     * @param array|null $data
     */
    public function omie(?array $data): void
    {
        $pdo = Connect::getInstance();
        $erro = null;

        // Ação: atualizar o cache local a partir da Omie (sob demanda, evita
        // bater no rate-limit "REDUNDANT" da Omie a cada carregamento).
        if (!empty($data["action"]) && $data["action"] === "sync") {
            $sync = new \Source\Support\OmieSync();
            if (!$sync->syncVendedores()) {
                $this->message->error("Erro ao consultar a Omie: " . $sync->error())->flash();
            } else {
                $this->message->success("Vendedores atualizados a partir da Omie.")->flash();
            }
            redirect("/admin/sellers/omie");
            return;
        }

        // Lê os vendedores do cache local. Se estiver vazio (primeira vez),
        // busca da Omie uma vez e popula o cache.
        $vendedores = $pdo->query("SELECT codigo, nome, email, inativo FROM omie_vendedores ORDER BY nome")
            ->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($vendedores)) {
            $sync = new \Source\Support\OmieSync();
            if ($sync->syncVendedores()) {
                $vendedores = $pdo->query("SELECT codigo, nome, email, inativo FROM omie_vendedores ORDER BY nome")
                    ->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                $erro = $sync->error();
            }
        }

        // sellers locais indexados por omie_codigo e por email (p/ status de login)
        $sellers = (new Seller())->find()->fetch(true) ?: [];
        $byCodigo = [];
        $byEmail = [];
        foreach ($sellers as $s) {
            if (!empty($s->omie_codigo)) {
                $byCodigo[(string)$s->omie_codigo] = $s;
            }
            if (!empty($s->email)) {
                $byEmail[mb_strtolower($s->email)] = $s;
            }
        }

        // monta a lista combinada (vendedor Omie + status do login local)
        $linhas = [];
        foreach ($vendedores as $v) {
            $codigo = (string)($v["codigo"] ?? "");
            $email = trim((string)($v["email"] ?? ""));
            $seller = $byCodigo[$codigo]
                ?? ($email !== "" ? ($byEmail[mb_strtolower($email)] ?? null) : null);

            $linhas[] = [
                "codigo" => $codigo,
                "nome" => (string)($v["nome"] ?? ""),
                "email" => $email,
                "inativo" => (bool)($v["inativo"] ?? 0),
                "seller" => $seller, // objeto Seller ou null
            ];
        }

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Vendedores (Omie) e Logins",
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/sellers/omie", [
            "app" => "sellers/omie",
            "head" => $head,
            "linhas" => $linhas,
            "erro" => $erro,
        ]);
    }

    /**
     * Cria (ou vincula) o login de acesso ao app para um vendedor da Omie.
     * POST { omie_codigo, nome, email, password }
     * @param array|null $data
     */
    public function createLogin(?array $data): void
    {
        $data = filter_var_array((array)$data, FILTER_SANITIZE_STRIPPED);

        $codigo = filter_var($data["omie_codigo"] ?? null, FILTER_VALIDATE_INT);
        $nome = trim((string)($data["nome"] ?? ""));
        $email = trim((string)($data["email"] ?? ""));
        $password = (string)($data["password"] ?? "");

        if (!$codigo || $email === "") {
            echo json_encode(["message" => $this->message->warning("Código Omie e email são obrigatórios.")->render()]);
            return;
        }
        if (!is_email($email)) {
            echo json_encode(["message" => $this->message->warning("O e-mail informado não é válido.")->render()]);
            return;
        }
        if (mb_strlen($password) < CONF_PASSWD_MIN_LEN) {
            $min = CONF_PASSWD_MIN_LEN;
            echo json_encode(["message" => $this->message->warning("A senha deve ter ao menos {$min} caracteres.")->render()]);
            return;
        }

        // separa nome em primeiro/sobrenome
        $partes = preg_split('/\s+/', $nome, 2);
        $firstName = $partes[0] ?? $nome;
        $lastName = $partes[1] ?? "";

        $pdo = Connect::getInstance();

        // já existe seller com esse email? então vincula/atualiza (evita duplicar)
        $stmt = $pdo->prepare("SELECT id FROM sellers WHERE email = :email LIMIT 1");
        $stmt->execute([":email" => $email]);
        $existingId = $stmt->fetchColumn();

        $hash = passwd($password);

        if ($existingId) {
            $upd = $pdo->prepare(
                "UPDATE sellers SET first_name = :fn, last_name = :ln, password = :pw, omie_codigo = :oc WHERE id = :id"
            );
            $upd->execute([
                ":fn" => $firstName, ":ln" => $lastName, ":pw" => $hash,
                ":oc" => $codigo, ":id" => (int)$existingId,
            ]);
            $this->message->success("Login atualizado com sucesso para {$nome}.")->flash();
        } else {
            $ins = $pdo->prepare(
                "INSERT INTO sellers (first_name, last_name, phone, document, email, password, omie_codigo)
                 VALUES (:fn, :ln, '', '', :email, :pw, :oc)"
            );
            $ins->execute([
                ":fn" => $firstName, ":ln" => $lastName, ":email" => $email,
                ":pw" => $hash, ":oc" => $codigo,
            ]);
            $this->message->success("Login criado com sucesso para {$nome}.")->flash();
        }

        echo json_encode(["reload" => true]);
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