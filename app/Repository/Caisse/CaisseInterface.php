<?php

namespace App\Repository\Caisse;


interface CaisseInterface
{
    public function getCaisse();

    public function insert($data);

    public function update($caisse_id, $data);

    public function delete($caisse_id);

    public function getAllDetailsUniqueId($date);
}




