<?php

namespace App\Repository\Bordereau;

use Hash;
use Exception;
use App\Models\Bordereau;
use Illuminate\Support\Facades\DB;


class BordereauRepository implements BordereauInterface
{

   private $model;

   public function __construct(Bordereau $model){
      $this->model = $model;
   }

   public function getBordereaux(){
      return $this->model::select(DB::raw('COUNT(prepa_labels.bordereau_id) as number_order'), 'bordereau.parcel_number', 'bordereau.id as bordereauId',  
      'bordereau.bordereau', 'bordereau.created_at as bordereau_created_at', 'bordereau.label_date', 'bordereau.origin')
      ->join('labels', 'labels.bordereau_id', '=', 'bordereau.parcel_number')
      ->orderBy('bordereau.created_at', 'DESC')
      ->groupBy('labels.bordereau_id')
      ->get();
   }

   public function save($bordereau_id, $bordereau, $date, $origin){
      return $this->model::insert([
         'parcel_number' => $bordereau_id,
         'bordereau' => $bordereau,
         'label_date' => $date,
         'created_at' => date('Y-m-d H:i:s'),
         'origin' => $origin
      ]);
   }

   public function getBordereauById($id){
      return $this->model::select('*')->where('parcel_number', $id)->get();
   }

   public function deleteBordereauByParcelNumber($parcel_number){
      return $this->model::where('parcel_number', $parcel_number)->delete();
   }
}























