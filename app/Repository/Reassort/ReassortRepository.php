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
}























