<?php

namespace Source\Models;

use Source\Core\Model;

/**
 * @package Source\Models
 */
class Category extends Model
{
    /**
     * Category constructor.
     */
    public function __construct()
    {
        parent::__construct('categories', ['id'], ['title']);
    }
}