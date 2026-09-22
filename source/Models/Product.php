<?php

namespace Source\Models;

use Source\Core\Model;

/**
 * @package Source\Models
 */
class Product extends Model
{
    /**
     * Product constructor.
     */
    public function __construct()
    {
        parent::__construct('products', ['id'], ['code', 'title', 'description', 'id_category', 'id_subcategory', 'id_provider']);
    }

    /**
     * @return SubCategory|null
     */
    public function getSubCategory(): ?SubCategory
    {
        return (new SubCategory())->findById($this->id_subcategory);
    }

    /**
     * @return Provider|null
     */
    public function getProvider(): ?Provider
    {
        return (new Provider())->findById($this->id_subcategory);
    }
}
