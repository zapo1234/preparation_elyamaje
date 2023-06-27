<?php

namespace App\Repository\Distributor;


interface DistributorInterface
{
    public function getDistributors();

    public function getDistributorById($id);

    public function insertDistributorsOrUpdate($data);
}




