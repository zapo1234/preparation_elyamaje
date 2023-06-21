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
         'created_at' => date('Y-m-d H:i:s')
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

   public function getParcelNumbersyDate($date){
      return $this->model::select('tracking_number')->where('bordereau_id', null)->where('created_at', 'LIKE', '%'.$date.'%')->get();
   }

   public function saveBordereau($bordereau_id, $parcelNumbers_array){
      return $this->model::whereIn('tracking_number', $parcelNumbers_array)->update(['bordereau_id' => $bordereau_id]);
   }

   public function deleteLabelById($label_id){
      return $this->model::where('id', $label_id)->delete();
   }

   public function updateLabelBordereau($bordereau_id){
      return $this->model::where('bordereau_id', $bordereau_id)->update(['bordereau_id' => null]);
   }
}























