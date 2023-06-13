<?php

namespace App\Repository\Dons;

use Hash;
use Exception;
use App\Models\Dons;


class DonsRepository implements DonsInterface
{

   private $model;

   public function __construct(Dons $model){
      $this->model = $model;
   }
}























