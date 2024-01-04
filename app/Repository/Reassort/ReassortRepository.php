<?php

namespace App\Repository\Reassort;

use Exception;
use App\Models\Product;
use App\Models\Reassort;
use App\Models\Categorie_dolibarr;
use Illuminate\Support\Facades\DB;
use App\Models\products_categories;

use App\Models\Products_association;
use Illuminate\Support\Facades\Http;
use App\Http\Service\Api\Api;

class ReassortRepository implements ReassortInterface 
{   
    private $model;
    private $products_categories;
    private $categories_dolibarr;
    private $products_association;
    private $products;
    private $api;
    public function __construct(Reassort $model,products_categories $products_categories,
    Categorie_dolibarr $categories_dolibarr,Products_association $products_association,Product $products,Api $api
    )
    {
        $this->model = $model;
        $this->products_categories = $products_categories;
        $this->categories_dolibarr = $categories_dolibarr;
        $this->products_association = $products_association;
        $this->products = $products;
        $this->api = $api;
    }

    public function getReassortByUser($user_id){

        $list = [];
        $reassort = $this->model::select('products_dolibarr.label', 'products_dolibarr.price_ttc', 'products.location', 'hist_reassort.*')
        ->leftJoin('products_dolibarr', 'products_dolibarr.product_id', '=', 'hist_reassort.product_id')
        ->leftJoin('products', 'products.barcode', '=', 'hist_reassort.barcode')
        // ->where('products.status', 'publish')
        // ->where('products.is_variable', 0)
        ->whereIn('id_reassort', [0, -1])
        ->where([
            ['user_id', $user_id],
            ['type', 0]
        ])
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

        // Cas de produits double si par exemple 1 en cadeau et 1 normal
        $product_double = [];
        foreach($list as $key1 => $li){
            foreach($li['items'] as $key2 => $item){
                if(isset($product_double[$key1])){

                    $id_product = array_column($product_double[$key1], "id");
                    $clesRecherchees = array_keys($id_product,  $item['product_id']);

                    if(count($clesRecherchees) > 0){
                        // $detail_doublon = $product_double[$key1][$clesRecherchees[0]];
                        unset($list[$key1]['items'][$key2]);

                        // Merge quantity
                        // $list[$detail_doublon['key1']]['items'][$detail_doublon['key2']]['qty'] = $item['qty'] + $detail_doublon['qty'];
                    
                        // Merge pick product
                        // $list[$detail_doublon['key1']]['items'][$detail_doublon['key2']]['pick'] = $item['pick'] + $detail_doublon['pick'];
                    } else {
                        $product_double[$key1][] = [
                            'id' => $item['product_id'],
                            'qty' => $item['qty'], 
                            'key1' => $key1,
                            'key2' => $key2,
                            'pick' => $item['pick']
                        ];
                    }
                } else {
                    $product_double[$key1][] = [
                        'id' => $item['product_id'],
                        'qty' => $item['qty'], 
                        'key1' => $key1,
                        'key2' => $key2,
                        'pick' => $item['pick']
                    ];
                }
            }
        }

        return $list;
    }

