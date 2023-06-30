<?php

namespace App\Repository\Don;

use Hash;
use Exception;
use App\Models\Donsproduct;


class DonsProductRepository implements DonsproductInterface
{

   private $model;

   public function __construct(Donsproduct $model){
      $this->model = $model;
   }


   public function inserts($order_id,$product_id,$label,$qty,$real_price)
   {
        $product = new Don();
        $product->order_id = $order_id;
        $product->product_id = $product_id;
        $product->label = $label;
        $product->real_price = $real_price;
        $product->save();
       
   }


   public function getproduct()
   {
      $data =  DB::table('dons_product')->select('product_id')->get();
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
   
























