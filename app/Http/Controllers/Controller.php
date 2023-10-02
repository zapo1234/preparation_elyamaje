<?php

namespace App\Http\Controllers;

use Throwable;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Http\Controllers\Order;
use Illuminate\Support\Facades\DB;
use App\Repository\Role\RoleRepository;
use App\Repository\User\UserRepository;
use App\Repository\Order\OrderRepository;
use App\Repository\Printer\PrinterRepository;
use App\Repository\Product\ProductRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Repository\Reassort\ReassortRepository;
use App\Repository\Categorie\CategoriesRepository;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Repository\Tiers\TiersRepository;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $orderController;
    private $orders;
    private $users;
    private $role;
    private $categories;
    private $products;
    private $printer;
    private $api;
    private $reassort;

    public function __construct(
        Order $orderController,
        UserRepository $users,
        RoleRepository $role,
        OrderRepository $orders,
        CategoriesRepository $categories,
        ProductRepository $products,
        PrinterRepository $printer,
        Api $api,
        ReassortRepository $reassort,
        TiersRepository $tiersRepository
    ) {
        $this->orderController = $orderController;
        $this->users = $users;
        $this->role = $role;
        $this->orders = $orders;
        $this->categories = $categories;
        $this->products = $products;
        $this->printer = $printer;
        $this->api = $api;
        $this->reassort = $reassort;
        $this->tiersRepository = $tiersRepository;
    }

    // INDEX ADMIN
    public function index()
    {
        $teams = $this->users->getUsersByRole([2, 3, 5]);
        $teams_have_order = $this->orders->getUsersWithOrder()->toArray();
        $products =  $this->products->getAllProductsPublished();
        $number_preparateur = 0;

        foreach ($teams as $team) {
            foreach ($team['role_id'] as $role) {
                if ($role == 2) {
                    $number_preparateur = $number_preparateur + 1;
                }
            }
        }

        $roles = $this->role->getRoles();
        return view('index', ['teams' => $teams, 'products' => $products, 'roles' => $roles, 'teams_have_order' => $teams_have_order, 'number_preparateur' => $number_preparateur]);
    }

    // INDEX CHEF D'ÉQUIPE
    public function dashboard()
    {
        $teams = $this->users->getUsersByRole([2, 3, 5]);
        $teams_have_order = $this->orders->getUsersWithOrder()->toArray();
        $products =  $this->products->getAllProductsPublished();
        $number_preparateur = 0;

        foreach ($teams as $team) {
            foreach ($team['role_id'] as $role) {
                if ($role == 2) {
                    $number_preparateur = $number_preparateur + 1;
                }
            }
        }

        $roles = $this->role->getRoles();
        return view('leader.dashboard', ['teams' => $teams, 'products' => $products, 'roles' => $roles, 'teams_have_order' => $teams_have_order, 'number_preparateur' => $number_preparateur]);
    }

    // CONFIGURATION ADMIN
    public function categories()
    {
        $categories = $this->categories->getAllCategories();
        return view('admin.categories', ['categories' => $categories]);
    }

    // CONFIGURATION LIST PRODUCTS ADMIN
    public function products()
    {
        // Get all products
        $products = $this->products->getAllProducts();
        // Get all categories products
        $categories = $this->categories->getAllCategories();
        return view('admin.products', ['products' => $products, 'categories' => $categories]);
    }

    // PRÉPARATEUR COMMANDES CLASSIQUES
    public function orderPreparateur()
    {
        $printer = $this->printer->getPrinterByUser(Auth()->user()->id);
       
        $reassort = $this->reassort->getReassortByUser(Auth()->user()->id);
        $count_rea = [];
        foreach($reassort as $rea){
            $count_rea[$rea->identifiant_reassort] = $rea;
        }

        $orders = $this->orderController->getOrder();
        $order_process = [];
        $orders_waiting_to_validate = [];
        $orders_validate = [];

        foreach ($orders['orders'] as $order) {
            if ($order['details']['status'] == "waiting_to_validate") {
                $orders_waiting_to_validate[] = $order;
            } else if ($order['details']['status'] == "waiting_validate") {
                $orders_validate[] = $order;
            } else {
                $order_process[] = $order;
            }
        }

        return view('preparateur.index_preparateur', [
            'user' => Auth()->user()->name,
            'orders_waiting_to_validate' => $orders_waiting_to_validate,
            'orders_validate' => $orders_validate,
            'orders' => isset($order_process[0]) ? $order_process[0] : [] /* Show only first order */,
            'number_orders' =>  count($order_process),
            'number_orders_waiting_to_validate' =>  count($orders_waiting_to_validate),
            'number_orders_validate' =>  count($orders_validate),
            'printer' => $printer[0] ?? false,
            'count_orders' => $orders['count'],
            'count_rea' => count($count_rea)
        ]);
    }

    // PRÉPARATEUR COMMANDES DISTRIBUTEURS
    public function ordersDistributeurs()
    {
        $printer = $this->printer->getPrinterByUser(Auth()->user()->id);
        $orders = $this->orderController->getOrderDistributeur();

        $reassort = $this->reassort->getReassortByUser(Auth()->user()->id);
        $count_rea = [];
        foreach($reassort as $rea){
            $count_rea[$rea->identifiant_reassort] = $rea;
        }

        $order_process = [];
        $orders_waiting_to_validate = [];
        $orders_validate = [];

        foreach ($orders['orders'] as $order) {
            if ($order['details']['status'] == "waiting_to_validate") {
                $orders_waiting_to_validate[] = $order;
            } else if ($order['details']['status'] == "waiting_validate") {
                $orders_validate[] = $order;
            } else {
                $order_process[] = $order;
            }
        }

        return view('preparateur.distributeur.index_preparateur', [
            'user' => Auth()->user()->name,
            'orders_waiting_to_validate' => $orders_waiting_to_validate,
            'orders_validate' => $orders_validate,
            'orders' => isset($order_process[0]) ? $order_process[0] : [] /* Show only first order */,
            'number_orders' =>  count($order_process),
            'number_orders_waiting_to_validate' =>  count($orders_waiting_to_validate),
            'number_orders_validate' =>  count($orders_validate),
            'printer' => $printer[0] ?? false,
            'count_orders' => $orders['count'],
            'count_rea' => count($count_rea)
        ]);
    }

    // PRÉPARATEUR COMMANDES TRANSFERTS DE STOCKS
    public function ordersTransfers(){
        $transfers = [];
        $transfers_progress = [];
        $reassort = $this->reassort->getReassortByUser(Auth()->user()->id);

        // Compte les autres commandes à faire
        $orders = $this->orderController->getOrder();

        foreach($reassort as $key => $rea){

            if($rea->id_reassort == 0){
                $transfers[$rea->identifiant_reassort]['id'] = $rea->identifiant_reassort;
                $transfers[$rea->identifiant_reassort]['date'] = $rea->datem;
                $transfers[$rea->identifiant_reassort]['products'][] = [
                    'product_id' => $rea->product_id,
                    'name' => $rea->name,
                    'qty' => $rea->qty,
                    'price' => $rea->price,
                    'barcode' => $rea->barcode,
                    'location' => $rea->location
                ];
            } else if($rea->id_reassort == -1){
                $transfers_progress[$rea->identifiant_reassort]['id'] = $rea->identifiant_reassort;
                $transfers_progress[$rea->identifiant_reassort]['date'] = $rea->datem;
                $transfers_progress[$rea->identifiant_reassort]['products'][] = [
                    'product_id' => $rea->product_id,
                    'name' => $rea->name,
                    'qty' => $rea->qty,
                    'price' => $rea->price,
                    'barcode' => $rea->barcode,
                    'location' => $rea->location
                ];
            }
        }

        // Récupère le premier réassort
        $total_transfers = count($transfers);
        $total_transfers_progress = count($transfers_progress);
        $transfers = $total_transfers > 0 ? $transfers[array_key_first($transfers)] : [];
        $transfers_progress = $total_transfers_progress > 0 ? $transfers_progress[array_key_first($transfers_progress)] : [];

        return view('preparateur.transfers.index_preparateur', 
        [
            'transfers' => $transfers, 
            'transfers_progress' => $transfers_progress,
            'total_transfers' => $total_transfers, 
            'total_transfers_progress' => $total_transfers_progress, 
            'user' => Auth()->user()->name,
            'count_orders' => $orders['count'],
            'count_rea' => $total_transfers + $total_transfers_progress
        ]);
    }

    // Mise à jour du status du transfers pour mettre en cours de traitement
    public function transfersProcesssing(Request $request){
        $transfer_id = $request->post('transfer_id');
        $status = $request->post('status');

        if($transfer_id && $status){
            return $this->reassort->updateStatusReassort($transfer_id, $status);
        }
        
    }

    // INDEX EMBALLEUR 
    public function wrapOrder()
    {
        return view('emballeur.index');
    }

    function getVieuxSplay (){

        
        $method = "GET";
        $apiKey = env('KEY_API_DOLIBAR'); 
            // $apiKey = 'VA05eq187SAKUm4h4I4x8sofCQ7jsHQd';

        $apiUrl = env('KEY_API_URL');
        // $apiUrl ="https://www.poserp.elyamaje.com/api/index.php/";


        $listWarehouses = $this->api->CallAPI("GET", $apiKey, $apiUrl."warehouses");
        $listWarehouses = json_decode($listWarehouses, true);

        $data_reassort = DB::table('hist_reassort')->groupBy('identifiant_reassort')->get()->toArray();
      
        
      
        $wh_id_name = array();

        

        foreach ($listWarehouses as $key => $value) {
           foreach ($value as $k => $val) {
            if ($k == "id") {
                $wh_id_name[$val] = $value["libelle"];
            }
           }
        }
    //    dump($wh_id_name);

        // dump($listWarehouses);
       //  dd($data_reassort);

        $liste_reassort = array();
        $id_etrepot_source = "";
        $id_etrepot_destination = "";
        $etat = "";
        $val_etat = 0;

    //    dump($data_reassort);

        foreach ($data_reassort as $key => $value) {

            $id_etrepot_source = (explode("to",$value->sense))[0];
            $id_etrepot_destination = (explode("to",$value->sense))[1];
                
            $val_etat = $value->id_reassort;

            if (!$value->origin_id_reassort) {          
                if ($val_etat == 0) {
                    $etat = '<span class="badge bg-warning text-dark">En attente</span>';
                }
                if ($val_etat < 0) {
                    $etat = '<span class="badge bg-info text-dark">En court</span>';
                }
                if ($val_etat > 0) {
                    $etat = '<span class="badge bg-success">Validé</span>';
                }
            }else {
                $etat = '<span class="badge bg-secondary">Annulé ('.$value->origin_id_reassort.')</span>';
            } 

            array_push($liste_reassort,[
                "identifiant" => $value->identifiant_reassort,
                "date" => date('d/m/Y H:i:s', $value->identifiant_reassort),
                "entrepot_source" => $wh_id_name[$id_etrepot_source],
                "entrepot_destination" => $wh_id_name[$id_etrepot_destination],
                "etat" => $etat,
                "val_etat" => $value->id_reassort,
                "origin_id_reassort" => $value->origin_id_reassort
            ]);
        }

        //   dd($listWarehouses);


        return view('admin.supply',
            [
                "listWarehouses" => $listWarehouses,
                "name_entrepot_a_alimenter" => "inconnue",
                "name_entrepot_a_destocker" => "inconnue",
                "liste_reassort" => $liste_reassort
            ]);

    }

    function createReassort(Request $request){

        $entrepot_source = $request->post('entrepot_source');
        $entrepot_destination = $request->post('entrepot_destination');

        if (!$entrepot_source || !$entrepot_destination) {
            dd("selectionnez les entrepots");
        }

     
        $method = "GET";
        $apiKey = env('KEY_API_DOLIBAR'); 
        // $apiKey = 'VA05eq187SAKUm4h4I4x8sofCQ7jsHQd';

       $apiUrl = env('KEY_API_URL');

        $listWarehouses = $this->api->CallAPI("GET", $apiKey, $apiUrl."warehouses");
        $listWarehouses = json_decode($listWarehouses, true);

    

        // 1- On récupère toute les facture de la semaine -7 jours pour vour les vente
        // puis on calcule la moyen hebdomadaire de vente pour chaque produit

        $mois = 1; // nombre de mois
        $jours = $mois*28;
        $interval = date("Y-m-d", strtotime("-$jours days")); 
        
        $coef = (1.10)/($jours/7);
        $limite = 100;

        // boutique elyamaje
        if ($entrepot_destination== "1") {
            $produitParam = array(
                'apikey' => $apiKey,
                'sqlfilters' => "t.datec >= '".$interval." 00:00:00' AND t.datec <= '".date("Y-m-d")." 23:59:59' AND t.ref LIKE '%TC1%'",
                'limit' => $limite,
                'page' => 0,
                'sortfield' => 'rowid',
                'sortorder' => 'DESC',
            );

        }

        // boutique nice
        if ($entrepot_destination== "11") {
            $produitParam = array(
                'apikey' => $apiKey,
                'sqlfilters' => "t.datec >= '".$interval." 00:00:00' AND t.datec <= '".date("Y-m-d")." 23:59:59' AND t.ref LIKE '%TC4%'",
                'limit' => $limite,
                'page' => 0,
                'sortfield' => 'rowid',
                'sortorder' => 'DESC',
            );
        }

        // boutique nice
        if ($entrepot_destination== "12") {
            $produitParam = array(
                'apikey' => $apiKey,
                'sqlfilters' => "t.datec >= '".$interval." 00:00:00' AND t.datec <= '".date("Y-m-d")." 23:59:59'",
                'limit' => $limite,
                'page' => 0,
                'sortfield' => 'rowid',
                'sortorder' => 'DESC',
            );
        }

        $array_factures_total = array();
        $page = $produitParam['page'];

        $condition = true;
        do {
            $page++;
            $produitParam['page'] = $page;

            $listinvoice = $this->api->CallAPI("GET", $apiKey, $apiUrl."invoices",$produitParam);     
            $factures = json_decode($listinvoice,true); 

            array_push($array_factures_total,$factures);
            // dd($array_factures_total);


            if (count($factures) < $limite) {
                $condition = false;
            }
        } while ($condition); 

        // dd($array_factures_total);

        $vente_by_product = array();

       
        foreach ($array_factures_total as $ktotal => $factures) {
            foreach ($factures as $key => $facture) {
                $lines = $facture["lines"];
                foreach ($lines as $kline => $line) {

                    if ($line["fk_product"] !="") {
                        if (!isset($vente_by_product[$line["fk_product"]])) {
                            $vente_by_product[$line["fk_product"]] = 
                            [
                                "qty" => $line["qty"]*$coef,
                                "desc" => $line["desc"],
                                "libelle" => $line["libelle"],
                                "total_ttc"=>$line["total_ttc"],
                                "subprice" => $line["subprice"]
                            ];
                        }else {
                            $vente_by_product[$line["fk_product"]]["qty"] = $vente_by_product[$line["fk_product"]]["qty"] + $line["qty"]*$coef;
                        }
                    }
                }
            }
        }

        // 2- on recupere les produit et leurs stock dans les différents entropot
        
        $products_dolibarrs = array();
        $page = 0;
        $condition = true;
        do {
            $page++;
            $all_products = $this->api->CallAPI("GET", $apiKey, $apiUrl."products?page=".$page);   
            $all_products = json_decode($all_products,true);  
            array_push($products_dolibarrs,$all_products);

            if (count($all_products) != 100) {
                $condition = false;
            }
        } while ($condition);

        // dd($products_dolibarrs);

        // 3- on récupère les entrepots existant 
        $warehouses = $this->api->CallAPI("GET", $apiKey, $apiUrl."warehouses");  
        $warehouses = json_decode($warehouses,true);  

        $warehouses_product_stock = array();

        foreach ($warehouses as $key_wh => $warehouse) {
            if (!isset($warehouses_product_stock[$warehouse["label"]])) {
                $warehouses_product_stock[$warehouse["label"]] = [
                    "id" => $warehouse["id"],
                    "label" => $warehouse["label"],
                    "statut" => $warehouse["statut"],
                    "list_product" => array()
                ];
            }
        }

        // on détermine l'entrepot a alimenter
        $name_entrepot_a_alimenter = "";
        $name_entrepot_a_destocker = "";

        foreach ($warehouses_product_stock as $key => $value) {

            if ($value["id"] == $entrepot_destination) {
                $name_entrepot_a_alimenter = $key;
            }
            if ($value["id"] == $entrepot_source) {
                $name_entrepot_a_destocker = $key;
            }
        }

        if ($name_entrepot_a_alimenter == "" || $name_entrepot_a_destocker == "") {
            dd("entrepot n'a pas pu etre determine");
        }

        // remplissage du tableau "list_product" pour chaque entrepot (tout les entrepot) NB : on peut se limiter au remplissage que du concerné

       // dd($products_dolibarrs[5][12]);

        foreach ($products_dolibarrs as $key_pr_do => $product_dolibarr) {
            foreach ($product_dolibarr as $k_p => $product) {

               // dd($product);

                if ($product["warehouse_array_list"]) {
                    if (count($product["warehouse_array_list"]) != 1) {
                        dump("produit en deux fois !");
                        dd($product);
                    }

                    foreach ($product["warehouse_array_list"] as $k_whp => $war_h_liste) {
                        foreach ($war_h_liste as $ww => $pr_st_wh) {                        
                            $warehouses_product_stock[$pr_st_wh["warehouse"]]["list_product"][$product["id"]] = 
                            [
                                "barcode" => $product["barcode"]?$product["barcode"]:"no_barcode",
                                "product_id" => $product["id"],
                                "price" => $product["price"]? $product["price"]:"0", 
                                "stock" => $pr_st_wh["stock" ]?$pr_st_wh["stock" ]:0,
                                "libelle" => $product["label"]
                            ];
                        }
                    }
                }
            }
        }       

       // dd($warehouses_product_stock);
        $products_reassort = array();
        $products_non_vendu_in_last_month = array();

        $tab_stock_vs_demande = array();

        // dump($name_entrepot_a_alimenter);       

        foreach ($warehouses_product_stock[$name_entrepot_a_alimenter]["list_product"] as $kproduct => $stock_in_war) {

            // source data
            $qte_en_stock_in_source = "";
            if (isset($warehouses_product_stock[$name_entrepot_a_destocker]["list_product"][$kproduct])) {
                $qte_en_stock_in_source = $warehouses_product_stock[$name_entrepot_a_destocker]["list_product"][$kproduct]["stock"];
            }else {
                $qte_en_stock_in_source = "0";
            }

            if (isset($vente_by_product[$kproduct])) {


                if ($stock_in_war["stock"] < $vente_by_product[$kproduct]["qty"]) {
                    array_push($products_reassort,[
                        "entrepot_a_alimenter" =>$name_entrepot_a_alimenter,
                        "name_entrepot_a_destocker" => $name_entrepot_a_destocker,
                        "qte_en_stock_in_source" => $qte_en_stock_in_source,
                        "libelle" => $stock_in_war["libelle"],
                        "product_id" => $kproduct,
                        "barcode" => $stock_in_war["barcode"],
                        "qte_act" => $stock_in_war["stock"]?$stock_in_war["stock"]:0,
                        "price" => $stock_in_war["price"]?$stock_in_war["price"]:"0",
                        "demande" => ceil($vente_by_product[$kproduct]["qty"]),
                        "qte_optimale" => ceil($vente_by_product[$kproduct]["qty"])*3
                    ]);
                }
            }else {
               
                array_push($products_non_vendu_in_last_month,[
                    "entrepot_a_alimenter" =>$name_entrepot_a_alimenter,
                    "name_entrepot_a_destocker" => $name_entrepot_a_destocker,
                    "qte_en_stock_in_source" => $qte_en_stock_in_source,
                    "libelle" => $stock_in_war["libelle"],
                    "product_id" => $kproduct,
                    "barcode" => $stock_in_war["barcode"],
                    "qte_act" => $stock_in_war["stock"]?$stock_in_war["stock"]:0,
                    "price" => $stock_in_war["price"]? $stock_in_war["price"]:"0",
                    "demande" => "inconnue",
                    "qte_optimale" => "inconnue"
                ]);
            }
        }

        // Dans les produit qui n'ont pas été vendu dans le moi on sort ceux dont la qté est inférieur a 5
        $products_non_vendu_in_last_month_inf_5 = array();

      //  dd($products_reassort);

        foreach ($products_non_vendu_in_last_month as $key => $value_) {
            if ($value_["qte_act"] < 5) {
                array_push($products_non_vendu_in_last_month_inf_5,$value_);
            }
        }

        foreach ($warehouses_product_stock[$name_entrepot_a_destocker]["list_product"] as $key => $value) {
            if (isset($warehouses_product_stock[$name_entrepot_a_alimenter]["list_product"][$key])) {
               $warehouses_product_stock[$name_entrepot_a_destocker]["list_product"][$key]["qte_in_destination"] = 
               $warehouses_product_stock[$name_entrepot_a_alimenter]["list_product"][$key]["stock"]? $warehouses_product_stock[$name_entrepot_a_alimenter]["list_product"][$key]["stock"]:0;
            }
        }

        // récupérer les user 
        $users = $this->users->getUsers()->toArray();

        return view('admin.supply',
            [
                "listWarehouses" => $listWarehouses,
                "products_reassort" => $products_reassort, 
                "products_non_vendu_in_last_month_inf_5" => $products_non_vendu_in_last_month_inf_5,
                "entrepot_source" => $entrepot_source,
                "entrepot_destination" => $entrepot_destination,
                "entrepot_source_product" => $warehouses_product_stock[$name_entrepot_a_destocker]["list_product"],
                "name_entrepot_a_alimenter" => $name_entrepot_a_alimenter,
                "name_entrepot_a_destocker" => $name_entrepot_a_destocker,
                "users" => $users
            ]);
    }




    function postReassort(Request $request){


        try {
          
            $data_save = array();
            $incrementation = 0;
            $decrementation = 0;
            $user_id = $request->post('user');
            $tabProduitReassort1 = $request->post('tabProduitReassort1');
            $entrepot_source = $request->post('entrepot_source');
            $entrepot_destination = $request->post('entrepot_destination');
            $name_date_reassort = "reassort_du_".date('Y-m-d H:i:s');
            $identifiant_reassort = time();
            $sense_transfert = $entrepot_source."to".$entrepot_destination;

            foreach ($tabProduitReassort1 as $key => $lineR) {

                if ($lineR["qte_transfere"] != 0) {           
                    $data1 = array(
                        'product_id' => $lineR["product_id"],
                        'warehouse_id' => $entrepot_source, 
                        'qty' => $lineR["qte_transfere"] * (-1), 
                        'type' => 1, 
                        'movementcode' => NULL, 
                        'movementlabel' => 'Transfere via preparation', 
                        'price' => $lineR["price"], 
                        'datem' => date('Y-m-d'), 
                        'dlc' => date('Y-m-d'), 
                        'dluo' => date('Y-m-d'), 
                    );

                        $data1["libelle_reassort"] = $name_date_reassort;
                        $data1["id_reassort"] = 0;
                        $data1["barcode"] = $lineR["barcode"];
                        $data1["identifiant_reassort"] = $identifiant_reassort;
                        $data1["sense"] = $sense_transfert;
                        $data1["user_id"] = $user_id;
                        array_push($data_save,$data1);
                        $decrementation++;
                    $data2 = array(
                        'product_id' => $lineR["product_id"],
                        'warehouse_id' => $entrepot_destination, 
                        'qty' => $lineR["qte_transfere"], 
                        'type' => 0, 
                        'movementcode' => NULL,
                        'movementlabel' => 'Transfere via preparation',
                        'price' => $lineR["price"],
                        'datem' => date('Y-m-d'),
                        'dlc' => date('Y-m-d'),
                        'dluo' => date('Y-m-d'),
                    );

                        $data2["libelle_reassort"] = $name_date_reassort;
                        $data2["id_reassort"] = 0;
                        $data2["barcode"] = $lineR["barcode"];
                        $data2["identifiant_reassort"] = $identifiant_reassort;
                        $data2["sense"] = $sense_transfert;
                        $data2["user_id"] = $user_id;
                        array_push($data_save,$data2);
                        $incrementation++;
                }
            }

            if ($incrementation != $decrementation) {
                return ["response" => false,"decrementation" => $decrementation,"incrementation" => $incrementation];
            }

            try {
                $resDB = DB::table('hist_reassort')->insert($data_save);
            } catch (\Throwable $th) {
                return ["response" => false,"decrementation" => $decrementation,"incrementation" => $incrementation, "error" => $th->getMessage()];
            }

            return ["response" => true,"decrementation" => $decrementation,"incrementation" => $incrementation,"resDB" => $resDB];

        } catch (\Throwable $th) {
            return ["response" => false,"decrementation" => $decrementation,"incrementation" => $incrementation, "error" => $th->getMessage()];
        } 
    }


    function executerTransfere($identifiant_reassort){


        try {
            $tabProduitReassort = $this->reassort->findByIdentifiantReassort($identifiant_reassort);

            if (!$tabProduitReassort) {
                return ["response" => false, "error" => "Transfère introuvable".$identifiant_reassort];
            }
            $apiKey = env('KEY_API_DOLIBAR');   
            $apiUrl = env('KEY_API_URL');
          
            $data_save = array();
            $incrementation = 0;
            $decrementation = 0;
            $i = 1;
            $ids="";
            $updateQuery = "UPDATE prepa_hist_reassort SET id_reassort = CASE";
            foreach ($tabProduitReassort as $key => $line) {

                

                if ($line["qty"] != 0) {           
                    $data = array(
                        'product_id' => $line["product_id"],
                        'warehouse_id' => $line["warehouse_id"], 
                        'qty' => $line["qty"]*-1, 
                        'type' => $line["type"], 
                        'movementcode' => $line["movementcode"], 
                        'movementlabel' => $line["movementlabel"], 
                        'price' => $line["price"], 
                        'datem' => date("Y-m-d", strtotime($line["datem"])), 
                        'dlc' => date("Y-m-d", strtotime($line["dlc"])),
                        'dluo' => date("Y-m-d", strtotime($line["dluo"])),
                    );
                    // on execute le réassort
                    $stockmovements = $this->api->CallAPI("POST", $apiKey, $apiUrl."stockmovements",json_encode($data));

                    if ($stockmovements) {
                        $updateQuery .= " WHEN id = ".$line['id']. " THEN ". $stockmovements;
                        if (count($tabProduitReassort) != $i) {
                            $ids .= $line['id'] . ",";
                        }else{
                            $ids .= $line['id'];
                        }
                        $i++;  
                        $incrementation++;
                    }
                }
            }
            $updateQuery .= " ELSE -1 END WHERE id IN (".$ids.")";

            $response = DB::update($updateQuery);

            return true;
        
        } catch (\Throwable $th) {
            dd($th);
            return ["response" => false, "error" => $th->getMessage()];
        } 

    }


    function delete_transfert($identifiant){

        $res = $this->reassort->deleteByIdentifiantReassort($identifiant);

       
        if ($res != -1) {
            return redirect()->back()->with('success', 'Transfère supprimé');
        }else {
            return redirect()->back()->with('error',  "Une érreur s'est produite");
        }
    }


    function cancel_transfert($identifiant){

      //  $cles = ['product_id','warehouse_id','qty','type','movementcode','movementlabel','price','datem','dlc','dluo','sense'];
        $transfert = $this->reassort->findByIdentifiantReassort($identifiant);

        if ($transfert != -1) {

            if (count($transfert) && count($transfert)%2 == 0) {

                $transfertCopie = $transfert;
                $date = date('Y-m-d');
                $identifiant_reassort = time();

                foreach ($transfert as $key => $line) {
                    unset($transfert[$key]['id']);
                    $sense = $line["sense"];
                    $wh_id_source = explode("to",$sense)[0];
                    $wh_id_destination = explode("to",$sense)[1];

                    $new_sense = $wh_id_destination."to".$wh_id_source;


                    $transfert[$key]["libelle_reassort"] = "Annulation réassort du ".date('Y-m-d H:i:s');
                    $transfert[$key]["id_reassort"] = 0;
                    $transfert[$key]["movementlabel"] = "Annulation transfert (".$line["id_reassort"].")";

                    $transfert[$key]["datem"] = $date;
                    $transfert[$key]["dlc"] = $date;
                    $transfert[$key]["dluo"] = $date;
                    $transfert[$key]["identifiant_reassort"] = $identifiant_reassort;
                    
                   
                    if ($line[ "warehouse_id"] == $wh_id_source) {
                        $transfert[$key]["warehouse_id"] = $wh_id_destination;
                    }

                    if ($line[ "warehouse_id"] == $wh_id_destination) {
                        $transfert[$key]["warehouse_id"] = $wh_id_source;
                    }

                    $transfert[$key]["sense"] = $new_sense;

                }

            $resDB = DB::table('hist_reassort')->insert($transfert);
            // mettre en annule le identifiant

            $colonnes_values = ['origin_id_reassort' => $identifiant];
            $res = $this->reassort->update_in_hist_reassort($identifiant, $colonnes_values);

            return redirect()->back()->with('success', 'Transfère annulé');
               
                
            }else {
                return redirect()->back()->with('error',  "Aucun transfère trouvé ou le nombrebre de ligne est inpaire");
            }

        }else {
            return redirect()->back()->with('error',  "Une érreur s'est produite");
        }

    }


    function preparationCommandeByToken(Request $request){


        try {

            
            // $apiUrl = env('KEY_API_URL');
            // $apiKey = env('KEY_API_DOLIBAR');
            $apiUrl = 'https://www.transfertx.elyamaje.com/api/index.php/';
            $apiKey = 'f2HAnva64Zf9MzY081Xw8y18rsVVMXaQ';

            
            
            $id = request('id');
            $token = request('tokenPrepa');

            if ($token == "btmhtn0zZyy8h4dvV3wOHCVTOwrHePKkosx85dG4WLrkk1I623U1yJiEeJLlFNuuylNDVVOhxkKVLMl05" && $id) {

                $order = $this->api->CallAPI("GET", $apiKey, $apiUrl."orders/".$id);
                $order = json_decode($order, true);

                    $tier = $this->api->CallAPI("GET", $apiKey, $apiUrl."thirdparties/".$order["socid"]);
                    $tier = json_decode($tier, true);

                    $name = $tier["name"];
                    $pname = $tier["name"];
                    $adresse = $tier["address"];
                    $city = $tier["town"];
                    $company = $tier["name_alias"];
                    $code_postal = $tier["zip"];
                    $contry = $tier["country_code"];
                    $email = $tier["email"];
                    $phone = $tier["phone"];

                if ($order["statut"] == 1) {

                    // verifier que tout les produits ont un code barre
                    $product_no_bc = "";
                    foreach ($order["lines"] as $key => $line) {
                        if (!$line["product_barcode"]) {
                            $product_no_bc = $line["libelle"];
                        }
                    }

                    if ($product_no_bc == "") {
                        $detail_facture = [

                            "ref_order" => $order["ref"],
                            "fk_commande" => $order["id"],
                            "socid" => $order["socid"],

                            "name" => $name,
                            "pname" => $pname,
                            "adresse" => $adresse,
                            "city" => $city,
                            "company" => $company,
                            "code_postal" => $code_postal,
                            "contry" => $contry,
                            "email" => $email,
                            "phone" => $phone,

                            "date" => date('Y-m-d H:i:s'),
                            "total_tax" => $order["total_tva"],
                            "total_order_ttc" => $order["total_ttc"],
                            "user_id" => 0,
                            "payment_methode" => $order["mode_reglement_code"],
                            "statut" => "processing"
                        ];

                        $id_f = DB::table('orders_doli')->insertGetId($detail_facture);
                        $lines = array();
                        foreach ($order["lines"] as $key => $line) {
                            array_push($lines,
                            $data_line = [
                                "id_commande" => $id_f,
                                "libelle" => $line["libelle"],
                                "id_product" => $line["fk_product"],
                                "barcode" => $line["product_barcode"],
                                "price" => $line["subprice"],
                                "qte" => $line["qty"],
                                "remise_percent" => $line["remise_percent"],
                                "total_ht" => $line["total_ht"],
                                "total_tva" => $line["total_tva"],
                                "total_ttc" => $line["total_ttc"],
                                "created_at" => date('Y-m-d H:i:s'),
                                "updated_at" => date('Y-m-d H:i:s')
                
                            ]);
                        }

                        $res = DB::table('lines_commande_doli')->insert($lines);
                        $order_put = $this->api->CallAPI("PUT", $apiKey, $apiUrl."orders/".$id,json_encode(["statut"=> "2"]));

                        // 3760324816721  4669

                        // "linkedObjectsIds" => [ "commande" => ["76": "7"]]



                        return redirect('https://www.transfertx.elyamaje.com/commande/list.php?leftmenu=orders&&action=successOrderToPreparation');

                    }else {

                        return redirect('https://www.transfertx.elyamaje.com/commande/card.php?id='.$id.'&&leftmenu=orders&&action=errorCodebare');

                       // return "le produit (". $product_no_bc.") n'a pas de code barre";
                    }
                }else {

                    // $message = "Le devis n'a pas été validé";
                    return redirect('https://www.transfertx.elyamaje.com/commande/card.php?id='.$id.'&&leftmenu=orders&&action=devisIvalide');
                }
            }else {
                // $message = "pas le droit";
                return redirect('https://www.transfertx.elyamaje.com/commande/card.php?id='.$id.'&&leftmenu=orders&&action=errorDroit');

            }
        } catch (Throwable $th) {
            return $th->getMessage();
        }
    }

    function changeStatutOfOrder($id_order,$id_statut){

        try {
        
            // $apiUrl = env('KEY_API_URL');
            // $apiKey = env('KEY_API_DOLIBAR');
            $apiUrl = 'https://www.transfertx.elyamaje.com/api/index.php/';
            $apiKey = 'f2HAnva64Zf9MzY081Xw8y18rsVVMXaQ';

            $order_put = $this->api->CallAPI("PUT", $apiKey, $apiUrl."orders/".$id,json_encode(["statut"=> $id_statut]));
            return true;

        } catch (\Throwable $th) {

            return false;
        }
    }
}
    