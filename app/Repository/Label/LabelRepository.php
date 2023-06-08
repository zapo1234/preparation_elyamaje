<?php

namespace App\Repository\Label;

use Exception;
use Carbon\Carbon;
use App\Models\Labels;
use Illuminate\Support\Facades\DB;

class LabelRepository implements LabelInterface

{

   private $model;

   public function __construct(Labels $model){
      $this->model = $model;
   }

   public function save($label){
      return $this->model::insert([
         'order_id' => $label['order_id'],
         'label' => $label['label'],
         'tracking_number' => $label['tracking_number'],
     ]);
   }

   public function getLabels(){
      return $this->model::select('labels.*', 'orders.status')
      ->join('orders', 'orders.order_woocommerce_id', '=', 'labels.order_id')
      ->orderBy('labels.created_at', 'DESC')->get();
   }

   public function getLabelById($label){
      return $this->model::select('label')->where('id', $label)->get();
   }
}























