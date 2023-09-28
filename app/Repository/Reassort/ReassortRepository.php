<?php

namespace App\Repository\Reassort;

use App\Models\Reassort;

class ReassortRepository implements ReassortInterface 
{   
    private $model;

    public function __construct(Reassort $model){
        $this->model = $model;
    }

    public function getReassortByUser($user_id){
        return $this->model::select('products.name', 'products.price', 'products.location', 'hist_reassort.*')
        ->leftJoin('products', 'products.barcode', '=', 'hist_reassort.barcode')
        ->where([
            ['user_id', $user_id],
            ['id_reassort', 0],
            ['qty', '>', 0]
        ])->get();
    }

    public function checkProductBarcode($product_id, $barcode){
        return $this->model::where('product_id', $product_id)->where('barcode', $barcode)->count();
     }

     public function findByIdentifiantReassort($identifiant, $cles = null)
     {
        try {
            if ($cles) {
                $res = Reassort::where('identifiant_reassort', $identifiant)->get($cles)->toArray();
                return $res;
            }else {
                $res = Reassort::where('identifiant_reassort', $identifiant)->get()->toArray();
                return $res;
            }
        } catch (Exception $e) {
            return -1;
        }

     }

     public function deleteByIdentifiantReassort($identifiant)
     {
        try {
            $deletedRows = Reassort::where('identifiant_reassort', $identifiant)->delete();
            return $deletedRows;
        } catch (Exception $e) {
            return -1;
        }
     }

     public function update_in_hist_reassort($identifiant, $colonnes_values){

        try {
            Reassort::where('identifiant_reassort', $identifiant)
            ->update($colonnes_values);
            return true;

        } catch (\Throwable $th) {
            return -1;
        }

     }



     
}























