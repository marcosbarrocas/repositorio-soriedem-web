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

    public function getProduct()
    {
        $product = (new Product())->find('code = :code', "code=00{$this->id_product}")->fetch();

        if ($product) {
            return $product;
        } else {
            // Handle the case where no product was found
            // You can return null or throw an exception, depending on your requirements.
            return null;
        }
    }

    public function getPrice()
    {
        return (new ClientProducts())->find('id_client = :idc AND id_product = :idp', "idc={$this->id_client}&idp={$this->id_product}")->fetch();
    }
}
