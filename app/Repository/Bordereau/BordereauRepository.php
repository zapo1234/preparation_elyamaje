<?php

namespace App\Repository\Bordereau;

use Hash;
use Exception;
use App\Models\Bordereau;


class BordereauRepository implements BordereauInterface
{

   private $model;

   public function __construct(Bordereau $model){
      $this->model = $model;
   }

   public function getBordereaux(){
      return $this->model::select('bordereau.parcel_number', 'bordereau.bordereau', 'bordereau.created_at as bordereau_created_at', 'labels.*')
      ->join('labels', 'labels.bordereau_id', '=', 'bordereau.parcel_number')
      // ->where('bordereau', '!=', null)
      ->groupBy('labels.order_id')
      ->orderBy('bordereau.created_at', 'DESC')->get();
   }

   public function save($bordereau_id, $bordereau){
      return $this->model::insert([
         'parcel_number' => $bordereau_id,
         'bordereau' => $bordereau,
         'created_at' => date('Y-m-d H:i:s')
      ]);
   }

   public function getBordereauById($id){
      return $this->model::select('*')->where('parcel_number', $id)->get();
   }

   public function deleteBordereauByParcelNumber($parcel_number){
      return $this->model::where('parcel_number', $parcel_number)->delete();
   }
}






















