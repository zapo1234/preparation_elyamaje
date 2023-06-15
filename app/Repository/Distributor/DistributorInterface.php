<?php

namespace App\Repository\Distributor;


interface DistributorInterface
{
    public function getDistributors();

    public function createDistributor($data);

    public function updateDistributors($data);

    public function deleteDistributor($distributor_id);
}




