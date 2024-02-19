<?php

namespace App\Repository\CashMovement;

use Exception;
use App\Models\CashMovement;
use Illuminate\Support\Facades\DB;

class CashMovementRepository implements CashMovementInterface
{
   private $model;

   public function __construct(CashMovement $model){
      $this->model = $model;
   }

   public function getMovements($date){
      return $this->model::select('cash_movements.*', 'users.name', 'caisse.name as deviceName')
      ->leftJoin('users', 'users.id', '=', 'cash_movements.user_id')
      ->join('caisse', 'caisse.uniqueId', '=', 'cash_movements.caisse')
      ->groupBy('cash_movements.id')
      ->where('cash_movements.created_at', 'LIKE', '%'.$date.'%')
      ->get();
   }

   public function addMovement($data){
      try{
         return $this->model::insert($data);
      } catch(Exception $e){   
         return $e->getMessage();
      }
   }

   public function updateMovement($movementId, $data){
      try{
         return $this->model::where('id', $movementId)->update($data);
      } catch(Exception $e){   
         return $e->getMessage();
      }
   }

   public function deleteMovement($movementId){
      try{
         return $this->model::where('id', $movementId)->delete();
      } catch(Exception $e){   
         return $e->getMessage();
      }
   }
}























