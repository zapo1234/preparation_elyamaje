<?php

namespace App\Repository\Distributor;

use Exception;
use App\Models\Distributors;


class DistributorRepository implements DistributorInterface
{

   private $model;

   public function __construct(Distributors $model){
      $this->model = $model;
   }

   public function getDistributors(){
      return $this->model::all();
   }

   public function createDistributor($data){
      return $this->model::insert([
         'name' => $data['name'],
         'customer_id' => $data['customer_id'],
         'created_at' => date('Y-m-d H:i:s')
      ]);
   }

   public function updateDistributors($data){
      return $this->model::where('id',$data['id'])->update([
         'name' => $data['name'],
         'customer_id' => $data['customer_id'],
      ]);
   }

   public function deleteDistributor($distributor_id){
      return $this->model::where('id', $distributor_id)->delete();
   }
}























