<?php

namespace App\Repository\Don;

use Hash;
use Exception;
use App\Models\Donsproduct;
use Illuminate\Support\Facades\DB;

class DonsProductRepository implements DonsproductInterface
{

   private $model;

   public function __construct(Donsproduct $model){
      $this->model = $model;
   }


   public function inserts($data)
   {
       DB::table('dons_products')->insert($data);
       
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
   
























