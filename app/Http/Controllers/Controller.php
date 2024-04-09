<?php

namespace App\Http\Controllers;

use DateTime;
use Exception;
use Throwable;
use League\Csv\Reader;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Http\Controllers\Order;
use App\Models\ProductDolibarr;
use Illuminate\Support\Facades\DB;
use App\Http\Service\PDF\CreatePdf;
use App\Http\Service\Api\PdoDolibarr;
use App\Http\Service\Api\Transferkdo;
use App\Http\Service\Api\TransferOrder;
use App\Repository\Role\RoleRepository;
use App\Repository\User\UserRepository;
use App\Repository\Order\OrderRepository;
use App\Repository\Tiers\TiersRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Repository\Printer\PrinterRepository;
use App\Repository\Product\ProductRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Repository\LogError\LogErrorRepository;
use App\Repository\Reassort\ReassortRepository;
use App\Repository\Categorie\CategoriesRepository;
use App\Http\Service\Woocommerce\WoocommerceService;
use Illuminate\Routing\Controller as BaseController;
use App\Repository\Commandeids\CommandeidsRepository;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Repository\OrderDolibarr\OrderDolibarrRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Illuminate\Support\Facades\File;



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
    private $tiersRepository;
    private $orderDolibarr;
    private $pdf;
    private $woocommerce;
    private $logError;
    private $factorder;
    private $commandeids;
    private $transferkdo;

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
        TiersRepository $tiersRepository,
        OrderDolibarrRepository $orderDolibarr,
        CreatePdf $pdf,
        WoocommerceService $woocommerce,
        LogErrorRepository $logError,
        TransferOrder $factorder,
        CommandeidsRepository $commandeids,
        Transferkdo $transferkdo
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
        $this->orderDolibarr = $orderDolibarr;
        $this->pdf = $pdf;
        $this->woocommerce = $woocommerce;
        $this->logError = $logError;
        $this->factorder = $factorder;
        $this->commandeids = $commandeids;
        $this->transferkdo = $transferkdo;
    }

    // INDEX ADMIN
    public function index()
    {
        $teams = $this->users->getUsersByRole([2, 3, 5]);
        $teams_have_order = $this->orders->getUsersWithOrder()->toArray();
        $teams_have_order_dolibarr = $this->orderDolibarr->getUsersWithOrderDolibarr()->toArray();

        // Merge des préparateurs avec commandes dolibarr et woocomemrce et suppressions doublons
        if(count($teams_have_order_dolibarr) > 0){
            $teams_have_order = array_merge($teams_have_order, $teams_have_order_dolibarr);
            $teams_have_order_array = [];
            $doublon = [];
            foreach($teams_have_order as $have_order){
                if(!in_array($have_order['id'], $doublon)){
                    $teams_have_order_array[] = $have_order;
                }

                $doublon[] = $have_order['id'];
            }
        } else {
            $teams_have_order_array = $teams_have_order;
        }
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
        return view('index', ['teams' => $teams, 'products' => $products, 'roles' => $roles, 'teams_have_order' => $teams_have_order_array, 'number_preparateur' => $number_preparateur]);
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

        $orders = $this->orderController->getOrder();

        $order_process = [];
        $orders_waiting_to_validate = [];
        $orders_validate = [];

        foreach ($orders['orders'] as $order) {
            if(isset($order['details']['discount'])){
                $discount = 0;
                foreach(explode(',',$order['details']['discount']) as $dis){
                    $discount = floatval($discount) + floatval($dis);
                }
                $order['details']['discount'] = $discount;
            }

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
            'count_rea' => count($reassort)
        ]);
    }

    // PRÉPARATEUR COMMANDES DISTRIBUTEURS
    public function ordersDistributeurs()
    {
        $printer = $this->printer->getPrinterByUser(Auth()->user()->id);
        $orders = $this->orderController->getOrderDistributeur();
        $reassort = $this->reassort->getReassortByUser(Auth()->user()->id);

        $order_process = [];
        $orders_waiting_to_validate = [];
        $orders_validate = [];

        foreach ($orders['orders'] as $order) {
            if(isset($order['details']['discount'])){
                $discount = 0;
                foreach(explode(',',$order['details']['discount']) as $dis){
                    $discount = floatval($discount) + floatval($dis);
                }
                $order['details']['discount'] = $discount;
            }
            
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
            'count_rea' => count($reassort)
        ]);
    }

    // PRÉPARATEUR COMMANDES TRANSFERTS DE STOCKS
    public function ordersTransfers(){
        $reassort = $this->reassort->getReassortByUser(Auth()->user()->id);

        // Compte les autres commandes à faire
        $orders = $this->orderController->getOrder();
        $transfers_progress = [];
        $transfers_waiting_to_validate = [];
        $transfers_validate = [];


        foreach ($reassort as $rea) {
            if ($rea['details']['status'] == "waiting_to_validate") {
                $transfers_waiting_to_validate[] = $rea;
            } else if ($rea['details']['status'] == "waiting_validate") {
                $transfers_validate[] = $rea;
            } else {
                $transfers_progress[] = $rea;
            }
        }
  
        return view('preparateur.transfers.index_preparateur', 
        [
            'transfers_waiting_to_validate' => $transfers_waiting_to_validate,
            'transfers_validate' => $transfers_validate,
            'transfers' => isset($transfers_progress[0]) ? $transfers_progress[0] : [] /* Show only first order */,
            'number_transfers' =>  count($transfers_progress),
            'number_transfers_waiting_to_validate' =>  count($transfers_waiting_to_validate),
            'number_transfers_validate' =>  count($transfers_validate),
            'user' => Auth()->user()->name,
            'count_orders' => $orders['count'],
            'count_rea' => count($reassort)
        ]);
    }

    // INDEX EMBALLEUR 
     public function wrapOrder(){
        return view('emballeur.index');
    }

    // INDEX SAV
    public function sav(){
        return view('sav.index');
    }

    // Mise à jour du status du transfers pour mettre en cours de traitement
    public function transfersProcesssing(Request $request){
        $transfer_id = $request->post('transfer_id');
        $status = $request->post('status');

        if($transfer_id && $status){
            // envoyer une notof pour décrémenter les alerte reassort 

            
            $res = $this->reassort->updateStatusReassort($transfer_id, $status);
            if ($res) {
                $this->api->updateSessionStockAlerte("alerte_reassortEnAttente", -1);
                return true;
            }
            return true;
        }
        
    }

    function getVieuxSplay (){

     

        // $donneesSession = session()->all();
        // dd($donneesSession);

        $url = "";
        if (date('N') == 1) {
            $url = asset('public/storage/reassorts/notraite/'.date('d-m-y').'_reassort.csv');
        }
        
             
        $method = "GET";
        $apiKey = env('KEY_API_DOLIBAR'); 
        // $apiKey = 'VA05eq187SAKUm4h4I4x8sofCQ7jsHQd';

        $apiUrl = env('KEY_API_URL');
        // $apiUrl ="https://www.poserp.elyamaje.com/api/index.php/";


        $listWarehouses = $this->api->CallAPI("GET", $apiKey, $apiUrl."warehouses");
        $listWarehouses = json_decode($listWarehouses, true);
      //  $data_reassortaa = DB::table('hist_reassort')->groupBy('identifiant_reassort')->get()->toArray();


      

        $hist_reassort = DB::table('hist_reassort')->get()->toArray();





        $identifiant_unique = array();
        $data_reassort = array();


        foreach ($hist_reassort as $key => $value) {
            if (!isset($data_reassort[$value->identifiant_reassort])) {
                $data_reassort[$value->identifiant_reassort] = [
                    $value
                ];
            }else {
                array_push($data_reassort[$value->identifiant_reassort],$value);
            }
        }

        // foreach ($hist_reassort as $key => $reasort) {
        //     $identif = $reasort->identifiant_reassort;

        //     if (!in_array($identif, $identifiant_unique)) {
        //         array_push($data_reassort,$reasort);
        //         array_push($identifiant_unique,$identif);
        //     }
        // }


        $wh_id_name = array();

        // dd($listWarehouses);
    
        foreach ($listWarehouses as $key => $value) {
           foreach ($value as $k => $val) {
            
            if ($k == "id") {
                $wh_id_name[$val] = $value["label"]; //4444

            }
           }
        }

        // dump($listWarehouses);
       //  dd($data_reassort);

        $liste_reassort = array();
        $id_etrepot_source = "";
        $id_etrepot_destination = "";
        $etat = "";
        $val_etat = 0;

        foreach ($data_reassort as $key => $value) {

            // voir s'il y'a au moin un produit non synchronisé (syncro == 0)
            $etatSyncro = 1;

            foreach ($value as $k => $lineReassort) {

                if ($lineReassort->syncro == 0 && !in_array($lineReassort->product_id,[4670,4674])) {
                    $etatSyncro = 0;
                    break;
                }
            }

            $value = $value[0];

            $id_etrepot_source = (explode("to",$value->sense))[0];
            $id_etrepot_destination = (explode("to",$value->sense))[1];
                
            $val_etat = $value->id_reassort;

            if (!$value->origin_id_reassort) {          
                if ($val_etat == 0) {
                    $etat = '<span class="badge bg-warning text-dark">En attente de préparation</span>';
                    $disabled = "";
                }
                if ($val_etat < 0) {
                    $etat = '<span class="badge bg-info text-dark">En cours de préparation</span>';
                    $disabled = "disabled";
                }
                if ($val_etat > 0) {
                    $etat = '<span class="badge bg-success">Términé</span>';
                    $disabled = "disabled";
                }
            }else {
                if ($value->origin_id_reassort == "Valide_annule") {
                    $etat = '<span class="badge bg-secondary">Validé => Annulé</span>';
                    $disabled = "disabled";
                }else {
                    $etat = '<span class="badge bg-secondary">Annulation ('.$value->origin_id_reassort.')</span>';
                    $disabled = "disabled";
                }
                
            } 

            $liste_reassort[$value->identifiant_reassort] = [
                "identifiant" => $value->identifiant_reassort,
                "libelle_reassort" => $value->libelle_reassort,
                "date" => date('d/m/Y H:i:s', $value->identifiant_reassort),
                "entrepot_source" => $wh_id_name[$id_etrepot_source],
                "entrepot_destination" => $wh_id_name[$id_etrepot_destination],
                "etat" => $etat,
                "val_etat" => $value->id_reassort,
                "origin_id_reassort" => $value->origin_id_reassort,
                "attribue_a" => $value->user_id,
                "disabled" => $disabled,
                "syncro" => $etatSyncro,
                "detail_reassort" => [],
                
            ];
        }

        $limite = 10000;
        // Récupérer les produits 
        $produitParamProduct = array(
            'apikey' => $apiKey,
            'limit' => $limite,
        );

        $product_detail = array();
        $all_products = $this->api->CallAPI("GET", $apiKey, $apiUrl."products",$produitParamProduct);  
        $all_products = json_decode($all_products,true);

        foreach ($all_products as $key => $product) {
            if (!isset($product_detail[$product["id"]])) {
                $product_detail[$product["id"]] = [
                   "label" => $product["label"],
                   "barcode" => $product["barcode"],
                ];
            }
        }

        foreach ($hist_reassort as $key => $reassort) {

            if ($reassort->qty > 0) {
                

                
                $id_product = $reassort->product_id;
               // dump($id_product);
                if (isset($product_detail[$id_product])) {
                    $hist_reassort[$key]->label = $product_detail[$id_product]["label"];
                }else {
                    $hist_reassort[$key]->label = "label inconnu";
                }
                array_push($liste_reassort[$reassort->identifiant_reassort]["detail_reassort"],$reassort);
            }

        }      

        $users = $this->users->getUsers()->toArray();  

        // on libère de la mémoire
        unset($all_products);


        return view('admin.supply',
            [
                "listWarehouses" => $listWarehouses,
                "name_entrepot_a_alimenter" => "inconnue",
                "name_entrepot_a_destocker" => "inconnue",
                "liste_reassort" => $liste_reassort,

                "start_date_origin" => "",
                "end_date_origin" => "",
                "users" => $users,
                "url"=> $url,
                "fileNameReassort" => date('d-m-y').'_reassort.csv',
            ]);

    }

    function calcul_equart_type($array) {
        // Calcul de la moyenne
        $mean = array_sum($array) / count($array);
    
        // Calcul de la somme des carrés des écarts à la moyenne
        $sum_of_squares = array_sum(array_map(function($x) use ($mean) { return pow($x - $mean, 2); }, $array));
    
        // Calcul de la variance
        $variance = $sum_of_squares / count($array);
    
        // Calcul de l'écart-type comme la racine carrée de la variance
        $standard_deviation = sqrt($variance);
    
        return $standard_deviation;
    }


    function completeDates($array) {
        // Récupère la première clé du tableau

        $keys = array_keys($array); // Récupère les clés du tableau
        // $start_date = reset($keys);
        $last_date = date('d/m/Y');
        $start_date = DateTime::createFromFormat('d/m/Y', $last_date)->modify('-28 day')->format('d/m/Y');


        $date_p_1 = $start_date;



        while ($date_p_1 != $last_date) {
            if (!isset($array[$date_p_1])) {
                $array[$date_p_1] = 0;
            }
            $date_p_1 = DateTime::createFromFormat('d/m/Y', $date_p_1)->modify('+1 day')->format('d/m/Y');
        }

        // on ajoute la date d'auj
        // if (!isset($array[$last_date])) {
        //     $array[$last_date] = 0;
        // }

        // on enlève la vente d'auj a voir avec walid
        if (isset($array[$last_date])) {
            unset($array[$last_date]);
        }

        uksort($array, function($a, $b) {
            $date_a = DateTime::createFromFormat('d/m/Y', $a);
            $date_b = DateTime::createFromFormat('d/m/Y', $b);
            return $date_a <=> $date_b;
        });

        $equart_type = round($this->calcul_equart_type($array),2);
        $array["sum"] = array_sum($array);

        $array["equart_type"] = $equart_type;

        return $array; // Retourne le tableau résultat
    }

    function alerteStockCron($token){

     
        

        if ($token == env('TOKEN_REASSOT')) {       

            $numeroJour = date('N');

            // $numeroJour = 5;

            if ($numeroJour <= 5 ) {

                $datasAlerte = array();
                $datasIncompletes = array();
        
                $tab_min = [
                    1 => 0.8,  // lundi      à 17h45h  Alerte
                    2 => 0.65,  // Mardi     à 17h45h  Alerte      
                    3 => 0.5,  // Mercredi   à 17h45h  Alerte
                    4 => 0.3,  // Jeudi      à 17h45h  Alerte
                    5 => 1,  // Vendredi     à 17h45h  // on génère un reassort
                    // 6 => 0.6,  // Samedi     à 22h
                    // 7 => 0.6,  // Dimanche   à 22h
                ];

                $percent_min = $tab_min[$numeroJour];


                $result = $this->alertStocks(15, 0, $token);


                $vente_by_product = $result["vente_by_product"];

            
                foreach ($vente_by_product as $key => $value) {
                    if ($value["stock_actuel"] == 'inconnu' || $value["desiredstock"] == 'inconnu') {

                        array_push($datasIncompletes,$value);

                    }else {
                        if (($value["stock_actuel"]/$value["desiredstock"]) < $percent_min) {
                            array_push($datasAlerte,$value);
                        }
                    }
                    
                }


                if ($numeroJour == 5) {

                    if ($datasAlerte) {
                        // On crée un fichier excel qui contiendra le réassort de lundi
                        $res = $this->exportExcel($datasAlerte,$percent_min);
                        if ($res["response"] == true) {
                            // injecter la réponse dans la table cron 

                            $data = 
                            [
                                'name' => 'Generate_reassort_lundi', 
                                'origin' => 'preparation', 
                                'error' => 0,
                                'message' =>  $res["message"], 
                                'code' => null, 
                                'from_cron' => 1
                            ];
                
                            $this->api->insertCronRequest($data);  

                        }else {
                            $data = 
                            [
                                'name' => 'Generate_reassort_lundi', 
                                'origin' => 'preparation', 
                                'error' => 1,
                                'message' =>  $res["message"], 
                                'code' => null, 
                                'from_cron' => 1
                            ];
                
                            $this->api->insertCronRequest($data); 
                        }
                    }
                    
                                    
                }else {
                    if ($datasAlerte) {
                        // On lance juste une alerte ... la quantité a suggérer serai de la somme entre compbler le reste de la semain + la semaine d'apres
                        $res = $this->exportExcel($datasAlerte,$percent_min);

                        if ($res["response"] == true) {
                            // injecter la réponse dans la table cron 

                            $data = 
                            [
                                'name' => 'Generate_alerte_or_lundi', 
                                'origin' => 'preparation', 
                                'error' => 0,
                                'message' =>  $res["message"], 
                                'code' => null, 
                                'from_cron' => 1
                            ];
                
                        }else {
                            $data = 
                            [
                                'name' => 'Generate_alerte_or_lundi', 
                                'origin' => 'preparation', 
                                'error' => 1,
                                'message' =>  $res["message"], 
                                'code' => null, 
                                'from_cron' => 1
                            ];
                
                        }
                        $this->api->insertCronRequest($data);
                    }
                    
                }

            }

        }else {
            dd("Vous n'avez pas accèes à cette route");
        }

    }

    public function exportExcel($datasAlerte,$percent_min)
    {


        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Ajoutez les entêtes
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'Libelle');
            $sheet->setCellValue('C1', 'Quantité vendu');
            $sheet->setCellValue('D1', 'Catégorie');
            
            // Ajoutez vos données ici en fonction de votre logique
            $row = 2;

            if (date('N') == 5) {
                foreach ($datasAlerte as $key => $value) {

                    $qte = ($percent_min * $value["desiredstock"]) - $value["stock_actuel"];

                    
    
                    $sheet->setCellValue('A' . $row, $value['fk_product']);
                    $sheet->setCellValue('B' . $row, $value['label']);
                    $sheet->setCellValue('C' . $row, $qte);
                    $sheet->setCellValue('D' . $row, "");
                    
                    $row++;
                }
    
                // Créez un Excel et sauvegardez le fichier
                $writer = new Csv($spreadsheet);
                $dateLundi = date("d-m-y", strtotime("+3 days")); 
                $fileName = $dateLundi.'_reassort.csv'; // Nom du fichier
    
                
    
    
                $writer->save(storage_path('app/public/reassorts/notraite/' . $fileName));
    
                // Retournez une réponse de téléchargement pour le fichier Excel
                return ["response" => true, "message" => "réassort créer avec succés"];
            }else {
                foreach ($datasAlerte as $key => $value) {

                    $qte = (($percent_min * $value["desiredstock"]) - $value["stock_actuel"]);
                    // + (1.8 - 0.7)*$value["desiredstock"];

                    // le 0,7 vient du fait qu'on suppose que arrivé au vendredi a 18h on sera a 70% du stock min alors on fait le
                    // calcule pour atteidre les 1.8*stock_desire qui couvriront la semaine d'après
                    
                    $sheet->setCellValue('A' . $row, $value['fk_product']);
                    $sheet->setCellValue('B' . $row, $value['label']);
                    $sheet->setCellValue('C' . $row, ceil($qte));
                    $sheet->setCellValue('D' . $row, "");
                    
                    $row++;
                }  
                
                // Créez un Excel et sauvegardez le fichier
                $writer = new Csv($spreadsheet);
                $date = date("d-m-y", strtotime("+0 days")); 
                $fileName = $date.'_alerte.csv'; // Nom du fichier              
    
                $writer->save(storage_path('app/public/alertes/notraite/' . $fileName));
    
                // Retournez une réponse de téléchargement pour le fichier Excel
                return ["response" => true, "message" => "Fichier d'alerte créer avec succés"];
            }





        } catch (\Throwable $th) {
            return ["response" => false, "message" => $th->getMessage()];
        }
    }

    
    function alertStocks($entrepot_destination, $Njour, $token = NULL){

        if ($token && $token != env('TOKEN_REASSOT')) {
            dd("acces interdit");
        }

        
        $method = "GET";
        $apiKey = env('KEY_API_DOLIBAR'); 
        $apiUrl = env('KEY_API_URL');

        
        if ($entrepot_destination== "15" || $entrepot_destination== "1") {

            $filterHowTC = "t.ref LIKE '%TC1%'";
            $produitParam = array(
                'apikey' => $apiKey,
                'limit' => 10000,
            );
        }

        if ($entrepot_destination== "6") {

            $filterHowTC = "t.ref LIKE '%FA%'";
            $produitParam = array(
                'apikey' => $apiKey,
                'limit' => 10000,
            );
        }

        // On récupère les produits et leurs stock dans l'entrepos selectionner 
        

        $pdoDolibarr = new PdoDolibarr(env('DB_HOST_2'),env('DB_DATABASE_2'),env('DB_USERNAME_2'),env('DB_PASSWORD_2'));
        $stocks_products = $pdoDolibarr->getStockProductByEntrepot($entrepot_destination);

        // Récupérer la liste des entrepots 
        $liste_entrepot = $pdoDolibarr->getAllEntrepot();
        // récupération du nom de l'entrepot
        $name_entrepot = (isset($liste_entrepot[$entrepot_destination])) ? $liste_entrepot[$entrepot_destination]['name_entrepot'] : "inconnu";

        // on récupère les infos de stock alerte et de stock souhaité (llxyq_product_warehouse_properties)  
        $infos_stock_min = $pdoDolibarr->getStockAlerteMin($entrepot_destination); // remettre la variable après

        // On récupère tout lesproduits 
        $all_products = $pdoDolibarr->getAllProduct(); 
        
        // dd($all_products);
        

        // $mois = 1; // nombre de mois
        // $jours = $mois*28;
        $interval = date("Y-m-d", strtotime("-$Njour days")); 
        $coef = 1;//(1.10)/($jours/7); // pour avoir une moyen sur 7 jours et on multipli par un coef de securité

        $array_factures_total = array();


        if (!$token) {
            $produitParam['sqlfilters'] = $filterHowTC . " AND t.datec >= '".date('Y-m-d')." 00:00:00' AND t.datec <= '".date("Y-m-d")." 23:59:59'";
        }else {
            $produitParam['sqlfilters'] = $filterHowTC . " AND t.datec >= '".$interval." 00:00:00' AND t.datec <= '".date("Y-m-d", strtotime("-1 days"))." 23:59:59'";
        }


        $listinvoice = $this->api->CallAPI("GET", $apiKey, $apiUrl."invoices",$produitParam);     
        $factures = json_decode($listinvoice,true); 
            if (!isset($factures["error"])) {
            array_push($array_factures_total,$factures);
        }

        

        $vente_by_product = array();
        $vente_by_product_by_date = array();
        $products_dolibarrs_first_tr = array();

        $kit = $this->reassort->getKits();
        $lot_de_limes_ids = $kit["all_id_pere_kits"];
        $lot_de_limes_vs_corresp = $kit["composition_by_pere"];



        foreach ($array_factures_total as $ktotal => $factures) {
            foreach ($factures as $key => $facture) {
                $lines = $facture["lines"];
                foreach ($lines as $kline => $line) {

                    // $date = date("d/m/Y", $facture["date"]);

                    if ($line["fk_product"] !="") {

                        $id_product = $line["fk_product"];
                        $qty = $line["qty"];  

                        if (in_array($id_product,$lot_de_limes_ids)) {

                            // on coverti le kit en unité dont il est composé
                            $tab_cores = $lot_de_limes_vs_corresp[$id_product];
                            foreach ($tab_cores as $xx => $comp) {
                                $id_product = $comp[0];
                                $qty = $qty*$comp[1]; 

                                if (!isset($vente_by_product[$id_product])) {
                                    $vente_by_product[$id_product] = 
                                    [
                                        "total_vente" => $qty*$coef,
                                        "fk_product" => $id_product,
                                        "name_entrepot" => $name_entrepot,
                                        "seuil_stock_alerte" => (isset($infos_stock_min[$id_product])) ? $infos_stock_min[$id_product]['seuil_stock_alerte'] : "inconnu",
                                        "desiredstock" => (isset($infos_stock_min[$id_product])) ? $infos_stock_min[$id_product]['desiredstock'] : "inconnu",
                                        "stock_actuel" => (isset($stocks_products[$id_product])) ? $stocks_products[$id_product]['reel'] : "inconnu",
                                        "label" => (isset($all_products[$id_product])) ? utf8_encode($all_products[$id_product]['label']) : "inconnu",
                                    ];

                                }else {
                                    // premier ajout de l'element dans le tableau ($products_dolibarrs_first_tr)
                                    $vente_by_product[$id_product]["total_vente"] = $vente_by_product[$id_product]["total_vente"] + $qty*$coef;
                                }

                            }
                        }else {

                            if (!isset($vente_by_product[$id_product])) {
                                $vente_by_product[$id_product] = 
                                [
                                    "total_vente" => $qty*$coef,
                                    "fk_product" => $id_product,
                                    "name_entrepot" => $name_entrepot,
                                    "seuil_stock_alerte" => (isset($infos_stock_min[$id_product])) ? $infos_stock_min[$id_product]['seuil_stock_alerte'] : "inconnu",
                                    "desiredstock" => (isset($infos_stock_min[$id_product])) ? $infos_stock_min[$id_product]['desiredstock'] : "inconnu",
                                    "stock_actuel" => (isset($stocks_products[$id_product])) ? $stocks_products[$id_product]['reel'] : "inconnu",
                                    "label" => (isset($all_products[$id_product])) ? utf8_encode($all_products[$id_product]['label']) : "inconnu",

                                ];
                            }else {
                                $vente_by_product[$id_product]["total_vente"] = $vente_by_product[$id_product]["total_vente"] + $qty*$coef;
                            }

                        }

                            

                    }
                }
            }
        }

        // ajouter les produits invendu en j-1 poir avoir leurs infos 
        foreach ($all_products as $id_product => $value) {


            if (!isset($vente_by_product[$id_product])) {
                if (in_array($id_product,$lot_de_limes_ids)) {
                    // on coverti le kit en unité dont il est composé
                    $tab_cores = $lot_de_limes_vs_corresp[$id_product];

                    foreach ($tab_cores as $xx => $comp) {
                        $id_product = $comp[0];
    
                        if (!isset($vente_by_product[$id_product])) {
                            $vente_by_product[$id_product] = 
                            [
                                "total_vente" => 0,
                                "fk_product" => $id_product,
                                "name_entrepot" => $name_entrepot,
                                "seuil_stock_alerte" => (isset($infos_stock_min[$id_product])) ? $infos_stock_min[$id_product]['seuil_stock_alerte'] : "inconnu",
                                "desiredstock" => (isset($infos_stock_min[$id_product])) ? $infos_stock_min[$id_product]['desiredstock'] : "inconnu",
                                "stock_actuel" => (isset($stocks_products[$id_product])) ? $stocks_products[$id_product]['reel'] : "inconnu",
                                "label" => (isset($all_products[$id_product])) ? utf8_encode($all_products[$id_product]['label']) : "inconnu",

                            ];
    
                        }
    
                    }



                }else {
                    $vente_by_product[$id_product] = 
                    [
                        "total_vente" => 0,
                        "fk_product" => $id_product,
                        "name_entrepot" => $name_entrepot,
                        "seuil_stock_alerte" => (isset($infos_stock_min[$id_product])) ? $infos_stock_min[$id_product]['seuil_stock_alerte'] : "inconnu",
                        "desiredstock" => (isset($infos_stock_min[$id_product])) ? $infos_stock_min[$id_product]['desiredstock'] : "inconnu",
                        "stock_actuel" => (isset($stocks_products[$id_product])) ? $stocks_products[$id_product]['reel'] : "inconnu",
                        "label" => (isset($all_products[$id_product])) ? utf8_encode($all_products[$id_product]['label']) : "inconnu",

                    ];
                }
                
            }
        }


        if ($token) {
            return
            [
                "vente_by_product" => $vente_by_product,
                "liste_entrepot" => $liste_entrepot,
                "interval" => "Vente J-".$Njour,
                "name_entrepot" => $name_entrepot,
                "id_enrepot" => $entrepot_destination,
                "nbrJ" => $Njour,
            ];
        }else {
            return view('admin.ventes_alertes',
            [
                "vente_by_product" => $vente_by_product,
                "liste_entrepot" => json_encode($liste_entrepot),
                "interval" => "Vente J-".$Njour,
                "name_entrepot" => $name_entrepot,
                "id_enrepot" => $entrepot_destination,
                "nbrJ" => $Njour,
            ]);
        }

        
        

    }

 
    function parseCSV($contenu) {

        $lignes = explode("\n", $contenu);
        $tableauDonnees = [];
        $cles = str_getcsv(array_shift($lignes));
        foreach ($lignes as $ligne) {

            if ($ligne) {
                $donneesLigne = str_getcsv($ligne);
                $ligneAssoc = array_combine($cles, $donneesLigne);
                $tableauDonnees[] = $ligneAssoc;
            }
           

        }
    
        return $tableauDonnees;
    }


    function listeAlerte(){

        $dataAlertes = array();
       

        // Vérifiez si le répertoire existe
        $dossiers = [
            storage_path('app/public/alertes/traite'),
            storage_path('app/public/alertes/notraite'),
            storage_path('app/public/reassorts/notraite'),
            storage_path('app/public/reassorts/traite'),
        ];
        foreach ($dossiers as $key => $value) {

            $chemin = $value;


            if (File::exists($chemin)) {
                // Récupérez la liste des fichiers dans le répertoire
                $fichiers = File::files($chemin);
    
                // Bouclez à travers les fichiers pour récupérer leurs noms
                foreach ($fichiers as $fichier) {
    
                    $nomFichier = pathinfo($fichier, PATHINFO_BASENAME);
    
                    if (pathinfo($fichier, PATHINFO_EXTENSION) === 'csv') {
    
                        array_push($dataAlertes,
                        [
                            "filme_name" => $nomFichier = pathinfo($fichier, PATHINFO_BASENAME),
                            "contenu" => $this->parseCSV(File::get($chemin."/".$nomFichier)),
                            "date" => date(str_replace('-', '/', substr($nomFichier, 0, 8))),
                            "url" => asset('public/storage/'.explode("public/",$chemin)[1]."/".$nomFichier),
                            "etat" => (basename(dirname($chemin."/".$nomFichier)) == "traite")? "Traitée" : "Non traitée",
                        ]);
                    }
                }
            }
        }

      
        return view('admin.listesAlertes',
        [
            "dataAlertes" => $dataAlertes
        ]);

    }

    function deplacerFichier($nomFichier) {


        if (strpos($nomFichier, "reassort")) {
            // Chemin du répertoire "notraite"
            $cheminSource = storage_path('app/public/reassorts/notraite');
            // Chemin du répertoire "traite"
            $cheminDestination = storage_path('app/public/reassorts/traite');
        }elseif (strpos($nomFichier, "alerte")) {
            // Chemin du répertoire "notraite"
            $cheminSource = storage_path('app/public/alertes/notraite');
            // Chemin du répertoire "traite"
            $cheminDestination = storage_path('app/public/alertes/traite');
        }else {
            return redirect()->back()->with("error",  "Le fichier ".$nomFichier." n'existe pas dans les alerte non traité");
        }
     

        // Vérifier si le fichier existe dans le répertoire source
        if (file_exists($cheminSource . '/' . $nomFichier)) {
            // Déplacer le fichier vers le répertoire de destination
            $deplacementReussi = rename($cheminSource . '/' . $nomFichier, $cheminDestination . '/' . $nomFichier);
    
            if ($deplacementReussi) {

                $this->api->updateSessionStockAlerte("alerte_stockReassort", -1);
                return redirect()->back()->with("success", "L'alerte a bien été mise au statut traité");

            } else {

                return redirect()->back()->with('error',  "Erreur lors du déplacement du fichier.");

            }
        } else {

            return redirect()->back()->with("error",  "Le fichier ".$nomFichier." n'existe pas dans les alerte non traité");
        }
    }
    
    // Utilisation de la fonction avec un exemple de nom de fichier
    

    function verifieFormatNameFichier($nomDuFichier) {
        // Expression régulière pour vérifier le format du nom de fichier
        $pattern = '/^\d{2}-\d{2}-\d{2}_alerte.*\.csv$/';
    
        // Vérifie si le nom du fichier correspond au format attendu

        if (preg_match($pattern, $nomDuFichier)) {
            return true;
        } else {
            return false;
        }
    }


    function createReassort(Request $request){  


        $by_file = $request->hasFile('file_reassort') && $request->file('file_reassort')->isValid();

       

        $entrepot_source = $request->post('entrepot_source');
        $entrepot_destination = $request->post('entrepot_destination');

        if ($by_file) {
            $file = $request->file('file_reassort');

         
            $csvContent = $file->getContent();

            $reader = Reader::createFromString($csvContent);
            $reader->setHeaderOffset(0);
            $csvDataArray = iterator_to_array($reader->getRecords()); 

            if (!isset($csvDataArray[1]["ID"]) || !isset($csvDataArray[1]["Libelle"]) || !isset($csvDataArray[1]["Quantité vendu"]) || !isset($csvDataArray[1]["Catégorie"])) {
                return redirect()->back()->with('error',  "le format du fichier n'est pas bon");
            }

            // on récupère la liste des produit et leurs stock d'alerte 

            $pdoDolibarr = new PdoDolibarr(env('DB_HOST_2'),env('DB_DATABASE_2'),env('DB_USERNAME_2'),env('DB_PASSWORD_2'));
            $infos_stock_min = $pdoDolibarr->getStockAlerteMin($entrepot_destination);

            $fileNameR = $file->getClientOriginalName();

            $by_reassort_auto = $this->verifieFormatNameFichier($fileNameR);

        }


       



        $start_date = $request->post('start_date');
        $end_date = $request->post('end_date');


        $start_date_origin = $start_date;
        $end_date_origin = $end_date;
   
        
        
        $first_transfert = $request->post('first_transfert');
        $ignore_bp = $request->post('ignore_bp');

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

            $limite = 10000;

           
            // boutique elyamaje
            if ($entrepot_destination== "all") {

                if (true) {
                    $filterHowTC = "";
                    $produitParam = array(
                        'apikey' => $apiKey,
                        'limit' => $limite,
                    );
                }

                if (false) {
                    $start_date = $request->post('start_date');
                    $end_date = $request->post('end_date');

                    if ($start_date && $end_date) {

                        $year_start = explode(", ",$start_date)[1];
                        $year_end = explode(", ",$end_date)[1];

                        $start_date = '"'.date("$year_start-m-d", strtotime($start_date)).'"';
                        $end_date = '"'.date("$year_end-m-d", strtotime($end_date)).'"';
                        
                        $with_dat = ' AND `fac`.`datef` >= '.$start_date.' AND `fac`.`datef` <= '.$end_date;
                    }else {
                        $with_dat = '';
                    }
                    $pdoDolibarr = new PdoDolibarr(env('HOST_ELYAMAJE'),env('DBNAME_DOLIBARR'),env('USER_DOLIBARR'),env('PW_DOLIBARR'));

                    // la categorie des gel = 7
                    // Récuperer tt les produit appartenant a cette categorie
                    $ids_gel = array();
                    $res = $pdoDolibarr->getCategories(7);

                    foreach ($res as $key => $value) {
                        array_push($ids_gel,$value["fk_product"]);
                    }

                    // on récupère tout les lines contenant un des produits de la catégorie 7 (Gels) puis on fait un groupe by sur le 
                    // fk_facture pour savoir combien de facture on a qui contiennent au moins un gel dont la facture est en positive
                    // et payé paye = 1
                    
                    $res2 = $pdoDolibarr->getReelFacturesByCategories($ids_gel, $with_dat);
                    $nbr_facure_gel = count($res2);

                    // dd($nbr_facure_gel);
                
                // traitement selon les clients
                    $fks_facture = array();
                    foreach ($res2 as $key => $value) {
                        array_push($fks_facture,$value["fk_facture"]);
                    }

                    

                    $res4 = $pdoDolibarr->getClientPros($fks_facture, $with_dat);
                    $nbr_clients_pros = count($res4);

                    


                    $res5 = $pdoDolibarr->getAllClientInHavingFacture($with_dat);
                    $nbr_clients = count($res5);

                

                    $rapportBySocid = ($nbr_clients_pros/$nbr_clients)*100;


                    $res3 = $pdoDolibarr->getFk_facture($with_dat);
                    $nbr_facure_total = count($res3);

                    if ($nbr_facure_total) {
                        $oercent_gel_fac = ($nbr_facure_gel/$nbr_facure_total)*100;
                    }else {
                        dd("Aucune facture dans cet interval");
                    }
                    $rapport = ($nbr_facure_gel/$nbr_facure_total)*100;
                    
                    return view('admin.supply',
                    [
                        "listWarehouses" => $listWarehouses,
                        "first_transfert" => $first_transfert,
                        "entrepot_source" => $entrepot_source,
                        "entrepot_destination" => $entrepot_destination,
                        "name_entrepot_a_alimenter" => "Tout les entrepots",
                        "name_entrepot_a_destocker" => "Entrepot Malpasse",
                        "start_date_origin" => $start_date_origin,
                        "end_date_origin" => $end_date_origin,
                        "state" => true ,
                        "nbr_facure_total" => $nbr_facure_total,
                        "nbr_facure_gel" => $nbr_facure_gel,
                        "rapport" => $rapport,

                        "nbr_clients" => $nbr_clients,
                        "nbr_clients_pros" => $nbr_clients_pros,
                        "rapportBySocid" => $rapportBySocid
                    ]);

                }

            }

           // boutique elyamaje
            if ($entrepot_destination== "1" || $entrepot_destination== "15") {

                $filterHowTC = "t.ref LIKE '%TC1%'";
                $produitParam = array(
                    'apikey' => $apiKey,
                    // 'sqlfilters' => "t.ref LIKE '%TC1%'",
                    'limit' => $limite,
                );

            }


            // boutique nice
            if ($entrepot_destination== "11") {
                $filterHowTC = "t.ref LIKE '%TC4%'";
                $produitParam = array(
                    'apikey' => $apiKey,
                    // 'sqlfilters' => "t.ref LIKE '%TC4%'",
                    'limit' => $limite,
                );
            }

            //'sqlfilters' => "t.datec >= '".$interval." 00:00:00' AND t.datec <= '".date("Y-m-d")." 23:59:59' AND t.ref LIKE '%TC4%'",

            // boutique beauty prof
            if ($entrepot_destination== "12") {
                $filterHowTC = "t.ref LIKE '%FA%'";

                $produitParam = array(
                    'apikey' => $apiKey,
                    'sqlfilters' => "t.datec >= '".$interval." 00:00:00' AND t.datec <= '".date("Y-m-d")." 23:59:59'",
                    'limit' => $limite,
                );
            }



            $array_factures_total = array();
            
            if ($first_transfert) {
                if ($start_date && $end_date) {

                $start_date = $request->post('start_date');
                $end_date = $request->post('end_date');

                $year_start = explode(", ",$start_date)[1];
                $year_end = explode(", ",$end_date)[1];

                $start_date = date("$year_start-m-d", strtotime($start_date));
                $end_date = date("$year_end-m-d", strtotime($end_date));

                $date1 = new DateTime($start_date);
                $date2 = new DateTime($end_date);

                $diff = $date2->diff($date1)->days;

                if ($diff>91) {
                    $resultat = [];

                    while ($date2 >= $date1) {

                        $dateDebut = $date2->format('Y-m-d');
                        $dateFin = $date2->modify('-3 month')->format('Y-m-d');
                        $resultat[] = ["debut" => $dateFin, "fin" => $dateDebut];
            
                    }

                    $resultat = array_reverse($resultat);

                    // dd($resultat);
                }else {
                    $resultat = [
                        [
                            "debut" => $start_date,
                            "fin" => $end_date
                        ]
                    ];
                }


                $coef = 1;

                // ajustement des intervale si la periode dépasse un an

                for ($i=1; $i < count($resultat) ; $i++) { 
                    if ($i == count($resultat)-1) {
                        $date = new DateTime($resultat[$i]["debut"]);
                        $date->modify("+$i day");
                        $nouvelleDate = $date->format('Y-m-d');
                        $resultat[$i]["debut"] = $nouvelleDate;

                    }else {
                        $date = new DateTime($resultat[$i]["debut"]);
                        $date->modify("+$i day");
                        $nouvelleDate = $date->format('Y-m-d');
                        $resultat[$i]["debut"] = $nouvelleDate;

                        $date2 = new DateTime($resultat[$i]["fin"]);
                        $date2->modify("+$i day");
                        $nouvelleDate = $date2->format('Y-m-d');
                        $resultat[$i]["fin"] = $nouvelleDate;
                    }
                }

                // dd($resultat);

                foreach ($resultat as $key => $value) {

                    $start_date = $value["debut"];
                    $end_date = $value["fin"];

                    if ($entrepot_destination !== "all") {
                        $produitParam['sqlfilters'] = $filterHowTC . " AND t.datec >= '".$start_date." 00:00:00' AND t.datec <= '".$end_date." 23:59:59'";
                    }else {
                        $produitParam['sqlfilters'] = "t.datec >= '".$start_date." 00:00:00' AND t.datec <= '".$end_date." 23:59:59'";
                    }
        
                    $listinvoice = $this->api->CallAPI("GET", $apiKey, $apiUrl."invoices",$produitParam);     
                    $factures = json_decode($listinvoice,true); 
        
                    sleep(5);
        
                    if (isset($factures["error"]) || !$factures) {
                        continue;
                    }else {
                        array_push($array_factures_total,$factures);
                    }
        
                    unset($factures);
                
                }


                // $produitParam['sqlfilters'] = $produitParam['sqlfilters'] . " AND t.datec >= '".$start_date." 00:00:00' AND t.datec <= '".$end_date." 23:59:59'";

                    
                }else {
                    dd("selectionner les deux date");
                }
            }else {
                $mois = 1; // nombre de mois
                $jours = $mois*28;
                $interval = date("Y-m-d", strtotime("-$jours days")); 
                $coef = (1.10)/($jours/7); // pour avoir une moyen sur 7 jours et on multipli par un coef de securité

               

                if ($ignore_bp && $interval <= "2023-10-07") {
                    $date_start_bp = "2023-10-07";
                    $date_end_bp = "2023-10-10";
                    $coef = $coef*(7/6.5);

                    $produitParam['sqlfilters'] = $filterHowTC . " AND t.datec >= '".$interval." 00:00:00' AND t.datec <= '".$date_start_bp." 23:59:59'";
                    $listinvoice = $this->api->CallAPI("GET", $apiKey, $apiUrl."invoices",$produitParam);     
                    $factures = json_decode($listinvoice,true); 

                    if (!isset($factures["error"])) {
                        array_push($array_factures_total,$factures);
                    }

                    $produitParam['sqlfilters'] = $filterHowTC . " AND t.datec >= '".$date_end_bp." 00:00:00' AND t.datec <= '".date("Y-m-d")." 23:59:59'";
                    $listinvoice = $this->api->CallAPI("GET", $apiKey, $apiUrl."invoices",$produitParam);     
                    $factures = json_decode($listinvoice,true); 

                    if (!isset($factures["error"])) {
                        array_push($array_factures_total,$factures);
                    }
                }else { 
                    
                   
                    $produitParam['sqlfilters'] = $filterHowTC . " AND t.datec >= '".$interval." 00:00:00' AND t.datec <= '".date("Y-m-d")." 23:59:59'";
                    $listinvoice = $this->api->CallAPI("GET", $apiKey, $apiUrl."invoices",$produitParam);     
                    $factures = json_decode($listinvoice,true); 
                      if (!isset($factures["error"])) {
                        array_push($array_factures_total,$factures);
                    }
                }

            }      
       

        // récuperer les label de la categories
        $cat_lab = $this->reassort->getAllCategoriesLabel();

        // Récupérer le couple categories - produits
        $all_categories = $this->reassort->getAllCategoriesAndProducts($cat_lab);

        $cat_no_exist = array();
        $product_no_cat = array();

        // on ajoute dans les infos pour tout les lines  (fk_cat, label cat, parents) 
        foreach ($array_factures_total as $xx => $factures) {           
            foreach ($factures as $kf => $fac) {
                foreach ($fac["lines"] as $kl => $product) {
                    $fk_product = $product["fk_product"];
                    if ($fk_product) {

                        if (isset($all_categories[$fk_product])) {
                            $fk_cat = $all_categories[$fk_product]["fk_categorie"];
                            if (isset($cat_lab[$fk_cat])) {
                                $label = $cat_lab[$fk_cat]['label'];
                                $fk_parent = $cat_lab[$fk_cat]['fk_parent'];
                            }else{

                                $label = "label cat inconnu";
                                $fk_parent = "parents inconnu";
                            }
                        }else {
                            $fk_cat = "inconnu";
                            $label = "label cat inconnu";
                            $fk_parent = "parents inconnu";

                            // array_push($product_no_cat,$fk_product);
                        // dd("produit (".$fk_product. ") n'apartien a aucune categorie actualiser la table all_categories");
                        }                      
                        $array_factures_total[$xx][$kf]["lines"][$kl]["fk_cat"] = $fk_cat;
                        $array_factures_total[$xx][$kf]["lines"][$kl]["label_cat"] = $label;
                        $array_factures_total[$xx][$kf]["lines"][$kl]["fk_parent"] = $fk_parent;
                    }else {
                        $array_factures_total[$xx][$kf]["lines"][$kl]["fk_cat"] = "inconnu";
                        $array_factures_total[$xx][$kf]["lines"][$kl]["label_cat"] = "label cat inconnu";
                        $array_factures_total[$xx][$kf]["lines"][$kl]["fk_parent"] = "parents inconnu";
                    }                        
                }

            }
        }

    
        // 2- on recupere les produit et leurs stock dans les différents entropot
        $produitParamProduct = array(
            'apikey' => $apiKey,
            'limit' => $limite,
        );

        $all_products = $this->api->CallAPI("GET", $apiKey, $apiUrl."products",$produitParamProduct);  
        $all_products = json_decode($all_products,true); 







        // avec la mise à jpurs il faut aller chercher les stocks des produits manuellement (warehouse_array_list)

        // on récupère la liste des produit et leurs stock d'alerte 
        $pdoDolibarr = new PdoDolibarr(env('DB_HOST_2'),env('DB_DATABASE_2'),env('DB_USERNAME_2'),env('DB_PASSWORD_2'));
        // $pdoDolibarr = new PdoDolibarr(env('DB_HOST_2'),"mamo9937_doli54","mamo9937_dolib54","]14]1pSxvS");

      
        // $id_entrepot_select = 1;
        $allStocks = $pdoDolibarr->getAllStockProduct();
       

















    
        $products_dolibarrs = array();
        array_push($products_dolibarrs,$all_products);

        // on ajoute la categorie, le label de la categorie et le parent_categorie au produits

        foreach ($products_dolibarrs as $elm => $element) {
            foreach ($element as $kkp => $product) {

                // dump($allStocks);

                // ajouter la clé warehouse_array_list pour chaque produit (info des stock de ce produit)
                if (isset($allStocks[$product["id"]])) {

                    // dd($allStocks[$product["id"]]);

                    $products_dolibarrs[$elm][$kkp]["warehouse_array_list"][$product["id"]] = $allStocks[$product["id"]];

                    // $product["warehouse_array_list"] = $allStocks[$product["id"]];

                }else {
                    $products_dolibarrs[$elm][$kkp]["warehouse_array_list"][$product["id"]] = [];
                }
                
                $fk_product = $product["id"];
                if ($fk_product) {

                    if (isset($all_categories[$fk_product])) {
                        $fk_cat = $all_categories[$fk_product]["fk_categorie"];
                        if (isset($cat_lab[$fk_cat])) {
                            $label = $cat_lab[$fk_cat]['label'];
                            $fk_parent = $cat_lab[$fk_cat]['fk_parent'];
                        }else{
                            $label = "label cat inconnu";
                            $fk_parent = "parents inconnu";
                        }
                    }else {
                        $fk_cat = "inconnu";
                        $label = "label cat inconnu";
                        $fk_parent = "parents inconnu";
                    }     

                    $products_dolibarrs[$elm][$kkp]["fk_cat"] = $fk_cat;
                    $products_dolibarrs[$elm][$kkp]["label_cat"] = $label;
                    $products_dolibarrs[$elm][$kkp]["fk_parent"] = $fk_parent;
                }else {
                    $products_dolibarrs[$elm][$kkp]["fk_cat"] = "inconnu";
                    $products_dolibarrs[$elm][$kkp]["label_cat"] = "label cat inconnu";
                    $products_dolibarrs[$elm][$kkp]["fk_parent"] = "parents inconnu";
                }

            }
        }



        $products_dolibarrs_first_tr = array();



        foreach ($products_dolibarrs as $key => $value) {
            foreach ($value as $k => $val) {



                $products_dolibarrs_first_tr[$val["id"]] = [

                    "desc" => $val["description"],
                    "libelle" => $val["label"],
                    "total_ttc"=>$val["price_ttc"],
                    "subprice" => $val["price_autogen"], // a revoir
                    
                    "fk_cat" => $val["fk_cat"],
                    "label_cat" => $val["label_cat"],
                    "fk_parent" => $val["fk_parent"],


                ];
            }
        }



        $vente_by_product = array();
        $vente_by_product_by_date = array();

        $kit = $this->reassort->getKits();
        $lot_de_limes_ids = $kit["all_id_pere_kits"];
        $lot_de_limes_vs_corresp = $kit["composition_by_pere"];



        if ($by_file) {

          
            foreach ($csvDataArray as $key => $value) {
                $id_product = $value["ID"];
                $qty = $value["Quantité vendu"];  
                
                if (in_array($id_product,$lot_de_limes_ids)) {
    
                    // on coverti le kit en unité dont il est composé
                    $tab_cores = $lot_de_limes_vs_corresp[$id_product];
                    foreach ($tab_cores as $xx => $comp) {
                        $id_product = $comp[0];
                        $qty = $qty*$comp[1]; 


                        if (!isset($vente_by_product[$id_product])) {
                            $vente_by_product[$id_product] = 
                            [
                                "qty" => $qty,
                                "desc" => $products_dolibarrs_first_tr[$id_product]["desc"],
                                "libelle" => $products_dolibarrs_first_tr[$id_product]["libelle"],
                                "total_ttc"=>$products_dolibarrs_first_tr[$id_product]["total_ttc"],
                                "subprice" => $products_dolibarrs_first_tr[$id_product]["subprice"],
                                
                                "fk_cat" => $products_dolibarrs_first_tr[$id_product]["fk_cat"],
                                "label_cat" => $products_dolibarrs_first_tr[$id_product]["label_cat"],
                                "fk_parent" => $products_dolibarrs_first_tr[$id_product]["fk_parent"],
                            ];
                        }else {
                            $vente_by_product[$id_product]["qty"] = $vente_by_product[$id_product]["qty"] + $qty;
                        }

                    }
                }else {
                    if (!isset($vente_by_product[$id_product])) {
                        $vente_by_product[$id_product] = 
                        [
                            "qty" => $qty,
                            "desc" => $products_dolibarrs_first_tr[$id_product]["desc"],
                            "libelle" => $products_dolibarrs_first_tr[$id_product]["libelle"],
                            "total_ttc"=>$products_dolibarrs_first_tr[$id_product]["total_ttc"],
                            "subprice" => $products_dolibarrs_first_tr[$id_product]["subprice"],

                            "fk_cat" => $products_dolibarrs_first_tr[$id_product]["fk_cat"],
                            "label_cat" => $products_dolibarrs_first_tr[$id_product]["label_cat"],
                            "fk_parent" => $products_dolibarrs_first_tr[$id_product]["fk_parent"],
                        ];
                    }else {
                        $vente_by_product[$id_product]["qty"] = $vente_by_product[$id_product]["qty"] + $qty;
                    }
                }

            }
        
        }else {
            foreach ($array_factures_total as $ktotal => $factures) {
                foreach ($factures as $key => $facture) {
                    $lines = $facture["lines"];
                    foreach ($lines as $kline => $line) {

                        $date = date("d/m/Y", $facture["date"]);

                        if ($line["fk_product"] !="") {
    
                            $id_product = $line["fk_product"];
                            $qty = $line["qty"];  
    
                            if (in_array($id_product,$lot_de_limes_ids)) {
    
                                // on coverti le kit en unité dont il est composé
                                $tab_cores = $lot_de_limes_vs_corresp[$id_product];
                                foreach ($tab_cores as $xx => $comp) {
                                    $id_product = $comp[0];
                                    $qty = $qty*$comp[1]; 

                                    // l'élément existe déja dans le tableau ($products_dolibarrs_first_tr)
                                    if (!isset($vente_by_product[$id_product])) {
                                        $vente_by_product[$id_product] = 
                                        [
                                            "qty" => $qty*$coef,
                                            "desc" => $products_dolibarrs_first_tr[$id_product]["desc"],
                                            "libelle" => $products_dolibarrs_first_tr[$id_product]["libelle"],
                                            "total_ttc"=>$products_dolibarrs_first_tr[$id_product]["total_ttc"],
                                            "subprice" => $products_dolibarrs_first_tr[$id_product]["subprice"],
                                            
                                            "fk_cat" => $products_dolibarrs_first_tr[$id_product]["fk_cat"],
                                            "label_cat" => $products_dolibarrs_first_tr[$id_product]["label_cat"],
                                            "fk_parent" => $products_dolibarrs_first_tr[$id_product]["fk_parent"],
                                        ];

                                    }else {
                                        // premier ajout de l'element dans le tableau ($products_dolibarrs_first_tr)
                                        $vente_by_product[$id_product]["qty"] = $vente_by_product[$id_product]["qty"] + $qty*$coef;
                                    }
    
                                }
                            }else {
                                if (!isset($vente_by_product[$id_product])) {
                                    $vente_by_product[$id_product] = 
                                    [
                                        "qty" => $qty*$coef,
                                        "desc" => $line["desc"],
                                        "libelle" => $line["libelle"],
                                        "total_ttc"=>$line["total_ttc"],
                                        "subprice" => $line["subprice"],
    
                                        "fk_cat" => $line["fk_cat"],
                                        "label_cat" => $line["label_cat"],
                                        "fk_parent" => $line["fk_parent"],
                                    ];
                                }else {
                                    $vente_by_product[$id_product]["qty"] = $vente_by_product[$id_product]["qty"] + $qty*$coef;
                                }

                                // remplissage du tableau de vente des produits par date 

                                // if (!isset($vente_by_product_by_date[$id_product])) {

                                //     $vente_by_product_by_date[$id_product][$date] = intval($qty);

                                // }else {
                                //     if (!isset($vente_by_product_by_date[$id_product][$date])) {
                                //         $vente_by_product_by_date[$id_product][$date] = intval($qty);
                                //     }else {
                                //         $vente_by_product_by_date[$id_product][$date] = $vente_by_product_by_date[$id_product][$date]+ $qty;
                                //     }
                                // }


                            }
    
                                
    
                        }
                    }
                }
            }
        }

        
        //  trier tout les tableaux vente_by_product_by_date par date :
        // foreach ($vente_by_product_by_date as $key => $value) {
        //     $vente_by_product_by_date[$key] = $this->completeDates($value);
        // }
        // dd($vente_by_product_by_date);

        // 3- on récupère les entrepots existant 
        $warehouses = $this->api->CallAPI("GET", $apiKey, $apiUrl."warehouses");  
        $warehouses = json_decode($warehouses,true);
         

        $warehouses_product_stock = array();
        $liste_warehouse = array();

        foreach ($warehouses as $key_wh => $warehouse) {
            
            if (!isset($warehouses_product_stock[$warehouse["label"]])) {

                // recuperer la liste des entrepots sans doublon
                array_push($liste_warehouse,$warehouse["label"]);
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
  
          if ($entrepot_destination== "all") {
            $name_entrepot_a_alimenter = "all_entrepot_a_alimenter";
            $name_entrepot_a_destocker = "all_entrepot_a_destocker";
          }

          if ($name_entrepot_a_alimenter == "" || $name_entrepot_a_destocker == "") {
              dd("entrepot n'a pas pu etre determine");
          }

        //   dump($vente_by_product);
        //   dd($products_dolibarrs_first_tr);
        if ($first_transfert) {

            return view('admin.supply',
            [
                "listWarehouses" => $listWarehouses,
                "vente_by_product" => $vente_by_product, 
                "first_transfert" => $first_transfert,

                "entrepot_source" => $entrepot_source,
                "entrepot_destination" => $entrepot_destination,
                "name_entrepot_a_alimenter" => $name_entrepot_a_alimenter,
                "name_entrepot_a_destocker" => $name_entrepot_a_destocker,

                "start_date_origin" => $start_date_origin,
                "end_date_origin" => $end_date_origin,

                

            ]);
        }    


       foreach ($products_dolibarrs as $key_pr_do => $product_dolibarr) {
            foreach ($product_dolibarr as $k_p => $product) {

                if ($product["warehouse_array_list"]) {
                
                    foreach ($product["warehouse_array_list"] as $k_whp => $war_h_liste) {

                        foreach ($liste_warehouse as $wk => $warehouse) {
                            if (!in_array($warehouse, array_column($war_h_liste, 'warehouse'))) {
                                    $products_dolibarrs[$key_pr_do][$k_p]["warehouse_array_list"][$k_whp][] = [
                                        "warehouse" => $warehouse,
                                        "stock" => "0"
                                ];
                            }
                        }
                    }
                }else {

                    $products_dolibarrs[$key_pr_do][$k_p]["warehouse_array_list"][$product["id"]] = [];

                    foreach ($liste_warehouse as $key => $warehouse) {
                        array_push($products_dolibarrs[$key_pr_do][$k_p]["warehouse_array_list"][$product["id"]],[
                            "warehouse" => $warehouse,
                            "stock" => "0"
                        ]);
                    }
                }
            }
        }

        // dd($products_dolibarrs[0][2]);

       
        // remplissage du tableau "list_product" pour chaque entrepot (tout les entrepot) NB : on peut se limiter au remplissage que du concerné

        // on boucle sur tout les produits existants

        foreach ($products_dolibarrs as $key_pr_do => $product_dolibarr) {
            foreach ($product_dolibarr as $k_p => $product) {

                
                if ($product["warehouse_array_list"]) {
                    if (count($product["warehouse_array_list"]) != 1) {
                        dump("produit en deux fois !");
                        dd($product);
                    }
                    foreach ($product["warehouse_array_list"] as $k_whp => $war_h_liste) {
                        foreach ($war_h_liste as $ww => $pr_st_wh) {  
                            // $warehouses_product_stock est le tableau qui contient list_product  vide
                            // $pr_st_wh["warehouse"] nous permet de cibler le bon entrepot et on met la quantité du produit en question $product
                            $warehouses_product_stock[$pr_st_wh["warehouse"]]["list_product"][$product["id"]] = 
                            [
                                "barcode" => $product["barcode"]?$product["barcode"]:"no_barcode",
                                "product_id" => $product["id"],
                                "price" => $product["price"]? $product["price"]:"0", 
                                "stock" => $pr_st_wh["stock" ]?$pr_st_wh["stock" ]:0,
                                "libelle" => $product["label"],

                                "fk_cat" => $product["fk_cat"],
                                "label_cat" => $product["label_cat"],
                                "fk_parent" => $product["fk_parent"],
                            ];
                        }
                    }
                }
            }
        }   
        

        $products_reassort = array();
        $products_non_vendu_in_last_month = array();

        $tab_stock_vs_demande = array();
        foreach ($warehouses_product_stock[$name_entrepot_a_alimenter]["list_product"] as $kproduct => $stock_in_war) {

            // source data
            $qte_en_stock_in_source = "";
            if (isset($warehouses_product_stock[$name_entrepot_a_destocker]["list_product"][$kproduct])) {
                $qte_en_stock_in_source = $warehouses_product_stock[$name_entrepot_a_destocker]["list_product"][$kproduct]["stock"];
            }else {
                $qte_en_stock_in_source = "0";
            }

            if (isset($vente_by_product[$kproduct])) {


             
              

                // on compare les vente par semaine et la quantité dont on dispose dans l'entrepot

                if ($by_file) {

                    
                    
                    // dd($infos_stock_min);

                    // depuis un fichier reassort automatique

                    if ($by_reassort_auto == true) {

                        $qte_act = $stock_in_war["stock"]?$stock_in_war["stock"]:0;
                        $demande = ceil($vente_by_product[$kproduct]["qty"]);

                        array_push($products_reassort,[
                            "entrepot_a_alimenter" =>$name_entrepot_a_alimenter,
                            "name_entrepot_a_destocker" => $name_entrepot_a_destocker,
                            "qte_en_stock_in_source" => $qte_en_stock_in_source,
                            "libelle" => $stock_in_war["libelle"],
                            "product_id" => $kproduct,
                            "barcode" => $stock_in_war["barcode"],
                            "qte_act" => $qte_act,
                            "price" => $stock_in_war["price"]?$stock_in_war["price"]:"0",
                            "demande" => $demande,

                            "qte_optimale" => $qte_act + $demande , // ($infos_stock_min[$kproduct])? $infos_stock_min[$kproduct]["desiredstock"]*0.65 : 0,
    
                            "fk_cat" => $stock_in_war["fk_cat"],
                            "label_cat" => $stock_in_war["label_cat"],
                            "fk_parent" => $stock_in_war["fk_parent"],
    
                        ]);

                        // if ($kproduct == 5262) {
                        //     dump($infos_stock_min[$kproduct]);
                        //     dump(
                        //         [
                        //             "entrepot_a_alimenter" =>$name_entrepot_a_alimenter,
                        //             "name_entrepot_a_destocker" => $name_entrepot_a_destocker,
                        //             "qte_en_stock_in_source" => $qte_en_stock_in_source,
                        //             "libelle" => $stock_in_war["libelle"],
                        //             "product_id" => $kproduct,
                        //             "barcode" => $stock_in_war["barcode"],
                        //             "qte_act" => $stock_in_war["stock"]?$stock_in_war["stock"]:0,
                        //             "price" => $stock_in_war["price"]?$stock_in_war["price"]:"0",
                        //             "demande" => ceil($vente_by_product[$kproduct]["qty"]),
        
                        //             "qte_optimale" => ($infos_stock_min[$kproduct])? $infos_stock_min[$kproduct]["desiredstock"]*0.65 : 0,
            
                        //             "fk_cat" => $stock_in_war["fk_cat"],
                        //             "label_cat" => $stock_in_war["label_cat"],
                        //             "fk_parent" => $stock_in_war["fk_parent"],
            
                        //         ]
                        //     );
                        // }

                    }else {
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
                            "qte_optimale" => $by_file? ceil($vente_by_product[$kproduct]["qty"]) : ceil($vente_by_product[$kproduct]["qty"])*3,
    
                            "fk_cat" => $stock_in_war["fk_cat"],
                            "label_cat" => $stock_in_war["label_cat"],
                            "fk_parent" => $stock_in_war["fk_parent"],
    
                        ]);
                    }
                   
                }else {
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
                            "qte_optimale" => $by_file? ceil($vente_by_product[$kproduct]["qty"]) : ceil($vente_by_product[$kproduct]["qty"])*3, /// iciiiii
    
                            "fk_cat" => $stock_in_war["fk_cat"],
                            "label_cat" => $stock_in_war["label_cat"],
                            "fk_parent" => $stock_in_war["fk_parent"],
    
                        ]);
                    }
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
                    "qte_optimale" => "inconnue",

                    "fk_cat" => $stock_in_war["fk_cat"],
                    "label_cat" => $stock_in_war["label_cat"],
                    "fk_parent" => $stock_in_war["fk_parent"],
                ]);
            }
        }

        // Dans les produit qui n'ont pas été vendu dans le moi on sort ceux dont la qté est inférieur a 5
        // $products_non_vendu_in_last_month_inf_5 = array();

      //  dd($products_reassort);

        // foreach ($products_non_vendu_in_last_month as $key => $value_) {
        //     if ($value_["qte_act"] < 5) {
        //         array_push($products_non_vendu_in_last_month_inf_5,$value_);
        //     }
        // }

        foreach ($warehouses_product_stock[$name_entrepot_a_destocker]["list_product"] as $key => $value) {
            if (isset($warehouses_product_stock[$name_entrepot_a_alimenter]["list_product"][$key])) {
               $warehouses_product_stock[$name_entrepot_a_destocker]["list_product"][$key]["qte_in_destination"] = 
               $warehouses_product_stock[$name_entrepot_a_alimenter]["list_product"][$key]["stock"]? $warehouses_product_stock[$name_entrepot_a_alimenter]["list_product"][$key]["stock"]:0;
            }
        }


        // récupérer les user 
        $users = $this->users->getUsers()->toArray();

        $start_date_origin = "";
        $end_date_origin = "";

    //    dd($warehouses_product_stock[$name_entrepot_a_destocker]["list_product"]);

        // foreach ($products_reassort as $key => $value) {
        //     if ($value["product_id"] == 5262) {
        //         dump($value);
        //     }
        // }

        return view('admin.supply',
            [
                "listWarehouses" => $listWarehouses,
                "products_reassort" => $products_reassort, 
                // "products_non_vendu_in_last_month_inf_5" => $products_non_vendu_in_last_month_inf_5,
                "entrepot_source" => $entrepot_source,
                "entrepot_destination" => $entrepot_destination,
                "entrepot_source_product" => $warehouses_product_stock[$name_entrepot_a_destocker]["list_product"],
                "name_entrepot_a_alimenter" => $name_entrepot_a_alimenter,
                "name_entrepot_a_destocker" => $name_entrepot_a_destocker,
                "users" => $users,
                "first_transfert" => $first_transfert,

                "start_date_origin" => $start_date_origin,
                "end_date_origin" => $end_date_origin,
                "by_file" => $by_file,
                "by_reassort_auto" => isset($by_reassort_auto)? $by_reassort_auto : NULL,

                
            ]);
    }




    function postReassort(Request $request,$data = NULL){

        
       


        try {

            $methode = $request->isMethod('post');

            $data_save = array();
            $incrementation = 0;
            $decrementation = 0;
            $identifiant_reassort = time();

            if ($methode) { 
                          
                
                $user_id = $request->post('user');
                $tabProduitReassort1 = $request->post('tabProduitReassort1');
                $entrepot_source = $request->post('entrepot_source');
                $entrepot_destination = $request->post('entrepot_destination');
                $name_date_reassort = $request->post('libele_reassort')? $request->post('libele_reassort'):"reassort_du_".date('Y-m-d H:i:s');
                $sense_transfert = $entrepot_source."to".$entrepot_destination;
            }else {

                $user_id = $request->post('user');
                $tabProduitReassort1 = $request->post('tabProduitReassort1');
                $entrepot_source = $request->post('entrepot_source');
                $entrepot_destination = $request->post('entrepot_destination');
                $name_date_reassort = $request->post('libele_reassort')? $request->post('libele_reassort'):"reassort_du_".date('Y-m-d H:i:s');
                $sense_transfert = $entrepot_source."to".$entrepot_destination;

            }


                foreach ($tabProduitReassort1 as $key => $lineR) {

                    if ($lineR["qte_transfere"] != 0) {           
                        $data1 = array(
                            'product_id' => $lineR["product_id"],
                            'warehouse_id' => $entrepot_source, 
                            'qty' => $lineR["qte_transfere"] * (-1), 
                            'type' => 1, 
                            'movementcode' => NULL, 
                            'movementlabel' => 'Transfere via preparation', 
                            'price' => isset($lineR["price"])? $lineR["price"]:0, 
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
                            'price' => isset($lineR["price"])? $lineR["price"]:0,
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

                // envoi de notif pour incrémenter les alertes de reassort

                $this->api->updateSessionStockAlerte("alerte_reassortEnAttente", 1);

                return ["response" => true,"decrementation" => $decrementation,"incrementation" => $incrementation,"resDB" => $resDB];
           

        } catch (\Throwable $th) {
            return ["response" => false,"decrementation" => $decrementation,"incrementation" => $incrementation, "error" => $th->getMessage()];
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
                    
                    $transfert[$key]["movementlabel"] = "Annulation transfert (".$line["id_reassort"].")";

                    $transfert[$key]["datem"] = $date;
                    $transfert[$key]["dlc"] = $date;
                    $transfert[$key]["dluo"] = $date;
                    $transfert[$key]["identifiant_reassort"] = $identifiant_reassort;
                    $transfert[$key]["pick"] = $line["qty"]? $line["qty"]:0;
                    
                   
                    if ($line[ "warehouse_id"] == $wh_id_source) {
                        $transfert[$key]["warehouse_id"] = $wh_id_destination;
                    }

                    if ($line[ "warehouse_id"] == $wh_id_destination) {
                        $transfert[$key]["warehouse_id"] = $wh_id_source;
                    }

                    $transfert[$key]["sense"] = $new_sense;

                    $data_reassort_annule = [
                        'product_id' => $line["product_id"],
                        'warehouse_id' => ($line["warehouse_id"]==$wh_id_source)? $wh_id_destination:$wh_id_source, 
                        'qty' => $line["qty"], 
                        'type' => $line["type"], 
                        'movementcode' => $line["movementcode"], 
                        'movementlabel' => "Annulation transfert via prep (".$identifiant.")", 
                        'price' => $line["price"], 
                        'datem' => date("Y-m-d"), 
                        'dlc' => date("Y-m-d"),
                        'dluo' => date("Y-m-d"),
                    ];

                   $id_reassort_cancel = $this->executReassortInverse($data_reassort_annule);

                    $transfert[$key]["id_reassort"] = $id_reassort_cancel;
                    $transfert[$key]["origin_id_reassort"] = $identifiant;
                    $transfert[$key]["status"] = "cancelling";

                }

                // dd($transfert);
                $resDB = DB::table('hist_reassort')->insert($transfert);
                // mettre en annule le identifiant

                $colonnes_values = ['origin_id_reassort' => "Valide_annule",'status' => "canceled"];
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

            
            $apiUrl = env('KEY_API_URL');
            $apiKey = env('KEY_API_DOLIBAR');
            // $apiUrl = 'https://www.transfertx.elyamaje.com/api/index.php/';
            // $apiKey = 'f2HAnva64Zf9MzY081Xw8y18rsVVMXaQ';

            
            
            $id = request('id');
            $token = request('tokenPrepa');
            $server_name = request('server_name');

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



                        return redirect('https://'.$server_name.'/commande/list.php?leftmenu=orders&&action=successOrderToPreparation');

                    }else {

                        return redirect('https://'.$server_name.'/commande/card.php?id='.$id.'&&leftmenu=orders&&action=errorCodebare');

                       // return "le produit (". $product_no_bc.") n'a pas de code barre";  
                    }
                }else {

                    // $message = "Le devis n'a pas été validé";
                    return redirect('https://'.$server_name.'/commande/card.php?id='.$id.'&&leftmenu=orders&&action=devisIvalide');
                }
            }else {
                // $message = "pas le droit";
                return redirect('https://'.$server_name.'/commande/card.php?id='.$id.'&&leftmenu=orders&&action=errorDroit');

            }
        } catch (Throwable $th) {
            return $th->getMessage();
        }
    }

    function changeStatutOfOrder($id_order,$id_statut){

        try {
        
            $apiUrl = env('KEY_API_URL');
            $apiKey = env('KEY_API_DOLIBAR');
            // $apiUrl = 'https://www.transfertx.elyamaje.com/api/index.php/';
            // $apiKey = 'f2HAnva64Zf9MzY081Xw8y18rsVVMXaQ';

            $order_put = $this->api->CallAPI("PUT", $apiKey, $apiUrl."orders/".$id,json_encode(["statut"=> $id_statut]));
            return true;

        } catch (\Throwable $th) {

            return false;
        }
    }

    function changeUserForReassort(){

        try {

            $value = request('value');

            $id_user = explode(',',$value)[0];
            $id_reassort = explode(',',$value)[1];


            $res = $this->reassort->updateUserReassort($id_user,$id_reassort);

            if ($res == true) {
                return ["response" => true];
            }else {
                return ["response" => false, "message"=> $res];
            }

    
            
        } catch (\Throwable $th) {

            return ["response" => false, "message"=> $th->getMessage()];
        }
      


    }

    function executReassortInverse($data){
  
        $apiUrl = env('KEY_API_URL');
        $apiKey = env('KEY_API_DOLIBAR');
        // $apiUrl = 'https://www.transfertx.elyamaje.com/api/index.php/';
        // $apiKey = 'f2HAnva64Zf9MzY081Xw8y18rsVVMXaQ';
       
        return $stockmovements = $this->api->CallAPI("POST", $apiKey, $apiUrl."stockmovements",json_encode($data));
    }


    function bordereauChrono(){

        $date = date('Y-m-d');
        $orders = $this->orders->getChronoLabelByDate($date)->toArray();
        $order_detail = [];
        $total_weight = 0;

        if(count($orders) > 0){
            foreach($orders as $key => $order){
                $total_weight = floatval($total_weight) + floatval($order['weight']);
    
                // Par envoie 
                $order_detail['orders'][$order['shipping_customer_country']]['orders'][$order['order_woocommerce_id']] = [
                    'weight' => $order['weight'],
                    'tracking_number' => $order['tracking_number'],
                    'shipping_method' => $order['shipping_method'],
                    'product_code' => $order['product_code'],
                    'billing_customer_company' => $order['shipping_customer_company'] != "" ? $order['shipping_customer_company'] : $order['shipping_customer_last_name'].' '.$order['shipping_customer_first_name'],
                    'first_name' => $order['shipping_customer_first_name'],
                    'last_name' => $order['shipping_customer_last_name'],
                    'postcode' => $order['shipping_customer_postcode'],
                    'city' => $order['shipping_customer_city'],
                    'country' => $order['shipping_customer_country'],
                    'customer_id' => $order['customer_id'],
                ];  
    
               
                $weight = $order_detail['orders'][$order['shipping_customer_country']]['orders'][$order['order_woocommerce_id']]['weight'];
    
                $order_detail['orders'][$order['shipping_customer_country']]['total_weight'] = 
                isset($order_detail['orders'][$order['shipping_customer_country']]['total_weight']) ? 
                floatval($order_detail['orders'][$order['shipping_customer_country']]['total_weight']) + floatval($weight): 
                floatval($weight);
                $order_detail['orders'][$order['shipping_customer_country']]['total_order'] = count($order_detail['orders'][$order['shipping_customer_country']]['orders']);
    
                $order_detail['total_weight'] = $total_weight;
            }
    
            $order_detail['total_order'] = count($orders);
            return $this->pdf->generateBordereauChrono($order_detail);
        } else {
            return redirect()->route('bordereaux')->with('error', 'Aucune étiquette chronopost générées aujourd\'hui');
        }
    }

    public function shop(){
        return view('shop.index');
    }

    public function giftCardOrders($token){

        if($token == "lMxNFRyfpoh1gTs9HK3LqJtQtXxIkSN4k8G7Ia6ihkTB!U1k29Cf!Bz5414jiop"){
            $status = "completed";
            $after = date('Y-m-d H:i:s', strtotime('-2 day'));
            $per_page = 100;
            $page = 1;
            $orders = $this->api->getOrdersWoocommerce($status, $per_page, $page, $after);

            if(isset($orders['message'])){
            $this->logError->insert(['order_id' => 0, 'message' => 'Tache Cron commande avec carte cadeaux seulement : '.$orders['message']]);
            return false;
            }

            if(!$orders){
            return array();
            } 
            
            $count = count($orders);
    
            // Check if others page
            if($count == 100){
            while($count == 100){
                $page = $page + 1;
                $orders_other = $this->api->getOrdersWoocommerce($status, $per_page, $page, $after);
            
                if(count($orders_other ) > 0){
                $orders = array_merge($orders, $orders_other);
                }
            
                $count = count($orders_other);
            }
            }  

            $order_to_billing = [];

            // Check if just gift card in order
            foreach($orders as $order){
                $item_gift_card = 0;
                foreach($order['line_items'] as $or){
                    if(str_contains($or['name'], 'Carte Cadeau')){
                        $item_gift_card = $item_gift_card + 1;
                    }
                }

                if($item_gift_card == count($order['line_items'])){
                    $order['coupons'] = '';
                    $order['preparateur'] = 'Aucun';
                    $order['emballeur'] = 'Aucun';
                    $order['order_woocommerce_id'] = $order['id'];
                    $order['order_id'] =  $order['id'];
                    $order['total_order'] =  $order['total'];
                    $order['total_tax_order'] =  $order['total_tax'];
                    $order['date'] =  $order['date_created'];
                    $order['gift_card_amount'] = 0;
                    $order['shipping_amount'] = 0;
                    $order['shipping_method_detail'] = "";
                    $order['discount_amount'] = 0;

                    $order_to_billing[] = $order;

                    if(count($order_to_billing) == 4){
                        // Envoie à la facturation par 4
                        $this->transferkdo->transferkdo($order_to_billing);
                        // Réinitialise le tableau
                        $order_to_billing = [];
                }
                    // Remplacer par fonction qui facture plusieurs fois
                }
            } 

    

            if(count($order_to_billing) > 0){
                // Envoie à la facturation par 4
                $this->transferkdo->transferkdo($order_to_billing);
            }
        } 
    }

    function updateSessionByNotif(Request $request){
        try {

            $cle = $request->post('cle');
            $value = $request->post('value');

            // Récupérer la valeur actuelle de la variable de session
            $ancienneValeur = session()->get($cle);
            // Décrémenter la valeur
            $nouvelleValeur = $ancienneValeur + $value;
            // Réattribuer la nouvelle valeur à la variable de session
            session()->put($cle, $nouvelleValeur);
        
            return['response' => true, 'data' => [
                    'nouvelleValeur' => $nouvelleValeur,
                    'cle' => $cle,
                    'value' => $value
                    ]];
    
        } catch (\Throwable $th) {

            return['response' => false, 'message' => $th->getMessage()];
        }
    }

    function updateStockAlertAndStockDesire(Request $request){

        
        

        try {
            $file = $request->hasFile('file_stock_min') && $request->file('file_stock_min')->isValid();
            $id_entrepot_select = $request->post('id_entrepot_select');

            if ($file && $id_entrepot_select) {
                $file = $request->file('file_stock_min');         
                $csvContent = $file->getContent();
                $reader = Reader::createFromString($csvContent);
                $reader->setHeaderOffset(0);
                $csvDataArray = iterator_to_array($reader->getRecords()); 

                if (!isset($csvDataArray[1]["id_product"]) || !isset($csvDataArray[1]["stock_desire"])) {
                    return redirect()->back()->with('error',  "le format du fichier n'est pas bon");
                }


                // on récupère la liste des produit et leurs stock d'alerte 
                $pdoDolibarr = new PdoDolibarr(env('DB_HOST_2'),env('DB_DATABASE_2'),env('DB_USERNAME_2'),env('DB_PASSWORD_2'));
                // $pdoDolibarr = new PdoDolibarr(env('DB_HOST_2'),"mamo9937_doli54","mamo9937_dolib54","]14]1pSxvS");

                // $id_entrepot_select = 1;
                $response = $pdoDolibarr->updateStockByIdProductAndEntrepot($id_entrepot_select, $csvDataArray);

                if ($response) {
                    return redirect()->back()->with('success', 'Les stocks minimum et alertes ont été mis à jour');
                }else {
                    return redirect()->back()->with('error',  "Une érreur s'est produite code 1");
                }

            }else {
                return redirect()->back()->with('error',  "Merci de selectionner l'entrepot et un fichier au bon format");
            }
        } catch (\Throwable $th) {
            return redirect()->back()->with('error',  $th->getMessage());
        }
    }

}