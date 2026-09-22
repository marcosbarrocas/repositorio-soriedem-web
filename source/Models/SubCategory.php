<?php

namespace Source\Models;

use Source\Core\Model;

/**
 * @package Source\Models
 */
class SubCategory extends Model
{
    /**
     * SubCategory constructor.
     */
    public function __construct()
    {
        parent::__construct('sub_categories', ['id'], ['title', 'id_category']);
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return (new Category())->findById($this->id_category);
    }
}