<?php

namespace App\Repository\Reassort;

use Exception;
use App\Models\Reassort;
use App\Models\products_categories;
use App\Models\Categorie_dolibarr;
use App\Models\Products_association;
class ReassortRepository implements ReassortInterface 
{   
    private $model;

    public function __construct(Reassort $model,products_categories $products_categories,Categorie_dolibarr $categories_dolibarr,Products_association $products_association){
        $this->model = $model;
        $this->products_categories = $products_categories;
        $this->categories_dolibarr = $categories_dolibarr;
        $this->products_association = $products_association;
    }

    public function getReassortByUser($user_id){
        return $this->model::select('products.name', 'products.price', 'products.location', 'hist_reassort.*')
        ->leftJoin('products', 'products.barcode', '=', 'hist_reassort.barcode')
        ->where([
            ['user_id', $user_id],
            ['type', 0]
        ])
        ->whereIn('id_reassort', [0, -1])
        ->get();
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



     

    public function checkIfDone($order_id, $barcode_array, $products_quantity){

        $diff = false;
        $product_reassort = $this->model::select('barcode', 'qty')
        ->where([
            ['identifiant_reassort', $order_id],
            ['type', 0]
        ])->get();

        // Liste des produits à picker
        $product_reassort = json_decode(json_encode($product_reassort), true);

        // Liste des produits pickés
        $product_pick_in = [];
        if(count($barcode_array) == count($products_quantity)){
            foreach($barcode_array as $key => $barcode){
                $product_pick_in[$barcode] = intval($products_quantity[$key]);
            }
        }
        
        // Check les différences
        foreach($product_reassort as $product){
            if($product_pick_in[$product['barcode']] != $product['qty']){
                $diff = true;
            }
        }

        if($diff){
            return false;
        } else {
            return true;
        }
    }

    public function updateStatusReassort($transfer_id, $status){
        return $this->model::where('identifiant_reassort', $transfer_id)->update(['id_reassort' => $status]);
    }

    public function getAllCategoriesAndProducts(){

        return  $this->products_categories::select()
        ->get()
        ->pluck(null, 'fk_product')
        ->toArray();
        ;
    }

    public function getAllCategoriesLabel(){

       // dd($this->categories_dolibarr);

        return  $this->categories_dolibarr::select()
        ->get()
        ->pluck(null, 'id')
        ->toArray();
        ;
    }

    public function getKits(){

        $all_table = $this->products_association::select()
        ->get()
        ->toArray();
        ;



        $all_id_pere_kits = array();
        $composition_by_pere = array();

        foreach ($all_table as $key => $value) {


            $id_pere = $value["fk_product_pere"];
            $id_fils = $value["fk_product_fils"];
            $qty = $value["qty"];

            if (!in_array($id_pere, $all_id_pere_kits)) {
                array_push($all_id_pere_kits,$id_pere);
            }

            if (!isset($composition_by_pere[$id_pere])) {

                $composition_by_pere[$id_pere][] = [$id_fils,$qty];

            }else {
                array_push($composition_by_pere[$id_pere], [$id_fils,$qty]);
            }
        }


        return [
            "all_id_pere_kits" => $all_id_pere_kits,
            "composition_by_pere" => $composition_by_pere
        ];
     }

    
}























