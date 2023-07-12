<?php

namespace App\Repository\LabelProductOrder;

use App\Models\LabelProductOrder;
use Illuminate\Support\Facades\DB;

class LabelProductOrderRepository implements LabelProductOrderInterface
{

   private $model;

   public function __construct(LabelProductOrder $model){
      $this->model = $model;
   }


   public function insert($order_id, $insert_label, $product_to_add_label, $quantity_product){
      $insert_product_order_label = [];
      foreach($product_to_add_label as $product){
         $insert_product_order_label[] = [
            'order_id' => $order_id,
            'label_id' => $insert_label,
            'product_id' => $product,
            'quantity' => $quantity_product[$product],
            'created_at' => date('Y-m-d H:i:s')
         ];
      }

      return $this->model->insert($insert_product_order_label);
   }

   public function deleteLabelProductOrderById($label_id){
      return $this->model::where('label_id', $label_id)->delete();
   }

   public function getLabelProductOrder($order_id){
      return $this->model::where('order_id', $order_id)->get();
   }
   
}























