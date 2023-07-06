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

   public function insertDistributorsOrUpdate($data){
      try{
         // Récupère les produits déjà existants
         try{
            $distributors_exists = $this->model::select('customer_id', 'first_name', 'last_name', 'role')->get()->toArray();
         } catch(Exception $e){
            return $e->getMessage();
         }

         // Aucun existants
         if(count($distributors_exists) == 0){
            try{
               return $this->model->insert($data);
            } catch(Exception $e){
               return $e->getMessage();
            }
           
         } else {
               $difference_local = [];
               $difference_online = [];

               $customer_id_on_local = array_column($data, "customer_id");
               $customer_id_online = array_column($distributors_exists, "customer_id");

               // Regarde si les données en local sont correctes
               foreach ($distributors_exists as $item) {
                  $customer_exist = array_keys($customer_id_on_local,  $item['customer_id']);
                  if(count($customer_exist) == 0){
                     $difference_online[] = $item;
                  } else {
                     if($data[$customer_exist[0]] != $item){
                        $difference_local[] = $data[$customer_exist[0]];
                     }
                  }
               }

              // Récupère les données sur wordpress non trouvées en local et les insert
               foreach ($data as $item2) {
                  $customer_exist_online = array_keys($customer_id_online,  $item2['customer_id']);
                  if(count($customer_exist_online) == 0){
                     $difference_local[] = $item2;
                  }
               }

               if (!empty($difference_local)) {
                  foreach ($difference_local as $diff) {
                     try{
                        $update = $this->model::where('customer_id', $diff['customer_id'])->update($diff);
                     } catch(Exception $e){
                        return $e->getMessage();
                     }
            
                     if($update == 0){
                        $this->model->insert($diff);
                     }
                  }
               } 

               if (!empty($difference_online)) {
                  foreach ($difference_online as $diff) {
                     try{
                        $update = $this->model::where('customer_id', $diff['customer_id'])->delete();
                     } catch(Exception $e){
                        return $e->getMessage();
                     }
                  }
               }

            return true;
         }
      } catch(Exception $e){
         return $e->getMessage();
      }
   }

   public function getDistributorById($id){
      return $this->model::where('customer_id', $id)->count();
   }
}























