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
      return $this->model::select('*')->where('is_variable', 0)->orderBy('menu_order', 'ASC')->get();
   }

   public function getAllProductsPublished(){
      return $this->model::select('*')->where('status', 'publish')->where('stock','>', 10)->where('is_variable', 0)->get();
   }

   public function insertProductsOrUpdate($data){
      try{
         // Récupère les produits déjà existants
         try{
            $products_exists = $this->model::select('product_woocommerce_id', 'category', 'category_id', 'variation', 
            'name', 'price', 'barcode', 'status', 'manage_stock', 'stock', 'is_variable', 'weight', 'menu_order')->get()->toArray();
         } catch(Exception $e){
            return $e->getMessage();
         }

         // Aucun existants
         if(count($products_exists) == 0){
           
            try{
               return $this->model->insert($data);
            } catch(Exception $e){
               return $e->getMessage();
            }
           
         } else {

            $difference_local = [];
            $difference_online = [];
            $product_id_on_local = array_column($data, "product_woocommerce_id");
            $product_id_online = array_column($products_exists, "product_woocommerce_id");

            // Regarde si les données en local sont correctes
            foreach ($products_exists as $item) {
               $product_exist = array_keys($product_id_on_local,  $item['product_woocommerce_id']);
               if(count($product_exist) == 0){
                  $difference_online[] = $item;
               } else {
                  if($data[$product_exist[0]] != $item){
                     $difference_local[] = $data[$product_exist[0]];
                  }
               }
            }

            // Récupère les données sur wordpress non trouvées en local et les insert
            foreach ($data as $item2) {
               $product_exist_online = array_keys($product_id_online,  $item2['product_woocommerce_id']);
               if(count($product_exist_online) == 0){
                  $difference_local[] = $item2;
               }
            }

            if (!empty($difference_local)) {
               foreach ($difference_local as $diff) {
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

            if (!empty($difference_online)) {
               foreach ($difference_online as $diff) {
                  try{
                     $update = $this->model::where('product_woocommerce_id', $diff['product_woocommerce_id'])->delete();
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

   public function updateProduct($id_product, $data){
      return $this->model::where('product_woocommerce_id', $id_product)->update($data);
   }

   public function getbarcodeproduct(){
      
      // recupérer 
      $data =  DB::table('products')->select('product_woocommerce_id','barcode')->get();
      // transformer les retour objets en tableau
      $list = json_encode($data);
      $lists = json_decode($data,true);
      $array_result = [];

      foreach($lists as $values){
         $array_result[$values['product_woocommerce_id']] = $values['barcode'];
      }
      
      // renvoyer un tableau associatif avec key id_product et value barcode.
      return $array_result;
   }
}























