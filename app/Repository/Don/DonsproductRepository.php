<?php

namespace App\Repository\Don;

use Hash;
use Exception;
use Illuminate\Support\Facades\DB;

class DonsProductRepository implements DonsproductInterface
{

   private $model;

   public function __construct(DonsProduct $model){
      $this->model = $model;
   }


   public function inserts($order_id,$product_id,$label,$qty,$real_price)
   {
       
       
   }


   public function getproduct()
   {
      $data =  DB::table('dons_products')->select('product_id')->get();
      // transformer les retour objets en tableau
      $list = json_encode($data);
      $lists = json_decode($data,true);
      $list_email =[];
      
      foreach($lists as $key =>  $values){
          $list_code[$values['product_id']] = $key;
      }
        return $list_email;

   }

   
}
   
























