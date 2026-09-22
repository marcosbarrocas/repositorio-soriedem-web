<?php

namespace Source\Models;

use Source\Core\Model;

class Visit extends Model
{
    public function __construct()
    {
        parent::__construct('visits', ['id'], ['id_seller', 'id_client', 'client', 'details', 'signature']);
    }

    public function getImages()
    {
        return (new VisitImage())->find('id_visit = :idv', "idv={$this->id}")->fetch(true);
    }
}
