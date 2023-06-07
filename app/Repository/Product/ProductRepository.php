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

   public function getAllProducts(){
      return $this->model::all();
   }

   public function insertProductsOrUpdate($products){
      try{
         // Récupère les catégories déjà existante
         try{
            $products_exists = $this->model::select('name', 'product_woocommerce_id')->get()->toArray();
         } catch(Exception $e){
            return $e->getMessage();
         }
         // Aucune existante
         if(count($products_exists) == 0){
            return $this->model->insert($products);
         } else {
               $difference = [];
               foreach ($products as $item1) {
                  $found = false;
                  foreach ($products_exists as $item2) {
                     if ($item1['name'] === $item2['name'] && $item1['product_woocommerce_id'] === $item2['product_woocommerce_id']) {
                           $found = true;
                           break;
                     }
                  }
         
                  if (!$found) {
                     $difference[] = $item1;
                  }
               }

               if (!empty($difference)) {
                  foreach ($difference as $diff) {
                    
                     try{
                        $update = $this->model::where('product_woocommerce_id', $diff['product_woocommerce_id'])->update($diff);
                     } catch(Exception $e){
                        return $e->getMessage();
                     }
            
                     if($update == 0){
                        $this->model->insert($diff);
                     }
                  }
               } 

               return true;
         }
      } catch(Exception $e){
         return $e->getMessage();
      }

   }
}























