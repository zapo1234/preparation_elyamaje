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
               $difference = [];
               foreach ($data as $item1) {
                  $found = false;

                  foreach ($distributors_exists as $item2) {
                     if ($item1['customer_id'] == $item2['customer_id']) {
                        if($item1 != $item2){
                           $found = false;
                           break;
                        } else {
                           $found = true;
                           break;
                        }
                     }
                  }
                  
                  if (!$found) {
                     $difference[] = $item1;
                  }
               }

               if (!empty($difference)) {

                  foreach ($difference as $diff) {
                    
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























