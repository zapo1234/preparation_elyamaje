<?php

namespace App\Repository\Bordereau;


interface BordereauInterface
{   
    public function getBordereaux();

    public function save($bordereau_id, $bordereau);

    public function getBordereauById($id);
}




