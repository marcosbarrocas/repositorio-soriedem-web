<?php

namespace Source\Models;

use Source\Core\Model;

class VisitImage extends Model
{
    public function __construct()
    {
        parent::__construct('visits_images', ['id'], ['id_visit', 'image']);
    }

    public function getVisit()
    {
        return (new Visit())->findById($this->id_visit);
    }
}
