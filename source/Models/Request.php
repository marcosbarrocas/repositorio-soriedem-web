<?php

namespace Source\Models;

use Source\Core\Model;

class Request extends Model
{
    public function __construct()
    {
        parent::__construct('requests', ['id'], ['request_number', 'id_seller', 'id_client', 'status', 'latitude', 'longitude']);
    }

    public function getClient()
    {
        return (new Client())->findById($this->id_client);
    }

    public function getSeller()
    {
        return (new Seller())->findById($this->id_seller);
    }

    public function getItemsRequest()
    {
        return (new Request())->find('request_number = :rn', "rn={$this->request_number}")->fetch(true);
    }

    /**
     * Retorna o produto vinculado a este item do pedido.
     *
     * O campo id_product da tabela requests guarda o id da tabela products.
     * O código comercial fica em products.code e deve ser lido no objeto retornado.
     *
     * @return Product|null Produto encontrado, ou null quando o id não existe em products.
     */
    public function getProduct(): ?Product
    {
        if (empty($this->id_product)) {
            return null;
        }

        return (new Product())->findById($this->id_product);
    }

    public function getPrice()
    {
        return (new ClientProducts())->find('id_client = :idc AND id_product = :idp', "idc={$this->id_client}&idp={$this->id_product}")->fetch();
    }
}
