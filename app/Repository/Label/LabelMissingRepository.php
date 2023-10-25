<?php

namespace App\Repository\Label;

use Exception;
use Carbon\Carbon;
use App\Models\LabelMissing;
use Illuminate\Support\Facades\DB;

class LabelMissingRepository implements LabelMissingInterface

{

   private $model;

   public function __construct(LabelMissing $model){
      $this->model = $model;
   }

   public function getAllLabelsMissingStatusValid(){
      return $this->model::where('status', 1)->get();
   }

   public function insert($status, $order_id){
      return $this->model::insert(
         [
            'status' => $status,
            'order_id' => $order_id
         ]
      );
   }

   public function delete($order_id){
      return $this->model::where('order_id', $order_id)->delete();
   }
}























