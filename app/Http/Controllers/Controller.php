<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Order;
use App\Repository\Role\RoleRepository;
use App\Repository\User\UserRepository;
use App\Repository\Order\OrderRepository;
use App\Repository\Printer\PrinterRepository;
use App\Repository\Product\ProductRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Repository\Categorie\CategoriesRepository;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Service\Api\Api;

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

    public function __construct(
        Order $orderController,
        UserRepository $users,
        RoleRepository $role,
        OrderRepository $orders,
        CategoriesRepository $categories,
        ProductRepository $products,
        PrinterRepository $printer,
        Api $api
    ) {
        $this->orderController = $orderController;
        $this->users = $users;
        $this->role = $role;
        $this->orders = $orders;
        $this->categories = $categories;
        $this->products = $products;
        $this->printer = $printer;
        $this->api=$api;
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
        $orders = $this->orderController->getOrder();
        $order_process = [];
        $orders_waiting_to_validate = [];
        $orders_validate = [];

        foreach ($orders as $order) {
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
            'printer' => $printer[0] ?? false
        ]);
    }

    // PRÉPARATEUR COMMANDES DISTRIBUTEURS
    public function ordersDistributeurs()
    {
        $printer = $this->printer->getPrinterByUser(Auth()->user()->id);
        $orders = $this->orderController->getOrderDistributeur();
        $order_process = [];
        $orders_waiting_to_validate = [];
        $orders_validate = [];

        foreach ($orders as $order) {
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
            'printer' => $printer[0] ?? false
        ]);
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
       // dd($wh_id_name);

        // dump($listWarehouses);
         //dd($data_reassort);

        $liste_reassort = array();
        $id_etrepot_source = "";
        $id_etrepot_destination = "";

        foreach ($data_reassort as $key => $value) {

            if ($id_etrepot_source == "" && $id_etrepot_destination == "") {
                $id_etrepot_source = (explode("to",$value->sense))[0];
                $id_etrepot_destination = (explode("to",$value->sense))[1];
            }
            

            array_push($liste_reassort,[
                "identifiant" => $value->identifiant_reassort,
                "date" => date('d/m/Y H:i:s', $value->identifiant_reassort),
                "entrepot_source" => $wh_id_name[$id_etrepot_source],
                "entrepot_destination" => $wh_id_name[$id_etrepot_destination],
                "etat" => ($value->id_reassort == 0)? '<span class="badge bg-warning text-dark">En attente</span>':'<span class="badge bg-success">Validé</span>',
            ]);
        }

       // dd($liste_reassort);


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

      //  dd($entrepot_destination);

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

        $interval = date("Y-m-d", strtotime("-14 days")); // 24 semaines
        $coef = 1.10/(0.25);
        // $coef = 1*1.10;

        $coef = 1;
        $limite = 100;

          $produitParam = array(
            'apikey' => $apiKey,
            'sqlfilters' => "t.datec >= '".$interval." 00:00:00' AND t.datec <= '".date("Y-m-d")." 23:59:59' AND t.ref LIKE '%TC4%'",

        //    'sqlfilters' => "t.ref LIKE '%TC1%'",

            'limit' => $limite,

            'page' => 0,
            'sortfield' => 'rowid',
            'sortorder' => 'DESC',
        );

        

        // $listinvoice = $this->api->CallAPI("GET", $apiKey, $apiUrl."invoices",$produitParam);     
        // $factures = json_decode($listinvoice,true); 

        // dd($factures);
        

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
        
        $listinvoice = $this->api->CallAPI("GET", $apiKey, $apiUrl."invoices",$produitParam);     
        $factures = json_decode($listinvoice,true);  

        $vente_by_product = array();

       
      
        foreach ($array_factures_total as $ktotal => $factures) {
            foreach ($factures as $key => $facture) {

                $lines = $facture["lines"];
                foreach ($lines as $kline => $line) {

                    // if ($line["fk_product"] =="4953") {
                    //     dd($facture);
                    // }

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
                                "stock" => $pr_st_wh["stock" ],
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
                $qte_en_stock_in_source = "Ø";
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
                        "qte_act" => $stock_in_war["stock"],
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
                    "qte_act" => $stock_in_war["stock"],
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
               $warehouses_product_stock[$name_entrepot_a_destocker]["list_product"][$key]["qte_in_destination"] = $warehouses_product_stock[$name_entrepot_a_alimenter]["list_product"][$key]["stock"];
            }
        }

        

        return view('admin.supply',
            [
                "listWarehouses" => $listWarehouses,
                "products_reassort" => $products_reassort, 
                "products_non_vendu_in_last_month_inf_5" => $products_non_vendu_in_last_month_inf_5,
                "entrepot_source" => $entrepot_source,
                "entrepot_destination" => $entrepot_destination,
                "entrepot_source_product" => $warehouses_product_stock[$name_entrepot_a_destocker]["list_product"],
                "name_entrepot_a_alimenter" => $name_entrepot_a_alimenter,
                "name_entrepot_a_destocker" => $name_entrepot_a_destocker
            ]);
    }




    function postReassort(Request $request){


        $data_save = array();
       

        $incrementation = 0;
        $decrementation = 0;
        $apiUrl = env('KEY_API_URL');
        $apiKey = env('KEY_API_DOLIBAR'); 

        $tabProduitReassort1 = $request->post('tabProduitReassort1');
        $entrepot_source = $request->post('entrepot_source');
        $entrepot_destination = $request->post('entrepot_destination');
        $name_date_reassort = "reassort_du_".date('Y-m-d H:i:s');
        $identifiant_reassort = time();
        $sense_transfert = $entrepot_source."to".$entrepot_destination;

        foreach ($tabProduitReassort1 as $key => $lineR) {


            $data1 = array(
                'product_id' => $lineR["product_id"],
                'warehouse_id' => $entrepot_source, 
                'qty' => $lineR["qte_transfere"] * (-1), // Quantité à ajouter (peut être négative pour une sortie)
                'type' => 1, // 0 pour entrée, 1 pour sortie (conformément à la documentation)
                'movementcode' => NULL, // Code de mouvement (facultatif)
                'movementlabel' => 'Transfere via preparation', // Étiquette de mouvement (facultatif)
                'price' => $lineR["price"], // Pour mettre à jour l'AWP lors d'une augmentation de stock
                'datem' => date('Y-m-d'), // Date du mouvement (facultatif, au format YYYY-MM-DD)
                'dlc' => date('Y-m-d'), // Date limite de consommation (facultatif, au format YYYY-MM-DD)
                'dluo' => date('Y-m-d'), // Date de péremption (facultatif, au format YYYY-MM-DD)  /stockmovements
            );

           // $stockmovements1 = $this->api->CallAPI("POST", $apiKey, $apiUrl."stockmovements",json_encode($data1));
            // if ($stockmovements1) {

                $data1["libelle_reassort"] = $name_date_reassort;
                $data1["id_reassort"] = 0;
                $data1["barcode"] = $lineR["barcode"];
                $data1["identifiant_reassort"] = $identifiant_reassort;
                $data1["sense"] = $sense_transfert;
                array_push($data_save,$data1);
                $decrementation++;
            // }
            $data2 = array(
                'product_id' => $lineR["product_id"],
                'warehouse_id' => $entrepot_destination, // J'ai changé la clé pour correspondre à la documentation
                'qty' => $lineR["qte_transfere"], // Quantité à ajouter (peut être négative pour une sortie)
                'type' => 0, // 0 pour entrée, 1 pour sortie (conformément à la documentation)
                'movementcode' => NULL, // Code de mouvement (facultatif)
                'movementlabel' => 'Transfere via preparation', // Étiquette de mouvement (facultatif)
                'price' => $lineR["price"], // Pour mettre à jour l'AWP lors d'une augmentation de stock
                'datem' => date('Y-m-d'), // Date du mouvement (facultatif, au format YYYY-MM-DD)
                'dlc' => date('Y-m-d'), // Date limite de consommation (facultatif, au format YYYY-MM-DD)
                'dluo' => date('Y-m-d'), // Date de péremption (facultatif, au format YYYY-MM-DD)
            );

            // $stockmovements2 = $this->api->CallAPI("POST", $apiKey, $apiUrl."stockmovements",json_encode($data2));
            // if ($stockmovements2) {

                $data2["libelle_reassort"] = $name_date_reassort;
                $data2["id_reassort"] = 0;
                $data2["barcode"] = $lineR["barcode"];
                $data2["identifiant_reassort"] = $identifiant_reassort;
                $data2["sense"] = $sense_transfert;
                array_push($data_save,$data2);
                $incrementation++;
            // }
        }

        if ($incrementation != $decrementation) {
            return ["response" => false,"decrementation" => $decrementation,"incrementation" => $incrementation];
        }
        // php artisan make:migration sense_to_hist_reassort

        try {
            $resDB = DB::table('hist_reassort')->insert($data_save);
        } catch (\Throwable $th) {
            return ["response" => false,"decrementation" => $decrementation,"incrementation" => $incrementation, "error" => $th->getMessage()];
        }
        


        return ["response" => true,"decrementation" => $decrementation,"incrementation" => $incrementation,"resDB" => $resDB];

    }


    function executerTransfere(){

    }

    function teste_insert(){

        $data = [
            [
                'product_id' => '4962',
                'warehouse_id' => '1',
                'qty' => -25,
                'type' => 1,
                'movementcode' => null,
                'movementlabel' => null,
                'price' => null,
                'datem' => null,
                'dlc' => null,
                'dluo' => null,
            ],
            [
                'product_id' => '4962',
                'warehouse_id' => '6',
                'qty' => '25',
                'type' => 0,
                'movementcode' => null,
                'movementlabel' => null,
                'price' => null,
                'datem' => null,
                'dlc' => null,
                'dluo' => null,
            ],
            [
                'product_id' => '5467',
                'warehouse_id' => '1',
                'qty' => -98,
                'type' => 1,
                'movementcode' => null,
                'movementlabel' => null,
                'price' => null,
                'datem' => null,
                'dlc' => null,
                'dluo' => null,
            ],
            [
                'product_id' => '5467',
                'warehouse_id' => '6',
                'qty' => '98',
                'type' => 0,
                'movementcode' => null,
                'movementlabel' => null,
                'price' => null,
                'datem' => null,
                'dlc' => null,
                'dluo' => null,
            ],
            [
                'product_id' => '5466',
                'warehouse_id' => '1',
                'qty' => -58,
                'type' => 1,
                'movementcode' => null,
                'movementlabel' => null,
                'price' => null,
                'datem' => null,
                'dlc' => null,
                'dluo' => null,
            ]
        ];
        

                

        $resDB = DB::table('prepa_hist_reassort')->insert($data_save);



    }
}
