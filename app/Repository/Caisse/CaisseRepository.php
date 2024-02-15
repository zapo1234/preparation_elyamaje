<?php

namespace App\Repository\Caisse;

use Exception;
use App\Models\Caisse;


class CaisseRepository implements CaisseInterface
{

   private $model;

   public function __construct(Caisse $model){
      $this->model = $model;
   }

   public function getCaisse() {
      return $this->model::all();
   }

   public function insert($data){
      return $this->model::insert($data);
   }

   public function update($caisse_id, $data){
      return $this->model::where('id', $caisse_id)->update($data);
   }

   public function delete($caisse_id){
      return $this->model::where('id', $caisse_id)->delete();
   }

   public function getAllDetailsUniqueId($date) {
      try{
         return $this->model::select('orders_doli.*', 'cashier.name as cashierName', 'cashier.id as cashierId', 'caisse.name as caisseName', 'caisse.uniqueId as caisseId')
         ->leftJoin('orders_doli', 'orders_doli.uniqueId', '=', 'caisse.uniqueId')
         ->leftJoin('users as cashier', 'cashier.id', '=', 'orders_doli.cashier')
         ->whereNotIn('orders_doli.statut', ['canceled', 'pending'])
         ->orWhereNull('orders_doli.statut') 
         // ->where('orders_doli.date', 'LIKE', '%'.$date.'%')
         // ->orWhereNull('orders_doli.date')
         ->get();
      } catch(Exception $e){
         return $e->getMessage();
      }
   }
}























