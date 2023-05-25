<?php

namespace App\Repository\Categorie;

use Exception;
use Carbon\Carbon;
use App\Models\Categorie;
use Illuminate\Support\Facades\DB;

class CategoriesRepository implements CategoriesInterface
{
     
   private $model;

   public function __construct(Categorie $model){
      $this->model = $model;
   }


   public function insertCategoriesOrUpdate($categories){

      try{
         // Récupère les catégories déjà existante
         $categories_exists = $this->model::select('name', 'category_id_woocommerce')->get()->toArray();
         
         // Aucune existante
         if(count($categories_exists) == 0){
            return $this->model->insert($categories);
         } else {
               $difference = [];
               foreach ($categories as $item1) {
                  $found = false;
                  foreach ($categories_exists as $item2) {
                     if ($item1['name'] === $item2['name'] && $item1['category_id_woocommerce'] === $item2['category_id_woocommerce']) {
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
                     $update = $this->model->where('category_id_woocommerce', $diff['category_id_woocommerce'])->update($diff);

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

   public function getAllCategories(){
      return $this->model::all();
   }

   public function updateCategoryOrder($id, $order_display){
      try{
         $this->model->where('id', $id)->update(['order_display' => $order_display]);
         return true;
      } catch(Exception $e){
         return $e->getMessage();
      }
   }

}