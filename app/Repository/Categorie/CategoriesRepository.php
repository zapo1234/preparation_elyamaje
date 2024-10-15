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

   public function insertCategoriesOrUpdate($data){
      try{
         // Récupère les catégories déjà existante
         try{
            $categories_exists = $this->model::select('name', 'category_id_woocommerce', 'parent_category_id')->get()->toArray();
         } catch(Exception $e){
            return $e->getMessage();
         }
        
         // Aucune existante
         if(count($categories_exists) == 0){
            return $this->model->insert($data);
         } else {
            $difference_local = [];
            $difference_online = [];

            $category_id_on_local = array_column($data, "category_id_woocommerce");
            $category_id_online = array_column($categories_exists, "category_id_woocommerce");

            // Regarde si les données en local sont correctes
            foreach ($categories_exists as $item) {
               $category_exist = array_keys($category_id_on_local,  $item['category_id_woocommerce']);
               if(count($category_exist) == 0){
                  $difference_online[] = $item;
               } else {
                  if($data[$category_exist[0]] != $item){
                     $difference_local[] = $data[$category_exist[0]];
                  }
               }
            }

            // Récupère les données sur wordpress non trouvées en local et les insert
            foreach ($data as $item2) {
               $category_exist_online = array_keys($category_id_online,  $item2['category_id_woocommerce']);
               if(count($category_exist_online) == 0){
                  $difference_local[] = $item2;
               }
            }


            if (!empty($difference_local)) {
               foreach ($difference_local as $diff) {
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

            if (!empty($difference_online)) {
               foreach ($difference_online as $diff) {
                  try{
                     $update = $this->model::where('category_id_woocommerce', $diff['category_id_woocommerce'])->delete();
                  } catch(Exception $e){
                     return $e->getMessage();
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


   public function getAllCategoriesNotSorted(){
      $categories = $this->model::all()->toArray();
      return $categories;
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
         $lits_ids = isset($arborescence[$category[0]]['sub_category']) ? $this->updateAllChildren($arborescence[$category[0]]['sub_category']) : [];
         $lits_ids[] = $id;
         
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