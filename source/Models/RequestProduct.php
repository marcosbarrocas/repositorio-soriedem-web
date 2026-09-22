<?php

namespace Source\Models;

use Source\Core\Model;

class RequestProduct extends Model
{
    public function __construct()
    {
        parent::__construct('requests_products', ['id'], ['id_request', 'id_product', 'amount']);
    }

    public function getProduct()
    {
        $product = (new Product())->find('code = :code', "code={$this->id_product}")->fetch();

        if ($product) {
            return $product;
        } else {


            return null;
        }
    }
}
