<?php

namespace Source\Support;

use Source\Core\Connect;

/**
 * Sincroniza cadastros da Omie para o banco local.
 *
 * A cesta de produtos (clients_products) e as consultas do app usam ids/`code`
 * locais, entao mantemos tabelas espelho de clientes e produtos vindos da Omie,
 * chaveadas por `code`:
 *   - products.code  = Omie "codigo" (ex.: PRD00026)  [codigo_produto no info[]]
 *   - clients.code   = Omie "codigo_cliente_omie"
 *
 * @package Source\Support
 */
class OmieSync
{
    /** @var Omie */
    private Omie $omie;

    /** @var \PDO */
    private \PDO $pdo;

    /** @var array<string,int> contadores da ultima execucao */
    private array $stats = [];

    public function __construct()
    {
        $this->omie = new Omie();
        $this->pdo = Connect::getInstance();
    }

    /** @return array<string,int> */
    public function stats(): array
    {
        return $this->stats;
    }

    /** @return string|null */
    public function error(): ?string
    {
        return $this->omie->error();
    }

    /**
     * Sincroniza todos os produtos da Omie (paginado).
     * @return bool
     */
    public function syncProdutos(int $porPagina = 100): bool
    {
        $inserted = $updated = 0;
        $pagina = 1;
        do {
            $resp = $this->omie->listarProdutos($pagina, $porPagina);
            if ($resp === null) {
                return false;
            }
            $lista = $resp["produto_servico_cadastro"] ?? [];
            foreach ($lista as $p) {
                $code = (string) ($p["codigo"] ?? $p["codigo_produto"] ?? "");
                if ($code === "") {
                    continue;
                }
                $omieCodigo = isset($p["codigo_produto"]) ? (int) $p["codigo_produto"] : null;
                $title = trim((string) ($p["descricao"] ?? ""));
                $value = isset($p["valor_unitario"]) ? (string) $p["valor_unitario"] : null;
                $imagens = $p["imagens"] ?? [];
                $photo = is_array($imagens) && !empty($imagens)
                    ? ($imagens[0]["url_imagem"] ?? $imagens[0]["url"] ?? null)
                    : null;
                $visible = (($p["inativo"] ?? "N") === "S") ? 0 : 1;
                $stock = isset($p["quantidade_estoque"]) ? (string) $p["quantidade_estoque"] : null;

                if ($this->upsertProduct($code, $omieCodigo, $title, $value, $photo, $visible, $stock)) {
                    $updated++;
                } else {
                    $inserted++;
                }
            }
            $totalPaginas = (int) ($resp["total_de_paginas"] ?? 1);
            $pagina++;
        } while ($pagina <= $totalPaginas);

        $this->stats["produtos_inseridos"] = $inserted;
        $this->stats["produtos_atualizados"] = $updated;
        return true;
    }

    /**
     * Sincroniza todos os clientes da Omie (paginado).
     * @return bool
     */
    public function syncClientes(int $porPagina = 100): bool
    {
        $inserted = $updated = 0;
        $pagina = 1;
        do {
            $resp = $this->omie->listarClientes($pagina, $porPagina);
            if ($resp === null) {
                return false;
            }
            $lista = $resp["clientes_cadastro"] ?? [];
            foreach ($lista as $c) {
                $code = (string) ($c["codigo_cliente_omie"] ?? "");
                if ($code === "") {
                    continue;
                }
                // O vendedor do cliente vem aninhado em recomendacoes.codigo_vendedor
                $codVendedor = $c["recomendacoes"]["codigo_vendedor"] ?? null;
                $row = [
                    "corporate_name" => trim((string) ($c["razao_social"] ?? $c["nome_fantasia"] ?? "")),
                    "address" => (string) ($c["endereco"] ?? ""),
                    "number" => (string) ($c["endereco_numero"] ?? ""),
                    "district" => (string) ($c["bairro"] ?? ""),
                    "city" => (string) ($c["cidade"] ?? ""),
                    "state" => (string) ($c["estado"] ?? ""),
                    "phone" => (string) ($c["telefone1_numero"] ?? ""),
                    "email" => (string) ($c["email"] ?? ""),
                    "contact_name" => (string) ($c["nome_fantasia"] ?? $c["razao_social"] ?? ""),
                    "cnpj" => (string) ($c["cnpj_cpf"] ?? ""),
                    "omie_codigo_vendedor" => $codVendedor ? (int) $codVendedor : null,
                ];
                if ($this->upsertClient($code, $row)) {
                    $updated++;
                } else {
                    $inserted++;
                }
            }
            $totalPaginas = (int) ($resp["total_de_paginas"] ?? 1);
            $pagina++;
        } while ($pagina <= $totalPaginas);

        $this->stats["clientes_inseridos"] = $inserted;
        $this->stats["clientes_atualizados"] = $updated;
        return true;
    }

