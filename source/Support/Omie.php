<?php

namespace Source\Support;

/**
 * Cliente HTTP da API Omie (JSON).
 *
 * Monta o envelope padrao da Omie:
 *   { "call": <metodo>, "app_key": ..., "app_secret": ..., "param": [ {...} ] }
 * e faz POST para o endpoint do recurso (clientes, produtos, pedido, vendedores).
 *
 * As credenciais vem das constantes CONF_OMIE_* (Config.php, lidas de env).
 *
 * @package Source\Support
 */
class Omie
{
    /** @var string base da API, ex.: https://app.omie.com.br/api/v1 */
    private string $base;

    /** @var string */
    private string $appKey;

    /** @var string */
    private string $appSecret;

    /** @var string|null ultima mensagem de erro (faultstring da Omie ou rede) */
    private ?string $error = null;

    public function __construct()
    {
        $this->base = rtrim(CONF_OMIE_BASE, "/");
        $this->appKey = CONF_OMIE_APP_KEY;
        $this->appSecret = CONF_OMIE_APP_SECRET;
    }

    /**
     * @return string|null
     */
    public function error(): ?string
    {
        return $this->error;
    }

    /**
     * Chamada generica a um recurso da Omie.
     *
     * @param string $resource caminho do recurso, ex.: "geral/clientes"
     * @param string $call     metodo, ex.: "ListarClientes"
     * @param array  $param    parametros do metodo (um objeto)
     * @return array|null       resposta decodificada, ou null em erro (ver error())
     */
    public function call(string $resource, string $call, array $param = []): ?array
    {
        $this->error = null;

        if (empty($this->appKey) || empty($this->appSecret)) {
            $this->error = "Credenciais da Omie nao configuradas (OMIE_APP_KEY/OMIE_APP_SECRET).";
            return null;
        }

        $url = $this->base . "/" . trim($resource, "/") . "/";
        $payload = json_encode([
            "call" => $call,
            "app_key" => $this->appKey,
            "app_secret" => $this->appSecret,
            "param" => [$param],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $this->error = "Erro de conexao com a Omie: " . curl_error($ch);
            curl_close($ch);
            return null;
        }
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        if (!is_array($data)) {
            $this->error = "Resposta invalida da Omie (HTTP {$status}).";
            return null;
        }

        // A Omie retorna erros como { "faultstring": ..., "faultcode": ... }
        if (isset($data["faultstring"])) {
            $this->error = trim($data["faultstring"]);
            return null;
        }

        if ($status >= 400) {
            $this->error = "Omie retornou HTTP {$status}.";
            return null;
        }

        return $data;
    }

    /* ------------------------------------------------------------------ */
    /* Clientes                                                            */
    /* ------------------------------------------------------------------ */

    public function listarClientes(int $pagina = 1, int $porPagina = 50, array $extra = []): ?array
    {
        return $this->call("geral/clientes", "ListarClientes", array_merge([
            "pagina" => $pagina,
            "registros_por_pagina" => $porPagina,
            "apenas_importado_api" => "N",
        ], $extra));
    }

    public function consultarCliente(int $codigoClienteOmie): ?array
    {
        return $this->call("geral/clientes", "ConsultarCliente", [
            "codigo_cliente_omie" => $codigoClienteOmie,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Produtos                                                            */
    /* ------------------------------------------------------------------ */

    public function listarProdutos(int $pagina = 1, int $porPagina = 50, array $extra = []): ?array
    {
        return $this->call("geral/produtos", "ListarProdutos", array_merge([
            "pagina" => $pagina,
            "registros_por_pagina" => $porPagina,
            "apenas_importado_api" => "N",
            "filtrar_apenas_omiepdv" => "N",
        ], $extra));
    }

    public function consultarProduto(int $codigoProduto): ?array
    {
        return $this->call("geral/produtos", "ConsultarProduto", [
            "codigo_produto" => $codigoProduto,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Vendedores                                                          */
    /* ------------------------------------------------------------------ */

    public function listarVendedores(int $pagina = 1, int $porPagina = 100, array $extra = []): ?array
    {
        return $this->call("geral/vendedores", "ListarVendedores", array_merge([
            "pagina" => $pagina,
            "registros_por_pagina" => $porPagina,
        ], $extra));
    }

    /* ------------------------------------------------------------------ */
    /* Pedidos                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Inclui um pedido de venda de produto.
     *
     * @param array $cabecalho ex.: ["codigo_cliente" => 123, "etapa" => "10", "codigo_parcela" => "999"]
     * @param array $itens     lista de ["codigo_produto" => .., "quantidade" => .., "valor_unitario" => ..]
     * @param int|null $codVendedor codigo do vendedor Omie (informacoes_adicionais.codVend)
     * @return array|null
     */
    public function incluirPedido(array $cabecalho, array $itens, ?int $codVendedor = null, array $informacoesAdicionais = []): ?array
    {
        $det = [];
        $indice = 0;
        foreach ($itens as $item) {
            $indice++;
            $det[] = [
                "ide" => [
                    "codigo_item_integracao" => (string) ($item["codigo_item_integracao"] ?? "IT{$indice}"),
                ],
                "produto" => [
                    "codigo_produto" => (int) $item["codigo_produto"],
                    "quantidade" => (float) $item["quantidade"],
                    "valor_unitario" => (float) $item["valor_unitario"],
                ],
            ];
        }

        $cabecalho["quantidade_itens"] = count($det);
        $param = [
            "cabecalho" => $cabecalho,
            "det" => $det,
        ];
        if ($codVendedor !== null) {
            $informacoesAdicionais["codVend"] = $codVendedor;
        }
        if (!empty($informacoesAdicionais)) {
            $param["informacoes_adicionais"] = $informacoesAdicionais;
        }

        return $this->call("produtos/pedido", "IncluirPedido", $param);
    }

    public function excluirPedido(int $codigoPedido): ?array
    {
        return $this->call("produtos/pedido", "ExcluirPedido", [
            "codigo_pedido" => $codigoPedido,
        ]);
    }

    public function listarPedidos(int $pagina = 1, int $porPagina = 50, array $extra = []): ?array
    {
        return $this->call("produtos/pedido", "ListarPedidos", array_merge([
            "pagina" => $pagina,
            "registros_por_pagina" => $porPagina,
            "apenas_importado_api" => "N",
        ], $extra));
    }
}
