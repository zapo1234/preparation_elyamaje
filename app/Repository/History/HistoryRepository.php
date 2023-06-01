<?php

namespace App\Repository\History;

use Hash;
use Exception;
use Carbon\Carbon;
use App\Models\History;
use App\Http\Service\Api\Api;
use Illuminate\Support\Facades\DB;

class HistoryRepository implements HistoryInterface

{

   private $model;


   public function __construct(History $model){
      $this->model = $model;
   }

   public function getHistory($date){
      return $this->model::select('users.id', 'users.name',
            DB::raw('COUNT(CASE WHEN histories.status = "prepared" THEN 1 ELSE NULL END) AS prepared_count'),
            DB::raw('COUNT(CASE WHEN histories.status = "finished" THEN 1 ELSE NULL END) AS finished_count'))
            ->join('users', 'users.id', '=', 'histories.user_id')
            ->groupBy('users.id', 'users.name')
            ->where('histories.created_at', 'LIKE', '%'.$date.'%')
            ->get()
            ->toArray();
   }


}























