<?php

namespace App\Repository\Categorie;

use Exception;
use App\Models\Categorie;

class CategoriesRepository implements CategoriesInterface
{
   
   private $model;

   public function __construct(Categorie $model){
      $this->model = $model;
   }

   public function insertCategoriesOrUpdate($categories){

      try{
         // Récupère les catégories déjà existante
         try{
            $categories_exists = $this->model::select('name', 'category_id_woocommerce')->get()->toArray();
         } catch(Exception $e){
            return $e->getMessage();
         }
        
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
                     try{
                        $update = $this->model->where('category_id_woocommerce', $diff['category_id_woocommerce'])->update($diff);
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

   public function getAllCategories(){
      $categories = $this->model::all()->toArray();
      $arborescence = $this->trierCategories($categories);
      return $arborescence;
   }


   function trierCategories($elements, $parent = 0) {
      $arborescence = array();
  
      foreach ($elements as $element) {
          if ($element['parent_category_id'] == $parent) {
              $sousCategories = $this->trierCategories($elements, $element['category_id_woocommerce']);
              if (!empty($sousCategories)) {
                  $element['sub_category'] = $sousCategories;
              }
              $arborescence[] = $element;
          }
      }
  
      return $arborescence;
  }


  function updateAllChildren($elements) {
   
   $list_ids = array();

   foreach ($elements as $element) {
       if (isset($element['sub_category'])) {
         $sousCategories = $this->updateAllChildren($element['sub_category']);
         if (!empty($sousCategories)) {
               foreach($sousCategories as $sous){
                  $list_ids [] = $sous;
               }
         }
       }
      $list_ids [] = $element['category_id_woocommerce'];
   }

   return $list_ids;
}

   public function updateCategoryOrder($id, $order_display, $parent){
      $categories = $this->model::all()->toArray();
    
      if($parent != "false"){
         $arborescence = $this->trierCategories($categories);
         $ids = array_column($arborescence, "category_id_woocommerce");
         $category = array_keys($ids,  $id);
         $lits_ids = $this->updateAllChildren($arborescence[$category[0]]['sub_category']);
         $lits_ids [] = $id;
         try{
            $this->model->whereIn('category_id_woocommerce', $lits_ids)->update(['order_display' => $order_display]);
            return true;
         } catch(Exception $e){
            return $e->getMessage();
         }
      } else {
         try{
            $this->model->where('category_id_woocommerce', $id)->update(['order_display' => $order_display]);
            return true;
         } catch(Exception $e){
            return $e->getMessage();
         }
      }
   }
}