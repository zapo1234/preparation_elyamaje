<?php

namespace App\Repository\Don;

use Hash;
use Exception;
use App\Models\Don;


class DonRepository implements DonInterface
{

   private $model;

   public function __construct(Don $model){
      $this->model = $model;
   }

   public function getdatauser()
   {

   }

   public function getchiffredons()
   {
      
   }
}























