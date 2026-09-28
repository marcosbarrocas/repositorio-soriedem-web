<?php

namespace Source\Models;

use Source\Core\Model;

/**
 * @package Source\Models
 */
class ClientProducts extends Model
{
    /**
     * ClientProducts constructor.
     */
    public function __construct()
    {
        parent::__construct('clients_products', ['id'], ['id_client', 'id_product']);
    }

    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return (new Product())->findById($this->id_product);
    }

    /**
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return (new Client())->findById($this->id_client);
    }

    /**
     * Conta os produtos já associados a um cliente na cesta.
     *
     * Cada registro de clients_products representa um produto que pode ser
     * comercializado para aquele cliente. O total é a quantidade desses
     * registros, não o cadastro geral de produtos.
     *
     * @param int $clientId Id do cliente na tabela clients.
     * @return int Quantidade de produtos associados. Retorna 0 quando o cliente não tem cesta.
     */
    public function countByClient(int $clientId): int
    {
        return (new ClientProducts())->find("id_client = :id", "id={$clientId}")->count();
    }
}