    /**
     * Busca os vendedores na API da Omie e grava o cache local.
     *
     * A Omie é a fonte dos vendedores do sistema e do app. Cada página da
     * consulta ListarVendedores é gravada em omie_vendedores (código, nome,
     * e-mail e se está inativo). Em seguida tenta ligar cada vendedor a um
     * login já existente em sellers, primeiro pelo e-mail e, se não achar,
     * pelo nome. Quando encontra, preenche sellers.omie_codigo.
     *
     * Vendedores da Omie que não têm login local ficam na estatística
     * vendedores_sem_match_lista. No terminal (php bin/omie-sync.php) essa
     * lista também é escrita em STDERR. No painel web STDERR não existe, então
     * a lista só volta em stats() para a mensagem da tela.
     *
     * @param int $porPagina Quantidade de vendedores pedida em cada página da Omie. Padrão 100.
     * @return bool true quando a Omie respondeu e o cache foi atualizado. false quando a consulta falha; o motivo fica em error().
     */
    public function syncVendedores(int $porPagina = 100): bool
    {
        $matched = 0;
        $unmatched = [];
        $pagina = 1;
        do {
            $resp = $this->omie->listarVendedores($pagina, $porPagina);
            if ($resp === null) {
                return false;
            }
            $lista = $resp["cadastro"] ?? [];
            foreach ($lista as $v) {
                $codigo = (int) ($v["codigo"] ?? 0);
                $email = trim((string) ($v["email"] ?? ""));
                $nome = trim((string) ($v["nome"] ?? ""));
                if ($codigo === 0) {
                    continue;
                }

                // cacheia o vendedor localmente (evita bater na Omie a cada page load)
                $cache = $this->pdo->prepare(
                    "INSERT INTO omie_vendedores (codigo, nome, email, inativo) VALUES (:c, :n, :e, :i)
                     ON DUPLICATE KEY UPDATE nome = :n2, email = :e2, inativo = :i2"
                );
                $inativo = (($v["inativo"] ?? "N") === "S") ? 1 : 0;
                $cache->execute([
                    ":c" => $codigo, ":n" => $nome, ":e" => $email, ":i" => $inativo,
                    ":n2" => $nome, ":e2" => $email, ":i2" => $inativo,
                ]);

                $sellerId = null;
                if ($email !== "") {
                    $stmt = $this->pdo->prepare("SELECT id FROM sellers WHERE LOWER(email) = LOWER(:e) LIMIT 1");
                    $stmt->execute([":e" => $email]);
                    $sellerId = $stmt->fetchColumn() ?: null;
                }
                if (!$sellerId && $nome !== "") {
                    // casa pelo primeiro nome (normalizado)
                    $stmt = $this->pdo->prepare(
                        "SELECT id FROM sellers WHERE LOWER(TRIM(first_name)) = LOWER(:n)
                         OR LOWER(TRIM(CONCAT(first_name,' ',last_name))) = LOWER(:n2) LIMIT 1"
                    );
                    $first = explode(" ", $nome)[0];
                    $stmt->execute([":n" => $first, ":n2" => $nome]);
                    $sellerId = $stmt->fetchColumn() ?: null;
                }

                if ($sellerId) {
                    $u = $this->pdo->prepare("UPDATE sellers SET omie_codigo = :c WHERE id = :id");
                    $u->execute([":c" => $codigo, ":id" => (int) $sellerId]);
                    $matched++;
                } else {
                    $unmatched[] = "{$nome} (#{$codigo})";
                }
            }
            $totalPaginas = (int) ($resp["total_de_paginas"] ?? 1);
            $pagina++;
        } while ($pagina <= $totalPaginas);

        $this->stats["vendedores_mapeados"] = $matched;
        $this->stats["vendedores_sem_match"] = count($unmatched);
        $this->stats["vendedores_sem_match_lista"] = $unmatched;
        if (!empty($unmatched) && defined("STDERR")) {
            fwrite(\STDERR, "Vendedores sem match (mapear manual): " . implode(", ", $unmatched) . "\n");
        }
        return true;
    }

