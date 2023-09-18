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

   public function getProductById($product_id){
      return $this->model::select('*')->where('product_woocommerce_id', $product_id)->get();
   }

   public function getAllProducts(){
      return $this->model::select('*')->where('is_variable', 0)->orderBy('menu_order', 'ASC')->get();
   }

   public function getAllProductsPublished(){
      return $this->model::select('*')->where('status', 'publish')->where('stock','>', 0)->where('is_variable', 0)->get();
   }

   public function insertProductsOrUpdate($data){

      try{
         // Récupère les produits déjà existants
         try{
            $products_exists = $this->model::select('product_woocommerce_id', 'category', 'category_id', 'variation', 
            'name', 'price', 'barcode', 'status', 'manage_stock', 'stock', 'is_variable', 'weight', 'menu_order', 'image', 'ref')->get()->toArray();
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
            $product_to_insert = [];
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

            // Récupère les produits sur wordpress non trouvées en local pour les insérer
            foreach ($data as $item2) {
               $product_exist_online = array_keys($product_id_online,  $item2['product_woocommerce_id']);
               if(count($product_exist_online) == 0){
                  $product_to_insert[] = $item2;
               }
            }

            // INSERT NEW PRODUCTS 
            foreach ($product_to_insert as $new) {
               try{
                  $this->model->insert($new);
               } catch(Exception $e){
                  dd($e->getMessage());
               }
            }

            // UPDATE PRODUCTS
            if (!empty($difference_local)) {
               foreach ($difference_local as $diff_local) {
                  try{
                     $update = $this->model::where('product_woocommerce_id', $diff_local['product_woocommerce_id'])->update($diff_local);
                  } catch(Exception $e){
                     dd($e->getMessage());
                  }
         
                  if($update == 0){
                     $this->model->insert($diff_local);
                  }
               }
            } 

            // DELETE PRODUCTS
            if (!empty($difference_online)) {
               foreach ($difference_online as $diff_online) {
                  try{
                     $update = $this->model::where('product_woocommerce_id', $diff_online['product_woocommerce_id'])->delete();
                  } catch(Exception $e){
                     dd($e->getMessage());
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

   public function updateMultipleProduct($location, $products_id){
      return $this->model::whereIn('product_woocommerce_id', $products_id)->update(['location'=> $location]);
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

   public function checkProductBarcode($product_id, $barcode){
      return $this->model::where('product_woocommerce_id', $product_id)->where('barcode', $barcode)->count();
   }
}























