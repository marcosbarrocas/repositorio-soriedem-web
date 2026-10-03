<?php

namespace Source\App\Admin;

use Source\Core\Connect;
use Source\Models\Client;
use Source\Models\Seller;
use Source\Support\OmieSync;
use Source\Support\Pager;

/**
 * Class Clients
 * @package Source\App\Admin
 */
class Clients extends Admin
{
    /**
     * Clients constructor.
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
            echo json_encode(["redirect" => url("/admin/clients/home/{$s}/1")]);
            return;
        }

        $search = null;
        $clients = (new Client())->find();

        if (!empty($data["search"]) && str_search($data["search"]) != "all") {
            $search = str_search($data["search"]);
            $clients = (new Client())->find("corporate_name LIKE CONCAT('%', :s, '%') OR contact_name LIKE CONCAT('%', :s, '%') OR email LIKE CONCAT('%', :s, '%')", "s={$search}");
            if (!$clients->count()) {
                $this->message->info("Sua pesquisa não retornou resultados")->flash();
                redirect("/admin/clients/home");
            }
        }

        $all = ($search ?? "all");
        $pager = new Pager(url("/admin/clients/home/{$all}/"));
        $pager->pager($clients->count(), 20, (!empty($data["page"]) ? $data["page"] : 1));

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Clientes",
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/clients/home", [
            "app" => "clients/home",
            "head" => $head,
            "search" => $search,
            "clients" => $clients->limit($pager->limit())->offset($pager->offset())->fetch(true),
            "paginator" => $pager->render()
        ]);
    }

    /**
     * Lista os clientes vindos da API da Omie.
     *
     * A Omie é a fonte dos clientes. A consulta ListarClientes é gravada na
     * tabela clients (código, nome fantasia, razão social, CNPJ e endereço).
     * Esta tela lê esse cadastro, para não chamar a Omie a cada abertura.
     * O botão Atualizar da Omie dispara a sincronização de todas as páginas.
     * Se o cadastro local ainda estiver vazio, a sincronização roda uma vez
     * ao abrir a página.
     *
     * A busca aceita nome fantasia, razão social ou CNPJ. O termo vai no
     * caminho /admin/clients/omie/{termo}/{página}, com espaços trocados por
     * "+", porque espaço no endereço quebra a rota.
     *
     * O filtro por vendedor usa o código gravado na Omie
     * (clients.omie_codigo_vendedor). A lista fica em
     * /admin/clients/omie/vendedor/{código}/{termo}/{página}. A busca por
     * texto, quando usada junto, continua limitada ao vendedor selecionado.
     *
     * @param array|null $data Dados da rota e do POST. "action" = "sync" atualiza a Omie. "s" é o texto digitado na busca. "vendedor" é o código Omie do vendedor. "search" e "page" vêm da URL da listagem filtrada.
     * @return void Não devolve valor. Mostra a listagem ou redireciona depois da busca e da atualização.
     */
    public function omie(?array $data): void
    {
        if (!empty($data["action"]) && $data["action"] === "sync") {
            $sync = new OmieSync();
            if (!$sync->syncClientes()) {
                $this->message->error("Erro ao consultar a Omie: " . $sync->error())->flash();
            } else {
                $stats = $sync->stats();
                $inseridos = (int) ($stats["clientes_inseridos"] ?? 0);
                $atualizados = (int) ($stats["clientes_atualizados"] ?? 0);
                $this->message->success("Clientes atualizados a partir da Omie. {$inseridos} novos e {$atualizados} atualizados.")->flash();
            }
            echo json_encode(["redirect" => url("/admin/clients/omie")]);
            return;
        }

        $vendedor = $this->codigoVendedor($data["vendedor"] ?? null);

        if (array_key_exists("s", $data)) {
            $term = $this->clientSearchTerm($data["s"]);
            $slug = ($term === "all" ? "all" : $this->clientSearchSlug($term));
            $vendedorBusca = $this->codigoVendedor($data["vendedor"] ?? null);
            if ($vendedorBusca) {
                $destino = url("/admin/clients/omie/vendedor/{$vendedorBusca}/{$slug}/1");
            } elseif ($term === "all") {
                $destino = url("/admin/clients/omie");
            } else {
                $destino = url("/admin/clients/omie/{$slug}/1");
            }
            echo json_encode(["redirect" => $destino]);
            return;
        }

        $omie = "IFNULL(code, '') <> ''";
        $where = $omie;
        $params = [];
        if ($vendedor) {
            $where .= " AND omie_codigo_vendedor = :vendedor";
            $params[] = "vendedor={$vendedor}";
        }

        $search = null;
        $term = $this->clientSearchTerm($data["search"] ?? null);
        if ($term !== "all") {
            $search = $term;
            $where .= " AND (contact_name LIKE CONCAT('%', :s, '%') OR corporate_name LIKE CONCAT('%', :s, '%') OR REPLACE(REPLACE(REPLACE(IFNULL(cnpj, ''), '.', ''), '/', ''), '-', '') LIKE CONCAT('%', :s, '%'))";
            $params[] = "s=" . rawurlencode($search);
        }

        $clients = (new Client())->find($where, implode("&", $params))->order("contact_name ASC");

        if ($search && !$clients->count()) {
            $this->message->info("Sua pesquisa não retornou resultados")->flash();
            redirect($vendedor
                ? "/admin/clients/omie/vendedor/{$vendedor}/all/1"
                : "/admin/clients/omie");
            return;
        }

        if (!$vendedor && !$search && !(new Client())->find($omie, "")->count()) {
            $sync = new OmieSync();
            if (!$sync->syncClientes()) {
                $this->message->error("Erro ao consultar a Omie: " . $sync->error())->flash();
            }
            $clients = (new Client())->find($omie, "")->order("contact_name ASC");
        }

        $vendedores = $this->vendedoresDoFiltro();
        $vendedorNome = null;
        foreach ($vendedores as $item) {
            if ($vendedor && $item["codigo"] === $vendedor) {
                $vendedorNome = $item["nome"];
                break;
            }
        }

        $all = ($search ? $this->clientSearchSlug($search) : "all");
        $base = $vendedor
            ? url("/admin/clients/omie/vendedor/{$vendedor}/{$all}/")
            : url("/admin/clients/omie/{$all}/");
        $pager = new Pager($base);
        $pager->pager($clients->count(), 20, (!empty($data["page"]) ? $data["page"] : 1));

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Clientes",
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/clients/omie", [
            "app" => "clients/omie",
            "head" => $head,
            "search" => $search,
            "vendedor" => $vendedor,
            "vendedorNome" => $vendedorNome,
            "vendedores" => $vendedores,
            "clients" => $clients->limit($pager->limit())->offset($pager->offset())->fetch(true),
            "paginator" => $pager->render(),
        ]);
    }

    /**
     * Normaliza o texto da busca de clientes.
     *
     * O termo pode vir do campo "s" ou do trecho {search} da URL. Na URL o
     * espaço é trocado por "+". Aqui o "+" volta a ser espaço e o texto passa
     * por str_search, que mantém letras, números, @ e espaço. Sem texto, o
     * retorno é "all".
     *
     * @param string|null $raw Texto digitado ou vindo da URL. Null quando a listagem abre sem busca.
     * @return string Termo usado no LIKE de nome fantasia, razão social e CNPJ, ou "all" quando a busca está vazia.
     */
    private function clientSearchTerm(?string $raw): string
    {
        $raw = str_replace(["+", "%20"], " ", (string) $raw);
        return str_search($raw);
    }

    /**
     * Monta o termo da busca no formato seguro para o caminho da listagem.
     *
     * Troca cada espaço por "+" em /admin/clients/omie/{termo}/{página}.
     * clientSearchTerm() desfaz essa troca na consulta.
     *
     * @param string $term Termo já normalizado por clientSearchTerm().
     * @return string O mesmo termo, com espaços substituídos por "+".
     */
    private function clientSearchSlug(string $term): string
    {
        return str_replace(" ", "+", $term);
    }

    /**
     * Valida o código do vendedor usado no filtro da listagem.
     *
     * O código vem do caminho /admin/clients/omie/vendedor/{código}/... ou do
     * campo oculto da busca. Só aceita dígitos, porque o valor é o
     * codigo do vendedor na Omie (omie_vendedores.codigo e
     * clients.omie_codigo_vendedor). Texto, vazio ou "all" significam que
     * nenhum vendedor foi escolhido.
     *
     * @param string|null $raw Código recebido da rota ou do formulário. Null quando a listagem abre sem filtro.
     * @return string|null O código só com dígitos, pronto para a consulta. Null quando não há vendedor selecionado.
     */
    private function codigoVendedor(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === "" || !ctype_digit($raw)) {
            return null;
        }

        return $raw;
    }

    /**
     * Monta a lista de vendedores exibida no filtro de clientes.
     *
     * Lê a tabela omie_vendedores, que espelha a consulta ListarVendedores da
     * Omie, e conta os clientes cujo omie_codigo_vendedor é o código daquele
     * vendedor. A contagem ignora cadastros locais sem código da Omie.
     * A ordem é o nome do vendedor.
     *
     * @return array<int, array{codigo:string, nome:string, total:int}> Cada item traz o código Omie, o nome e a quantidade de clientes associados. Lista vazia se ainda não houver vendedores gravados.
     */
    private function vendedoresDoFiltro(): array
    {
        $sql = "SELECT v.codigo, v.nome, COUNT(c.id) AS total
                FROM omie_vendedores v
                LEFT JOIN clients c
                    ON c.omie_codigo_vendedor = v.codigo
                   AND IFNULL(c.code, '') <> ''
                GROUP BY v.codigo, v.nome
                ORDER BY v.nome";
        $linhas = Connect::getInstance()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $lista = [];
        foreach ($linhas as $linha) {
            $lista[] = [
                "codigo" => (string) $linha["codigo"],
                "nome" => (string) $linha["nome"],
                "total" => (int) $linha["total"],
            ];
        }

        return $lista;
    }

    /**
     * @param array|null $data
     * @throws \Exception
     */
    public function client(?array $data): void
    {
        //create
        if (!empty($data["action"]) && $data["action"] == "create") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

            $clientCreate = new Client();
            $clientCreate->corporate_name = $data["corporate_name"];
            $clientCreate->phone = clean_mask($data["phone"]);
            $clientCreate->email = $data["email"];
            $clientCreate->contact_name = $data["contact_name"];
            $clientCreate->id_seller = $data["id_seller"];
            $clientCreate->address = $data["address"];
            $clientCreate->number = $data["number"];
            $clientCreate->district = $data["district"];
            $clientCreate->city = $data["city"];
            $clientCreate->state = $data["state"];
            $clientCreate->cnpj = $data["cnpj"];

            if (!$clientCreate->save()) {
                $json["message"] = $clientCreate->message()->render();
                echo json_encode($json);
                return;
            }

            $this->message->success("Cliente cadastrado com sucesso...")->flash();
            $json["redirect"] = url("/admin/clients/client/{$clientCreate->id}");

            echo json_encode($json);
            return;
        }

        //update
        if (!empty($data["action"]) && $data["action"] == "update") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $clientUpdate = (new Client())->findById($data["client_id"]);

            if (!$clientUpdate) {
                $this->message->error("Você tentou gerenciar um cliente que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/clients/home")]);
                return;
            }

            $clientUpdate->corporate_name = $data["corporate_name"];
            $clientUpdate->phone = clean_mask($data["phone"]);
            $clientUpdate->email = $data["email"];
            $clientUpdate->contact_name = $data["contact_name"];
            $clientUpdate->id_seller = $data["id_seller"];
            $clientUpdate->address = $data["address"];
            $clientUpdate->number = $data["number"];
            $clientUpdate->district = $data["district"];
            $clientUpdate->city = $data["city"];
            $clientUpdate->state = $data["state"];
            $clientUpdate->cnpj = $data["cnpj"];

            if (!$clientUpdate->save()) {
                $json["message"] = $clientUpdate->message()->render();
                echo json_encode($json);
                return;
            }

            $this->message->success("Cliente atualizado com sucesso...")->flash();
            echo json_encode(["reload" => true]);
            return;
        }

        //delete
        if (!empty($data["action"]) && $data["action"] == "delete") {
            $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
            $clientDelete = (new Client())->findById($data["client_id"]);

            if (!$clientDelete) {
                $this->message->error("Você tentnou deletar um cliente que não existe")->flash();
                echo json_encode(["redirect" => url("/admin/clients/home")]);
                return;
            }

            $clientDelete->destroy();

            $this->message->success("O cliente foi excluído com sucesso...")->flash();
            echo json_encode(["redirect" => url("/admin/clients/home")]);

            return;
        }

        $clientEdit = null;
        if (!empty($data["client_id"])) {
            $clientId = filter_var($data["client_id"], FILTER_VALIDATE_INT);
            $clientEdit = (new Client())->findById($clientId);
        }

        $head = $this->seo->render(
            CONF_SITE_NAME . " | " . ($clientEdit ? "Perfil de {$clientEdit->fullName()}" : "Novo Cliente"),
            CONF_SITE_DESC,
            url("/admin"),
            url("/admin/assets/images/image.jpg"),
            false
        );

        echo $this->view->render("widgets/clients/client", [
            "app" => "clients/home",
            "head" => $head,
            "client" => $clientEdit,
            "sellers" => (new Seller())->find()->fetch(true)
        ]);
    }
}