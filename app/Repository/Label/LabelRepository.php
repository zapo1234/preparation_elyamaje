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
      return $this->model::insertGetId([
         'order_id' => $label['order_id'],
         'label' => $label['label'],
         'origin' => $label['origin'],
         'tracking_number' => $label['tracking_number'],
         'label_format' => $label['label_format'],
         'created_at' => date('Y-m-d H:i:s'),
         'tracking_status' => $label['tracking_status'] ?? 0,
         'cn23' => $label['cn23'] ?? null,
         'weight' => $label['weight'] ?? null
     ]);
   }

   public function getLabels(){
      return $this->model::select('labels.*', 'orders.status')
      ->join('orders', 'orders.order_woocommerce_id', '=', 'labels.order_id')
      ->orderBy('labels.created_at', 'DESC')->get();
   }

   public function getLabelById($label){
      return $this->model::select('label', 'label_format', 'cn23', 'download_cn23')->where('id', $label)->get();
   }

   public function getParcelNumbersyDate($date){
      return $this->model::select('tracking_number')->where('bordereau_id', null)->where('origin', '!=', 'chronopost')->where('created_at', 'LIKE', '%'.$date.'%')->get();
   }

   public function saveBordereau($bordereau_id, $parcelNumbers_array){
      return $this->model::whereIn('tracking_number', $parcelNumbers_array)->update(['bordereau_id' => $bordereau_id]);
   }

   public function deleteLabelByTrackingNumber($tracking_number){
      return $this->model::where('tracking_number', $tracking_number)->delete();
   }

   public function updateLabelBordereau($bordereau_id){
      return $this->model::where('bordereau_id', $bordereau_id)->update(['bordereau_id' => null]);
   }

   public function getAllLabels(){
      $date = date('Y-m-d');
      return $this->model::select('*')->where('created_at', 'LIKE', '%'.$date.'%')->get();
   }

   public function getAllLabelsByStatusAndDate($rangeDate){
      $current_date = date('Y-m-d H:i:s',strtotime("-".$rangeDate." days"));
      return $this->model 
      ->select('orders.status', 'labels.*')
      ->join('orders', 'orders.order_woocommerce_id', '=', 'labels.order_id')
      ->where('labels.created_at', '>', $current_date)->where('tracking_status', '!=', 5)->get();
   }

   public function getAllLabelsByStatusAndDateApi($rangeDate){
      $current_date = date('Y-m-d H:i:s',strtotime("-".$rangeDate." days"));
      return $this->model 
      ->select('labels.order_id', 'labels.tracking_number', 'labels.origin')
      ->join('orders', 'orders.order_woocommerce_id', '=', 'labels.order_id')
      ->where('labels.created_at', '>', $current_date)
      ->where('tracking_status', '!=', 5)
      ->orderBy('labels.updated_at', 'ASC')
      ->get();
   }

   public function updateLabelStatus($labels){

      $order_id = [];
      $updateQuery = "UPDATE prepa_labels SET updated_at = '".date('Y-m-d H:i:s')."', tracking_status = (CASE order_id";
      

      // Colissimo
      foreach ($labels['colissimo'] as  $value) {
         $order_id[] = $value['order_id'];  
         $updateQuery .= " WHEN '" . $value['order_id'] . "' THEN " . $value['step'];   
      }

      // Chronopost
      foreach ($labels['chronopost'] as  $value2) {
         $order_id[] = $value2['order_id'];
         $updateQuery .= " WHEN '" . $value2['order_id'] . "' THEN " . $value2['step'];                
      }

      $updateQuery.= " END) WHERE order_id IN (".implode(',',$order_id).")";
      $response = DB::update($updateQuery);
      
      return $response;
   }

   public function updateLabel($data, $label_id){
      return $this->model::where('id', $label_id)->update($data);
   }
}























