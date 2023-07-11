<?php

namespace App\Repository\ProductOrder;

use Hash;
use Exception;
use Carbon\Carbon;
use App\Models\ProductsOrder;
use Illuminate\Support\Facades\DB;

class ProductOrderRepository implements ProductOrderInterface

{

   private $model;

   public function __construct(ProductsOrder $model){
      $this->model = $model;
   }

   public function getAllProductsPicked(){
      return $this->model::select('product_woocommerce_id', 'order_id')->where('pick', 1)->get();
   }

   public function deleteProductOrderByLineItem($order_id, $line_item_id){
      return $this->model::where('line_item_id', $line_item_id)->where('order_id', $order_id)->delete();
   }

   public function insertProductOrder($product_order_woocommerce){
     
      $last_key_product = array_key_last($product_order_woocommerce['line_items']);

      $product_id = $product_order_woocommerce['line_items'][$last_key_product]['variation_id'] != 0 ?
      $product_order_woocommerce['line_items'][$last_key_product]['variation_id'] :
      $product_order_woocommerce['line_items'][$last_key_product]['product_id'];
      $category = $product_order_woocommerce['line_items'][$last_key_product]['category'][0]['name'];
      $category_id = $product_order_woocommerce['line_items'][$last_key_product]['category'][0]['term_id'];
      $quantity = $product_order_woocommerce['line_items'][$last_key_product]['quantity'];
      $cost = $product_order_woocommerce['line_items'][$last_key_product]['price'];
      $subtotal_tax = $product_order_woocommerce['line_items'][$last_key_product]['subtotal_tax'];
      $total_tax = $product_order_woocommerce['line_items'][$last_key_product]['total_tax'];
      $total_price = $product_order_woocommerce['line_items'][$last_key_product]['subtotal'];
      $line_item_id = $product_order_woocommerce['line_items'][$last_key_product]['id'];

      return $this->model::insert([
         'order_id' => $product_order_woocommerce['id'],
         'product_woocommerce_id' => $product_id,
         'category' => $category,
         'category_id' => $category_id,
         'quantity' => $quantity,
         'cost' => $cost,
         'subtotal_tax' => $subtotal_tax,
         'total_tax' => $total_tax,
         'total_price' => $total_price,
         'pick' => 0,
         'line_item_id' => $line_item_id,
         'pick_control' => 0,
         'created_at' => date('Y-m-d H:i:s')
      ]);
   }

   public function getProductsByOrderId($order_id){
      return $this->model::where('order_id', $order_id)->get();
   }


    public function getproductdolibar()
    {
        // recupérer des product depuis dolibar....pour recupérer id_product +dolibarr......


    }
}























