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
            ['type', 0]
        ])
        ->whereIn('id_reassort', [0, -1])
        ->get();
    }

    public function checkProductBarcode($product_id, $barcode){
        return $this->model::where('product_id', $product_id)->where('barcode', $barcode)->count();
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
}























