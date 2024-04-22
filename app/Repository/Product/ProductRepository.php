<?php

namespace App\Repository\Product;

use Hash;
use Exception;
use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ProductRepository implements ProductInterface

{

   private $model;

   private $products =[];

   public function __construct(Product $model){

      $this->model = $model;
   }


   
    /**
    * @return array
    */
    public function getProducts(): array
    {
      return $this->products;
    }
   
   
    public function setProducts(array $products)
    {
       $this->products = $products;
       return $this;
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
      $list_product_api =[];

      foreach($lists as $values){
         $array_result[$values['product_woocommerce_id']] = $values['barcode'];
   
      }
      
      // recuperer la liste de produit
       //$this->setProducts($list_product_api);
      // renvoyer un tableau associatif avec key id_product et value barcode.
      return $array_result;
   }

   public function checkProductBarcode($product_id, $barcode){
      return $this->model::where('product_woocommerce_id', $product_id)->where('barcode', $barcode)->count();
   }

   function getProductsByBarcode($data){

      $list_barcode = array();

      foreach ($data as $key => $value) {
        if ($value["barcode"] != "no_barcode" && $value["barcode"]) {
          array_push($list_barcode,$value["barcode"]);
        }else {
          return ["response" => false, "message" => "Le produit ".$value["product_id"]." n'a pas de code barre sur dolibarr"];
        }
      }

      $res = $this->model::select('product_woocommerce_id', 'barcode')
      ->get()
      ->pluck(null, 'barcode')
      ->whereIn('barcode', $list_barcode)
      ->toArray();

      $ids_wc_vs_qte = array();

      foreach ($data as $key => $value) {
         if (isset($res[$value["barcode"]])) {

            array_push($ids_wc_vs_qte, [
               "id_product_wc" => $res[$value["barcode"]]["product_woocommerce_id"],
               "qty" => $value["qty"],
               "product_id" => $value["product_id"]
            ]);
         }else {
            return ["response" => false, "message" => "Le produit dont le code barre dolibarr = ".$value["barcode"]." n'existe pas dans la table prepa_procuct wc"];
         }
      }

      return ["response" => true, "ids_wc_vs_qte"=> $ids_wc_vs_qte];

    }

    function updateStockServiceWc($product_id_wc, $quantity){

      try {
         $customer_key = config('app.woocommerce_customer_key');
         $customer_secret = config('app.woocommerce_customer_secret');

         $getProductQuantity = Http::withBasicAuth($customer_key, $customer_secret)->get(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$product_id_wc);

         // dd($getProductQuantity->json());
         $newQuantity = $getProductQuantity->json()['stock_quantity'] - $quantity;

         // Si c'est une variation
         if($getProductQuantity->json()['parent_id'] != 0){
            $updateProductQuantity  = Http::withBasicAuth($customer_key, $customer_secret)
            ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$getProductQuantity->json()['parent_id']."/variations/".$product_id_wc, [
                  "stock_quantity" => $newQuantity
            ]);
            
         // Si c'est un produit sans variation
         } else {
            $updateProductQuantity  = Http::withBasicAuth($customer_key, $customer_secret)
            ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$product_id_wc, [
                  "stock_quantity" => $newQuantity
            ]);
         }
         return ["response" => true,"qte_actuelle" => $newQuantity];

      } catch (\Throwable $th) {
         return ["response" => false,"qte_actuelle" => "inchange", "message" => $th->getMessage()];
      }
    }

    function constructKit($product_id_wc){

      dd($product_id_wc);

      try {
         $customer_key = config('app.woocommerce_customer_key');
         $customer_secret = config('app.woocommerce_customer_secret');

         $getProductQuantity = Http::withBasicAuth($customer_key, $customer_secret)->get(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$product_id_wc);

         dd($getProductQuantity->json());


         $newQuantity = $getProductQuantity->json()['stock_quantity'];


         dd($newQuantity);


         dd("Finnnn");
         // Si c'est une variation
         if($getProductQuantity->json()['parent_id'] != 0){
            $updateProductQuantity  = Http::withBasicAuth($customer_key, $customer_secret)
            ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$getProductQuantity->json()['parent_id']."/variations/".$product_id_wc, [
                  "stock_quantity" => $newQuantity
            ]);
            
         // Si c'est un produit sans variation
         } else {
            $updateProductQuantity  = Http::withBasicAuth($customer_key, $customer_secret)
            ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$product_id_wc, [
                  "stock_quantity" => $newQuantity
            ]);
         }
         return ["response" => true,"qte_actuelle" => $newQuantity];

      } catch (\Throwable $th) {
         return ["response" => false,"qte_actuelle" => "inchange", "message" => $th->getMessage()];
      }
    }   


}























