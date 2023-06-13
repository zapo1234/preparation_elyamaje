<?php

namespace App\Repository\Don;

use Hash;
use Exception;
use App\Models\Dons;


class DonRepository implements DonInterface
{

   private $model;

   public function __construct(Dons $model){
      $this->model = $model;
   }
}























