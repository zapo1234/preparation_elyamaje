<?php

namespace App\Repository\Product;

use Hash;
use Exception;
use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductInterface

{

   private $model;

   public function __construct(Product $model){

      $this->model = $model;
   }

}























