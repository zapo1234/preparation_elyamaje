<?php

namespace App\Repository\History;

use App\Models\History;
use Illuminate\Support\Facades\DB;

class HistoryRepository implements HistoryInterface

{

   private $model;


   public function __construct(History $model){
      $this->model = $model;
   }

   public function getHistoryByDate($date){
      return $this->model::select('users.id', 'users.name', 
         DB::raw('GROUP_CONCAT(CASE WHEN prepa_histories.poste != 0 THEN prepa_histories.poste ELSE NULL END) AS user_poste'),
         DB::raw('GROUP_CONCAT(CASE WHEN prepa_histories.status = "prepared" THEN prepa_histories.order_id ELSE NULL END) AS prepared_order'),
         DB::raw('GROUP_CONCAT(CASE WHEN prepa_histories.status = "finished" THEN prepa_histories.order_id ELSE NULL END) AS finished_order'),
         DB::raw('COUNT(CASE WHEN prepa_histories.status = "prepared" THEN 1 ELSE NULL END) AS prepared_count'),
         DB::raw('COUNT(CASE WHEN prepa_histories.status = "finished" THEN 1 ELSE NULL END) AS finished_count'))
         ->join('users', 'users.id', '=', 'histories.user_id')
         ->groupBy('users.id', 'users.name')
         ->where('histories.created_at', 'LIKE', '%'.$date.'%')
         ->get()
         ->toArray();
   }


   public function getAllHistory(){
      return $this->model::select('histories.id as histo', 'users.id', 'users.name', 'histories.poste', 'histories.created_at', 'histories.order_id',
         DB::raw('COUNT(CASE WHEN prepa_histories.status = "prepared" THEN 1 ELSE NULL END) AS prepared_count'),
         DB::raw('COUNT(CASE WHEN prepa_histories.status = "finished" THEN 1 ELSE NULL END) AS finished_count'))
         ->join('users', 'users.id', '=', 'histories.user_id')
         ->groupBy('histories.id')
         ->orderBy('histories.created_at', 'DESC')
         ->get()
         ->toArray();
   }

   public function save($data){
      return $this->model::insert($data);
   }
}