    public function getReassortById($order_id){

        $transfer = $this->model::select('products_dolibarr.label as name', 'products_dolibarr.price_ttc', 'products.image', 'products.location', 'hist_reassort.*')
            ->leftJoin('products_dolibarr', 'products_dolibarr.product_id', '=', 'hist_reassort.product_id')
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


    public function getAllCategoriesAndProducts($cat_lab){

        $res =  $this->products_categories::select()
        ->get()
        ->toArray();
        ;

        $product_catIds = array();
        foreach ($res as $key => $value) {
            if (!isset($product_catIds[$value["fk_product"]])) {
                $value["fk_categorie"] = [
                    "cat" => [$value["fk_categorie"]],
                    "cat_parent" => [$cat_lab[$value["fk_categorie"]]["fk_parent"]],
                ];
                $product_catIds[$value["fk_product"]] = $value;
            }else {
                array_push($product_catIds[$value["fk_product"]]["fk_categorie"]["cat"], $value["fk_categorie"]);
                array_push($product_catIds[$value["fk_product"]]["fk_categorie"]["cat_parent"], $cat_lab[$value["fk_categorie"]]["fk_parent"]);
            }
        }


        foreach ($product_catIds as $key => $value) {

            $cat = $value["fk_categorie"]["cat"];
            $catParent = $value["fk_categorie"]["cat_parent"];
            $product_catIds[$key]["fk_categorie"] = $this->getLastCategorie($cat, $catParent);

        }


        return $product_catIds;
    }

    public function getAllCategoriesLabel(){

       // dd($this->categories_dolibarr);

        return  $this->categories_dolibarr::select()
        ->get()
        ->pluck(null, 'id')
        ->toArray();
       
    }

    public function getKits(){

        $all_table = $this->products_association::select()
        ->get()
        ->toArray();
        



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

     public function updateStatusTextReassort($transfer_id, $status){
        return $this->model::where('identifiant_reassort', $transfer_id)->update(['status' => $status]);
    }

    public function checkIfDoneTransfersDolibarr($order_id, $barcode_array, $products_quantity, $partial){
        $transfer = $this->model::select('products.name', 'products.image', 'products.price', 'products.location', 'hist_reassort.*')
        ->leftJoin('products', 'products.barcode', '=', 'hist_reassort.barcode')
        ->where([
            ['identifiant_reassort', $order_id],
            ['type', 0]
        ])
        ->get();

        $transfer = json_decode(json_encode($transfer), true);

        $total_product = 0;
        foreach($products_quantity as $product){
            $total_product = $total_product + intval($product);
        }

        // Cas de produits double si par exemple 1 en cadeau et 1 normal
        $product_double = [];
        foreach($transfer as $key_barcode => $list){

            if(isset($product_double[$list["barcode"]])){
                if(isset($product_double[$list["barcode"]][0])){

                // $quantity = $product_double[$list["barcode"]][0]['qty'];
                $key_barcode_to_remove = $product_double[$list["barcode"]][0]['key_barcode_to_remove'];
                unset($transfer[$key_barcode_to_remove]);
                // $transfer[$key_barcode]['qty'] = $transfer[$key_barcode]['qty'] + $quantity;
                }
            } else {
                $product_double[$list["barcode"]][] = [
                'qty' => $list['qty'],
                'key_barcode_to_remove' => $key_barcode
                ];
            }
        }

        $list_products = [];
        foreach($transfer as $list){
            if($list['qty'] != $list['pick']){
                $list_products[] = [
                    "barcode" => $list['barcode'],
                    "quantity" =>  $list['qty'],
                    "id" =>  $list['id'],
                ];
            }
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

    
        if(count($product_pick_in) > 0){
            // Mise à jour de la valeur pick avec la quantité qui a été bippé pour chaque produit
            $cases = collect($product_pick_in)->map(function ($item) {
                return sprintf("WHEN %d THEN '%s'", $item['id'], intval($item['quantity']));
            })->implode(' ');


            $query = "UPDATE prepa_hist_reassort SET pick = (CASE id {$cases} END) WHERE id IN (".implode(',',$lits_id).")";
            DB::statement($query);
        }

        if(!$partial){
            if(!$diff_quantity && !$diff_barcode){
                // Modifie le status de la commande en "Commande préparée",
                $this->updateStatusTextReassort($order_id ,"prepared-order");

                // Insert la commande dans histories
                DB::table('histories')->insert([
                    'order_id' => $order_id,
                    'user_id' => Auth()->user()->id,
                    'status' => 'prepared',
                    'created_at' => date('Y-m-d H:i:s'),
                    'total_product' => $total_product ?? null
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

    public function getLastCategorie($cat, $catParent){


        if (count($cat) == 1) {
            return $cat[0];
        }
        else {
            foreach ($cat as $key => $value) {
                if (!in_array($value,$catParent)) {
                    return $value;
                }
            }
            return "inconue";
        }
        
    }

    public function updateUserReassort($id_user,$identifiant_reassort){

        try {

            Reassort::where('identifiant_reassort', $identifiant_reassort)
            ->update(['user_id' => $id_user]);
            return true;

        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
    public function orderResetTransfers($order_id){
        try{
            $update_products = $this->model::where('identifiant_reassort', $order_id)->update(['pick' => 0]);
            return true;
         } catch(Exception $e){
            return false;
         }
    }

    public function getQteToTransfer($identifiant_reassort,$ids_ignore_tab){

        $transfer = $this->model::select('product_id', 'barcode', 'qty')
        ->where([
            ['identifiant_reassort', $identifiant_reassort],
            ['qty','>', 0],
            ['syncro', 0],
        ])
        ->whereNotIn('product_id', $ids_ignore_tab)
        ->get()
        ->toArray()
        ;
        return $transfer;
    }

    public function update_syncro_in_hist_reassort($identifiant_reassort, $datas_updated_succes){

        try {
            Reassort::whereIn('product_id', $datas_updated_succes)
            ->where('identifiant_reassort', $identifiant_reassort)
            ->update(["syncro" => 1]);
            return true;

        } catch (\Throwable $th) {
            return -1;
        }

     }

     public function getProductsByCategorie($id_categorie){

        $res =  $this->products_categories::select()
        ->where('fk_categorie',$id_categorie)
        ->get()
        ->toArray();
        ;

        return $res;
    }

    public function productsAssociationByIds($products_unite, $id_categorie){

      

        $data_no_products =[5502,5495,5544]; // les id dolibarr des produits a ignorer
        // on met les id des produits dans un seul tableau
        $data = array();
        foreach ($products_unite as $key => $value) {
            array_push($data,$value["fk_product"]);
        }

        $res = $this->products_association::select()
        ->whereIn('fk_product_fils',$data)
        ->get()
        ->toArray();

       

        
        foreach ($res as $key => $value) {
            if (!in_array($value["fk_product_pere"], $data_no_products)) {
                array_push($data,$value["fk_product_pere"]);
            }
            
        }
        $data = array_unique($data, SORT_REGULAR);


        $infos_id_wc = $this->products::select(
            'products.product_woocommerce_id as id_product_wc', 
            'products.name as name', 
            'products.barcode as barcode_wc', 
            'products_dolibarr.barcode as barcode_dolibarr',
            'products_dolibarr.product_id as product_id_doli',
            )
        ->leftJoin('products_dolibarr', 'products.barcode', '=', 'products_dolibarr.barcode')

        ->whereIn('products_dolibarr.product_id',$data)
        ->get()
        ->toArray()
        ;


      // création des réassort a la base du tableau $tab_repartition ce tableau a l'architecture suivante 
        // le produit fils c'est a dire le produit qui compose les quites est stocker dans l'index, chaque produit 
        // contient les produit qu'ils composent c'est a dire les kits 

        $tab_repartition = array();
        // mettre la valeur de 'product_id_doli' comme clée dans notre résultat
        $infos_id_wc = array_column($infos_id_wc, null, 'product_id_doli');
        $no_trouve = array();

        foreach ($res as $key => $value) {

            if (isset($infos_id_wc[$value["fk_product_pere"]])) {
                if (!isset($tab_repartition[$value["fk_product_fils"]])) {
                    $tab_repartition[$value["fk_product_fils"]][] = [
                        "fk_product_pere" => $value["fk_product_pere"],
                        "qty" => $value["qty"],
                        "id_wc" => $infos_id_wc[$value["fk_product_pere"]]["id_product_wc"],
                        "barcode" => $infos_id_wc[$value["fk_product_pere"]]["barcode_wc"],
                        "name" => $infos_id_wc[$value["fk_product_pere"]]["name"]
                    ];
                }else {
                    array_push($tab_repartition[$value["fk_product_fils"]], [
                        "fk_product_pere" => $value["fk_product_pere"],
                        "qty" => $value["qty"],
                        "id_wc" => $infos_id_wc[$value["fk_product_pere"]]["id_product_wc"],
                        "barcode" => $infos_id_wc[$value["fk_product_pere"]]["barcode_wc"],
                        "name" => $infos_id_wc[$value["fk_product_pere"]]["name"]
                    ]);
                }
            }else {
                array_push($no_trouve,$value["fk_product_pere"]);
            }

        }


        

        $tab_vernis = array();  
        $ids_unitaire = array();
        $all_ids_wc = array();

        
        if ($id_categorie == "1" || $id_categorie == "70") {

            // on construit un tableau associatif qui va contenir tout les vernis avec leux composant
            foreach ($tab_repartition as $fils => $val) {
                array_push($ids_unitaire,$fils);

                foreach ($val as $key => $value) {  

                    

                    if (!in_array($value["fk_product_pere"],$data_no_products)) {                    
                        if (!isset($tab_vernis[$value["fk_product_pere"]])) {
                            array_push($tab_vernis,);
                            $tab_vernis[$value["fk_product_pere"]] = [$fils];
                        }else {
                            array_push($tab_vernis[$value["fk_product_pere"]],$fils);
                        }
                    }
                }
            }

            // on ignorer les coffret qui possèdent plus de 6 verni 
            foreach ($tab_vernis as $id_coffret => $value) {
                if (count($value) > 6) {
                    unset($tab_vernis[$id_coffret]);
                }
            }

            // on récupère les quantité des produits dans dolibarr (on va se baser sur ça pour calculer les coffret)
            $entrepot_cible = "Entrepôt Malpassé";
            $all_product =  $this->getProductDolibarByApi($ids_unitaire,$entrepot_cible);
            // mettre la valeur de 'product_id_doli' comme clée dans notre résultat
            $all_product = array_column($all_product, null, 'id_product');


            // on constrits un tableau qui va contenir la quantité de chaque coffret récupérer depuis dolibarr 
            $datas = array();

            foreach ($tab_vernis as $id_coffret => $value) {
                $qty_coffret = $all_product[$value[0]]["stock"];
                foreach ($value as $key => $coffret) {
                    if ($all_product[$coffret]["stock"] < $qty_coffret) {
                        $qty_coffret = $all_product[$coffret]["stock"];
                    }
                }
                array_push($datas,[
                    "id_dolibarr" =>  $id_coffret,
                    "id_wc" =>  $infos_id_wc[$id_coffret]["id_product_wc"],
                    "qty" => $qty_coffret,
                    "name" =>  $infos_id_wc[$id_coffret]["name"]
                ]);  
                
                array_push($all_ids_wc,$infos_id_wc[$id_coffret]["id_product_wc"]);

            }  
            
            return ["datas"=>$datas, "all_ids_wc" => $all_ids_wc];
        }

        
        if ($id_categorie == "100") {
            $datas_limes = array();

            foreach ($tab_repartition as $id_dolibarr_unite => $kits) {

                $id_wc_unite = $infos_id_wc[$id_dolibarr_unite]["id_product_wc"];
                $qtyUniteInWc = $this->getQuantiteInWc($id_wc_unite);

                foreach ($kits as $key => $kit) {
                    array_push($datas_limes,[
                        "id_dolibarr" =>  $kit["fk_product_pere"],
                        "id_wc" =>  $infos_id_wc[$kit["fk_product_pere"]]["id_product_wc"],
                        "qty" => intval($qtyUniteInWc/$kit["qty"]),
                        "name" =>  $kit["name"]
                    ]);
                    array_push($all_ids_wc,$infos_id_wc[$kit["fk_product_pere"]]["id_product_wc"]);
                }

               
            }

            return ["datas"=>$datas_limes, "all_ids_wc" => $all_ids_wc];
        }
           



     }


    function updateStockServiceWcKits($data,$id_fils){

        try {

            $customer_key = config('app.woocommerce_customer_key');
            $customer_secret = config('app.woocommerce_customer_secret');

            $qtyFils = Http::withBasicAuth($customer_key, $customer_secret)->get(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$id_fils)->json()['stock_quantity'];

                
            foreach ($data as $key => $value) {

                $id_pere_wc = $value["id_wc"];
                $newQuantity = (intval($qtyFils/$value["qty"]))?? 0;
                $this->putQuantiteInWc($id_pere_wc, $newQuantity);

            }

           return ["response" => true];
  
        } catch (\Throwable $th) {
           return ["response" => false,"qte_actuelle" => "inchange", "message" => $th->getMessage()];
        }
    }

      

    function putQuantiteInWc($product_id_wc, $newQuantity){

        try {
           $customer_key = config('app.woocommerce_customer_key');
           $customer_secret = config('app.woocommerce_customer_secret');

           $getProductQuantity = Http::withBasicAuth($customer_key, $customer_secret)->get(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$product_id_wc);

           // Si c'est une variation
           if($getProductQuantity->json()['parent_id'] != 0){
              $updateProductQuantity  = Http::withBasicAuth($customer_key, $customer_secret)
              ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$getProductQuantity->json()['parent_id']."/variations/".$product_id_wc, [
                    "stock_quantity" => $newQuantity
              ]);
              
           // Si c'est un produit sans variation
           } else {
              $updateProductQuantity  = Http::withBasicAuth($customer_key, $customer_secret)
              ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$product_id_wc, [
                    "stock_quantity" => $newQuantity
              ]);
           }
           return ["response" => true];
  
        } catch (\Throwable $th) {
           return ["response" => false,"qte_actuelle" => "inchange", "message" => $th->getMessage()];
        }
    }


    function getProductDolibarByApi($ids_unitaire, $entrepot_cible){
       
        $limite = 10000;
        $method = "GET";
        $apiKey = env('KEY_API_DOLIBAR'); 
        $apiUrl = env('KEY_API_URL');

        $datas_product_final = array();
        
        $produitParamProduct = array(
            'apikey' => $apiKey,
            'limit' => $limite,
        );

        $all_products = $this->api->CallAPI("GET", $apiKey, $apiUrl."products",$produitParamProduct);  
        $all_products = json_decode($all_products,true); 

      foreach ($all_products as $key => $product) {
        if (in_array($product["id"],$ids_unitaire)) {

            $stock = 0;
            if ($product["warehouse_array_list"]) {
                foreach ($product["warehouse_array_list"][$product["id"]] as $key => $entrepot) {
                    if ($entrepot["warehouse"] == $entrepot_cible) {
                        $stock = $entrepot["stock"];
                    }
                }
            }
            
            array_push($datas_product_final,[
                "id_product" => $product["id"],
                "stock" => $stock
            ]);
        }
      }

        return $datas_product_final;

    }


    function getQuantiteInWc($product_id_wc){

        try {
           $customer_key = config('app.woocommerce_customer_key');
           $customer_secret = config('app.woocommerce_customer_secret');

           $getProductQuantity = Http::withBasicAuth($customer_key, $customer_secret)->get(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$product_id_wc)->json()['stock_quantity'];

           return $getProductQuantity;
  
        } catch (\Throwable $th) {
           return ["response" => false,"message" => $th->getMessage()];
        }
    }

    function updateColonneSyncro($datas_updated_succes, $identifiant_reassort){    

        $id_dolibarr_syncro = array();
  
        if ($datas_updated_succes) {
           foreach ($datas_updated_succes as $key => $value) {
              array_push($id_dolibarr_syncro,$value["product_id"]);
           }

           $this->model::where('identifiant_reassort', $identifiant_reassort)
           ->whereIn('product_id', $id_dolibarr_syncro)
           ->update(['syncro' => 1]);

           return count($datas_updated_succes);
        }

        return false;       
  
    }


}