    /**
     * Casa fotos dos produtos antigos (com foto, sem omie_codigo) nos produtos
     * vindos da Omie (com omie_codigo, sem foto), de forma CONSERVADORA:
     * so aplica quando o sufixo do codigo bate E os titulos compartilham uma
     * palavra significativa (>=4 letras). Isso evita foto errada na migracao.
     *
     * Copia photo/file_bt/file_fispq do produto antigo para o produto Omie.
     * Nao apaga nada; so preenche produtos Omie que estao sem foto.
     *
     * @return bool
     */
    public function matchPhotos(): bool
    {
        // produtos antigos com foto (fonte)
        $fonte = $this->pdo->query(
            "SELECT id, code, title, photo, file_bt, file_fispq
             FROM products
             WHERE omie_codigo IS NULL AND photo IS NOT NULL AND photo <> ''"
        )->fetchAll(\PDO::FETCH_ASSOC);

        // produtos Omie sem foto (destino)
        $destino = $this->pdo->query(
            "SELECT id, code, omie_codigo, title
             FROM products
             WHERE omie_codigo IS NOT NULL AND (photo IS NULL OR photo = '')"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $update = $this->pdo->prepare(
            "UPDATE products SET photo = :photo, file_bt = :bt, file_fispq = :fispq WHERE id = :id"
        );

        $aplicados = 0;
        $ambiguos = 0;
        foreach ($destino as $d) {
            $num = preg_replace('/\D/', '', (string) $d["code"]); // ex.: PRD02836 -> 02836
            if (strlen($num) < 4) {
                continue;
            }
            $hits = [];
            foreach ($fonte as $f) {
                $ec = preg_replace('/\D/', '', (string) $f["code"]);
                if ($ec !== "" && substr($ec, -strlen($num)) === $num
                    && $this->titlesAgree($d["title"], $f["title"])) {
                    $hits[] = $f;
                }
            }
            if (count($hits) === 1) {
                $f = $hits[0];
                $update->execute([
                    ":photo" => $f["photo"],
                    ":bt" => $f["file_bt"],
                    ":fispq" => $f["file_fispq"],
                    ":id" => $d["id"],
                ]);
                $aplicados++;
            } elseif (count($hits) > 1) {
                $ambiguos++;
            }
        }

        $this->stats["fotos_aplicadas"] = $aplicados;
        $this->stats["fotos_ambiguas"] = $ambiguos;
        $this->stats["fotos_pendentes"] = count($destino) - $aplicados;
        return true;
    }

