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
}