<?php

namespace App\Repository\Bordereau;


interface BordereauInterface
{   
    public function getBordereaux();

    public function save($bordereau_id, $bordereau, $date, $origin);

    public function getBordereauById($id);

    public function deleteBordereauByParcelNumber($parcel_number);
}