    /**
     * Titulos "concordam" se compartilham ao menos uma palavra significativa
     * (>=4 caracteres, ignorando parenteses e acentuacao/pontuacao).
     */
    private function titlesAgree(string $a, string $b): bool
    {
        $words = function (string $t): array {
            $t = mb_strtoupper($t, "UTF-8");
            $t = preg_replace('/\(.+?\)/u', ' ', $t);       // remove "(...)"
            $t = preg_replace('/[^A-Z0-9ÁÉÍÓÚÂÊÔÃÕÇ ]/u', ' ', $t);
            $out = [];
            foreach (preg_split('/\s+/', trim($t)) as $w) {
                if (mb_strlen($w) >= 4) {
                    $out[$w] = true;
                }
            }
            return $out;
        };
        $wa = $words($a);
        $wb = $words($b);
        return !empty(array_intersect_key($wa, $wb));
    }

    /**
     * @return bool true = atualizou (ja existia), false = inseriu
     */
    private function upsertProduct(string $code, ?int $omieCodigo, string $title, ?string $value, ?string $photo, int $visible, ?string $stock): bool
    {
        $id = $this->findIdByCode("products", $code);
        if ($id) {
            // MIGRACAO: nunca apagar foto/valor existentes quando a Omie nao fornece.
            // photo/value so sao sobrescritos se vierem preenchidos da Omie.
            $sql = "UPDATE products SET
                        omie_codigo = :oc,
                        title = :t,
                        value = COALESCE(:v, value),
                        photo = COALESCE(:ph, photo),
                        visible = :vi,
                        stock = :st
                    WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ":oc" => $omieCodigo,
                ":t" => $title,
                ":v" => ($value === null || $value === "") ? null : $value,
                ":ph" => ($photo === null || $photo === "") ? null : $photo,
                ":vi" => $visible,
                ":st" => $stock,
                ":id" => $id,
            ]);
            return true;
        }
        // colunas NOT NULL sem default recebem valores neutros
        $sql = "INSERT INTO products (code, omie_codigo, title, description, value, photo, visible, stock, id_category, id_subcategory, id_provider)
                VALUES (:code, :oc, :t, :d, :v, :ph, :vi, :st, 0, 0, 0)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([":code" => $code, ":oc" => $omieCodigo, ":t" => $title, ":d" => $title, ":v" => $value, ":ph" => $photo, ":vi" => $visible, ":st" => $stock]);
        return false;
    }

    /**
     * @param array<string,string> $row
     * @return bool true = atualizou, false = inseriu
     */
    private function upsertClient(string $code, array $row): bool
    {
        $id = $this->findIdByCode("clients", $code);
        if ($id) {
            $sql = "UPDATE clients SET corporate_name=:corporate_name, address=:address, number=:number,
                    district=:district, city=:city, state=:state, phone=:phone, email=:email,
                    contact_name=:contact_name, cnpj=:cnpj, omie_codigo_vendedor=:omie_codigo_vendedor WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $params = $this->prefix($row);
            $params[":id"] = $id;
            $stmt->execute($params);
            return true;
        }
        $sql = "INSERT INTO clients (code, corporate_name, address, number, district, city, state, phone, email, contact_name, id_seller, cnpj, omie_codigo_vendedor)
                VALUES (:code, :corporate_name, :address, :number, :district, :city, :state, :phone, :email, :contact_name, 0, :cnpj, :omie_codigo_vendedor)";
        $stmt = $this->pdo->prepare($sql);
        $params = $this->prefix($row);
        $params[":code"] = $code;
        $stmt->execute($params);
        return false;
    }

    /**
     * Prefixa as chaves de $row com ":" para bind nomeado.
     * @param array<string,string> $row
     * @return array<string,string>
     */
    private function prefix(array $row): array
    {
        $out = [];
        foreach ($row as $k => $v) {
            $out[":{$k}"] = $v;
        }
        return $out;
    }

    /**
     * @return int|null id local da linha com aquele code, ou null
     */
    private function findIdByCode(string $table, string $code): ?int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM {$table} WHERE code = :code LIMIT 1");
        $stmt->execute([":code" => $code]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }
}
