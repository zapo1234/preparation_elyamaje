<?php

namespace App\Repository\Reassort;

use Exception;
use App\Models\Reassort;
use Illuminate\Support\Facades\DB;

class ReassortRepository implements ReassortInterface 
{   
    private $model;

    public function __construct(Reassort $model){
        $this->model = $model;
    }

    public function getReassortByUser($user_id){

        $list = [];
        $reassort = $this->model::select('products.name', 'products.price', 'products.location', 'hist_reassort.*')
        ->leftJoin('products', 'products.barcode', '=', 'hist_reassort.barcode')
        ->where([
            ['user_id', $user_id],
            ['type', 0]
        ])
        ->whereIn('id_reassort', [0, -1])
        ->whereIn('hist_reassort.status', ['processing', 'waiting_to_validate', 'waiting_validate'])
        ->get();


        $reassort = json_decode(json_encode($reassort), true);

        foreach($reassort as $rea){
            $list[$rea['identifiant_reassort']]['details'] = [
                'id' => $rea['identifiant_reassort'],
                'first_name' => "Transfert",
                'last_name' => null,
                'date' => $rea['datem'],
                'total' => 0,
                'total_tax' => 0,
                'status' => $rea['status'],
                'coupons' => '',
                'discount' => 0,
                'discount_amount' => 0,
                'gift_card_amount' => 0,
                'shipping_amount' => 0,
                'shipping_method' => '',
                'customer_note'   => ''
            ];
            $list[$rea['identifiant_reassort']]['items'][] = $rea;
        }

        return $list;
    }

    public function getReassortById($order_id){
        $transfer = $this->model::select('products.name', 'products.image', 'products.price', 'products.location', 'hist_reassort.*')
        ->leftJoin('products', 'products.barcode', '=', 'hist_reassort.barcode')
        ->where([
            ['identifiant_reassort', $order_id],
            ['type', 0]
        ])
        ->get();

        foreach($transfer as $key => $order){
            $transfer[$key]['order_woocommerce_id'] = $order['identifiant_reassort'];
            $transfer[$key]['transfers'] = true;
            $transfer[$key]['cost'] = $order['price'];
            $transfer[$key]['quantity'] = $order['qty'];

            $transfer[$key]['shipping_method_detail'] = "Transfert";
        }

     return $transfer;
    }


    public function checkProductBarcode($product_id, $barcode){
        return $this->model::where('product_id', $product_id)->where('barcode', $barcode)->count();
     }

    public function findByIdentifiantReassort($identifiant, $cles = null){
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

    public function updateStatusTextReassort($transfer_id, $status){
        return $this->model::where('identifiant_reassort', $transfer_id)->update(['status' => $status]);
    }

    public function checkIfDoneTransfersDolibarr($order_id, $barcode_array, $products_quantity, $partial){
        $transfer = $this->model::select('products.name', 'products.image', 'products.price', 'products.location', 'hist_reassort.*')
        ->leftJoin('products', 'products.barcode', '=', 'hist_reassort.barcode')
        ->where([
            ['identifiant_reassort', $order_id],
            ['type', 0],
            ['pick', 0],
        ])
        ->get();

        $transfer = json_decode(json_encode($transfer), true);

        $list_products = [];
        foreach($transfer as $list){
            $list_products[] = [
                "barcode" => $list['barcode'],
                "quantity" =>  $list['qty'],
                "id" =>  $list['id'],
            ];
        }

        $product_pick_in = [];
        $lits_id = [];

        // Construit le tableaux à update 
        $barcode_research = array_column($list_products, "barcode");
        
        foreach($barcode_array as $key => $barcode){
            $clesRecherchees = array_keys($barcode_research, $barcode);

            if(count($clesRecherchees) != 0){
                $lits_id[] = $list_products[$clesRecherchees[0]]['id'];

                $product_pick_in[] = [
                    'id' => $list_products[$clesRecherchees[0]]['id'],
                    'barcode' => $barcode,
                    'quantity' => intval($products_quantity[$key])
                ];
            }
        }

        // Récupère les différences entre les produits de la commande et ceux qui ont été bippés
        $barcode = array_column($product_pick_in, "barcode");
        $diff_quantity = false;
        $diff_barcode = false;

        foreach($list_products as $list){
            $clesRecherchees = array_keys($barcode, $list['barcode']);
            if(count($clesRecherchees) != 0){
                if($product_pick_in[$clesRecherchees[0]]['quantity'] != $list['quantity']){
                $diff_quantity = true;
                }
            } else {
                $diff_barcode = true;
            }
        }


        // Mise à jour de la valeur pick avec la quantité qui a été bippé pour chaque produit
        $cases = collect($product_pick_in)->map(function ($item) {
            return sprintf("WHEN %d THEN '%s'", $item['id'], intval($item['quantity']));
        })->implode(' ');


        $query = "UPDATE prepa_hist_reassort SET pick = (CASE id {$cases} END) WHERE id IN (".implode(',',$lits_id).")";
        DB::statement($query);


        if(!$partial){
            if(!$diff_quantity && !$diff_barcode){
                // Modifie le status de la commande en "Commande préparée",
                $this->updateStatusTextReassort($order_id ,"prepared-order");

                // Insert la commande dans histories
                DB::table('histories')->insert([
                'order_id' => $order_id,
                'user_id' => Auth()->user()->id,
                'status' => 'prepared',
                'created_at' => date('Y-m-d H:i:s')
                ]);
                return true;
            } else {
                return false;
            }
        } else {
            // Modifie le status de la commande en "en attente de validation"
            try{
                $this->updateStatusTextReassort($order_id, "waiting_to_validate");
                return true;
            } catch(Exception $e){
                return $e->getMessage();
            }
        }
    }
}























