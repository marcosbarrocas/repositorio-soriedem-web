<?php

namespace Source\Models;

use Source\Core\Model;

/**
 * @package Source\Models
 */
class Provider extends Model
{
    /**
     * Provider constructor.
     */
    public function __construct()
    {
        parent::__construct('providers', ['id'], ['title']);
    }
}