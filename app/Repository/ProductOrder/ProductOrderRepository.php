<?php

namespace App\Repository\ProductOrder;

use Hash;
use Exception;
use Carbon\Carbon;
use App\Models\ProductsOrder;
use Illuminate\Support\Facades\DB;

class ProductOrderRepository implements ProductOrderInterface

{

   private $model;

   public function __construct(ProductsOrder $model){
      $this->model = $model;
   }

   public function getAllProductsPicked(){
      return $this->model::select('product_woocommerce_id', 'order_id')->where('pick', 1)->get();
   }

}























