<?php

namespace App\Http\Controllers;

use DateTime;
use Exception;
use Mike42\Escpos\Printer;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;

use App\Models\ProductDolibarr;
use App\Models\Categorie_dolibarr;
use Illuminate\Support\Facades\DB;
use App\Models\products_categories;
use App\Models\Products_association;
use App\Http\Service\Api\PdoDolibarr;
use App\Http\Service\Api\TransferOrder;
use Illuminate\Support\Facades\Http;
use App\Http\Service\Api\Transfertext;
use App\Http\Service\PDF\InvoicesPdf;
use App\Http\Service\Api\Construncstocks;
use App\Repository\Role\RoleRepository;
use App\Repository\User\UserRepository;
use App\Repository\Order\OrderRepository;
use App\Repository\History\HistoryRepository;
use App\Repository\Printer\PrinterRepository;
use App\Repository\Product\ProductRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Repository\LogError\LogErrorRepository;
use App\Repository\Label\LabelMissingRepository;
use App\Repository\Colissimo\ColissimoRepository;
use App\Repository\Categorie\CategoriesRepository;
use App\Http\Service\Woocommerce\WoocommerceService;
use App\Repository\Caisse\CaisseRepository;
use App\Repository\CashMovement\CashMovementRepository;
use Illuminate\Routing\Controller as BaseController;
use App\Repository\Distributor\DistributorRepository;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Repository\OrderDolibarr\OrderDolibarrRepository;
use App\Repository\Terminal\TerminalRepository;
use App\Repository\Notification\NotificationstocksRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Admin extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $api;
    private $category;
    private $user;
    private $role;
    private $history;
    private $products;
    private $distributors;
    private $printer;
    private $colissimoConfiguration;
    private $order;
    private $woocommerce;
    private $factorder;
    private $labelMissing;
    private $orderDolibarr;
    private $transferorder;
    private $facture;
    private $logError;
    private $terminal;
    private $cashMovement;
    private $caisse;
    private $transfers;
    private $construcstocks;
    private $stocks;
    private $pdf;

    public function __construct(
        Api $api, 
        CategoriesRepository $category,
        UserRepository $user, 
        RoleRepository $role,
        HistoryRepository $history,
        ProductRepository $products,
        DistributorRepository $distributors,
        PrinterRepository $printer,
        ColissimoRepository $colissimoConfiguration,
        OrderRepository $order,
        WoocommerceService $woocommerce,
        TransferOrder $factorder,
        Transfertext $transfers,
        InvoicesPdf $pdf,
        LabelMissingRepository $labelMissing,
        OrderDolibarrRepository $orderDolibarr,
        TransferOrder $facture,
        LogErrorRepository $logError,
        TerminalRepository $terminal,
        CashMovementRepository $cashMovement,
        CaisseRepository $caisse,
        NotificationstocksRepository $stocks,
        Construncstocks $construcstocks
    ){
        $this->api = $api;
        $this->category = $category;
        $this->user = $user;
        $this->role = $role;
        $this->history = $history;
        $this->products = $products;
        $this->distributors = $distributors;
        $this->printer = $printer;
        $this->colissimoConfiguration = $colissimoConfiguration;
        $this->order = $order;
        $this->woocommerce = $woocommerce;
        $this->factorder = $factorder;
        $this->transfers = $transfers;
        $this->labelMissing = $labelMissing;
        $this->orderDolibarr = $orderDolibarr;
        $this->facture = $facture;
        $this->logError = $logError;
        $this->terminal = $terminal;
        $this->cashMovement = $cashMovement;
        $this->caisse = $caisse;
        $this->construcstocks = $construcstocks;
        $this->stocks = $stocks;
        $this->pdf=$pdf;
    }

    public function syncCategories(){
        $per_page = 100;
        $page = 1;
        $categories = $this->api->getAllCategories($per_page, $page);
        $count = count($categories);
        $insert_categories = [];

        // Check if others page
        if($count == 100){
          while($count == 100){
            $page = $page + 1;
            $categories_other = $this->api->getAllCategories($per_page, $page);
           
            if(count($categories_other ) > 0){
              $categories = array_merge($categories, $categories_other);
            }
          
            $count = count($categories_other);
          }
        }  

        foreach($categories as $category){
            $insert_categories [] = [
                'name' => $category['name'],
                'category_id_woocommerce' => $category['id'],
                'parent_category_id' => $category['parent'],

            ];
        }

        $sync = $this->category->insertCategoriesOrUpdate($insert_categories);

        if($sync){
            return redirect()->route('admin.categories')->with('success', 'Catégories synchronisées avec succès !');
        } else {
            return redirect()->route('admin.categories')->with('error', $sync);
        }
    }

    public function syncProducts(){
        $per_page = 100;
        $page = 1;
        $products = $this->api->getAllProducts($per_page, $page);
        $count = count($products);
        $insert_products = [];

        // Check if others page
        if($count == 100){
          while($count == 100){
            $page = $page + 1;
            $products_other = $this->api->getAllProducts($per_page, $page);
           
            if(count($products_other ) > 0){
              $products = array_merge($products, $products_other);
            }
            $count = count($products_other);
          }
        }  

        foreach($products as $product){

            dd($product);
            if($product['id'] == 31110){
                dd($product);
            }

            $barcode = $this->getValueByKey($product['meta_data'], "barcode");
            $category_name = [];
            $category_id = [];

            foreach($product['categories'] as $cat){
                $category_name[] = $cat['name'];
                $category_id[] = $cat['id'];
            }
            
            $variation = false;
            foreach($product['attributes'] as $attribut){
                if($attribut['variation'] && count($product['variations']) == count($attribut['options'])){
                    $variation = $attribut['name'];
                }
            }

            // Dans le cas ou plus d'options que de variations, on re check
            if(!$variation){
                foreach($product['attributes'] as $attribut){
                    if($attribut['variation']){
                        $variation = $attribut['name'];
                    }
                }
            }

          
            if($variation){
                $ids = array_column($product['attributes'], "name");
                $clesRecherchees = array_keys($ids,  $variation);
            }

            if($variation && count($product['variations']) > 0){
                $option = $product['attributes'][$clesRecherchees[0]]['options'];
                $name_variation = false;

                // Insertion du produit de base sans les variations
                $insert_products [] = [
                    'product_woocommerce_id' => $product['id'],
                    'parent_id' => 0,
                    'category' =>  implode(',', $category_name),
                    'category_id' => implode(',', $category_id),
                    'variation' => 0,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'barcode' => str_replace(' ', '',$barcode),
                    'status' => $product['status'],
                    'manage_stock' => $product['manage_stock'],
                    'stock' => $product['stock_quantity'] ?? 0,
                    'is_variable' => 1,
                    'weight' =>  $product['weight'],
                    'menu_order' => $product['menu_order'],
                    'image' => isset($product['images'][0]['src']) ? $product['images'][0]['src'] : null,
                    'ref' => isset($product['sku']) ? $product['sku'] : null,
                    'is_virtual' => isset($product['virtual']) ? ($product['virtual'] ? 1 : 0) : 0
                ];

                foreach($option as $key => $op){
                    if(isset($product['variations'][$key])){
                        if(isset($product['variation_attributes'])){
                            if(count($product['variation_attributes']) > 0){
                                if(isset($product['variation_attributes'][$product['variations'][$key]])){
                                    $first_key = array_key_first($product['variation_attributes'][$product['variations'][$key]]);
                                    $name_variation = $product['variation_attributes'][$product['variations'][$key]][$first_key];
                                }
                            }
                        } 

                        

                        $name = $name_variation ? $product['name'].' - '.$name_variation : $product['name'].' - '.$op;
                        $insert_products [] = [
                            'product_woocommerce_id' => $product['variations'][$key],
                            'parent_id' => $product['id'],
                            'category' =>  implode(',', $category_name),
                            'category_id' => implode(',', $category_id),
                            'variation' => 1,
                            'name' => $name,
                            'price' => $product['variation_prices'][$key],
                            'barcode' => str_replace(' ', '', $product['barcodes_list'][$key]),
                            'status' => $product['status'],
                            'manage_stock' => $product['manage_stock_variation'][$key] == "yes" ? 1 : 0,
                            'stock' => $product['stock_quantity_variation'][$key] ?? 0,
                            'is_variable' => 0,
                            'weight' =>  $product['weights_variation'][$key] != "" ? $product['weights_variation'][$key] : $product['weight'],
                            'menu_order' => $product['menu_order'],
                            'image' => isset($product['images'][0]['src']) ? $product['images'][0]['src'] : null,
                            'ref' => isset($product['sku']) ? $product['sku'] : null,
                            'is_virtual' => isset($product['virtual']) ? ($product['virtual'] ? 1 : 0) : 0
                        ];

                       
                    }
                }
                dd('stop 1');
            } else {
                $insert_products [] = [
                    'product_woocommerce_id' => $product['id'],
                    'parent_id' => 0,
                    'category' =>  implode(',', $category_name),
                    'category_id' => implode(',', $category_id),
                    'variation' => 0,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'barcode' => str_replace(' ', '', $barcode),
                    'status' => $product['status'],
                    'manage_stock' => $product['manage_stock'],
                    'stock' => $product['stock_quantity'] ?? 0,
                    'is_variable' => 0,
                    'weight' =>  $product['weight'],
                    'menu_order' => $product['menu_order'],
                    'image' => isset($product['images'][0]['src']) ? $product['images'][0]['src'] : null,
                    'ref' => isset($product['sku']) ? $product['sku'] : null,
                    'is_virtual' => isset($product['virtual']) ? ($product['virtual'] ? 1 : 0) : 0
                ];
            }
        }

        dd("stop 2");
        $sync = $this->products->insertProductsOrUpdate($insert_products);

        if($sync){
            return redirect()->route('admin.products')->with('success', 'Produits synchronisés avec succès !');
        } else {
            return redirect()->route('admin.products')->with('error', $sync);
        }
    }

    public function updateProduct(Request $request){
        $location = $request->post('location');
        $id_product = $request->post('id_product');

        $data = [
            'location' => $location
        ];

        echo json_encode(['success' => $this->products->updateProduct($id_product, $data)]);
    }

    public function updateProductsMultiple(Request $request){

        $location = $request->post('location');
        $products_id = explode(',', $request->post('products_id'));

        if($this->products->updateMultipleProduct($location, $products_id)){
            return redirect()->route('admin.products')->with('success', 'Produits modifiés avec succès !');
        } else {
            return redirect()->route('admin.products')->with('error', 'Les produits n\'ont pas été modifiés');
        }
    }

    public function updateOrderCategory(Request $request){
        $id = $request->post('id');
        $order_display = $request->post('order_display');
        $parent = $request->post('parent');
        echo json_encode(['success' => $this->category->updateCategoryOrder($id, $order_display, $parent)]);
    }

    public function account(){
        $users = $this->user->getUsersAndRoles($withInactive = true);
        $rolesUser =  Auth()->user()->roles->toArray();
        $ids = array_column($rolesUser, "id");
        $isAdmin = count(array_keys($ids,  1)) > 0 ? true : false;
        $roles = $this->role->getRoles();

        return view('admin.account', ['users' => $users, 'roles' => $roles, 'isAdmin' => $isAdmin]);
    }

    public function analytics(){
        return view('admin.analytics');
    }

    public function getAnalytics(Request $request){
        $date = $request->get('date') ?? date('Y-m-d');
        $histories = $this->history->getHistoryAdmin($date);

        $total_order = 0;
        foreach($histories as $histo){
            if($histo['total_order']){
                $total_order = $histo['status'] == "finished" ?  $total_order + $histo['total_order'] : $total_order;
            } else if($histo['total_order_ttc']){
                $total_order = $histo['status'] == "finished" ?  $total_order + $histo['total_order_ttc'] : $total_order;
            }
        }

        $list_histories = [];
        try{
            $list_histories = $this->buildHistory($histories);
            echo json_encode(['success' => true, 'histories' => $list_histories, 'total_order' => $total_order /*, 'average_by_name' => $average_by_name*/]);
        } catch(Exception $e){
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getAverage(){
        try{
            $histories = $this->history->getAllHistoryAdmin();
            $list_histories = $this->buildHistory($histories);

            // Calcule de la moyenne de commande préparées & emballées et des produits bippés par jour pour chaque préparateur & emballeurs
            $average = [];
            foreach($list_histories as $list){
                foreach($list as $l){
                $average[$l['name']][$l['date']] = ['finished_count' => $l['finished_count'], 'prepared_count' => $l['prepared_count'] , 'items_picked' => $l['items_picked']];
                }
            }

            $average_by_name = [];
            $number_prepared = 0;
            $number_finished = 0;
            $number_items_picked = 0;

            foreach($average as $key => $avg){
                foreach($avg as $av){
                    $number_prepared = $number_prepared + $av['prepared_count'];
                    $number_finished = $number_finished + $av['finished_count'];
                    $number_items_picked = $number_items_picked + $av['items_picked'];
                }

            $average_by_name[$key] = ['name' => $key, 'avg_prepared' => round($number_prepared / count($avg), 2), 'avg_finished' => round($number_finished / count($avg), 2), 'avg_items_picked' => round($number_items_picked / count($avg), 2)];
                $number_prepared = 0;
                $number_finished = 0;
                $number_items_picked = 0;
            }

            // Trie par commandes préparées
            usort($average_by_name, function($a, $b) {
                return $b['avg_prepared'] <=> $a['avg_prepared'];
            });

            echo json_encode(['success' => true, 'average_by_name' => $average_by_name]);
        } catch (Exception $e){
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function roles(){
        $roles = $this->role->getRoles();
        $role_can_not_delete = [1,2,3,4,5,6];
        return view('admin.roles', ['roles' => $roles, 'role_can_not_delete' =>  $role_can_not_delete]);
    }

    public function createRole(Request $request){
        $role = $request->post('role');
        $color = $request->post('color');

        $data = [
            'role' => $role,
            'color' => $color
        ];
        
        if($this->role->createRole($data)){
            return redirect()->route('roles')->with('success', 'Rôle créé avec succès !');
        } else {
            return redirect()->route('roles')->with('error', 'Le rôle n\'a pas pu être créé');
        }
    }

    public function updateRole(Request $request){
        $update_role = $request->post('update_role');
        $update_color = $request->post('update_color');
        $role_id = $request->post('role_id');
        $data = [
            'role' => $update_role,
            'color' => $update_color,
            'role_id' => $role_id
        ];

        if($this->role->updateRole($data)){
            return redirect()->route('roles')->with('success', 'Rôle modifié avec succès !');
        } else {
            return redirect()->route('roles')->with('error', 'Le rôle n\'a pas pu être modifié');
        }
    }

    public function deleteRole(Request $request){
        $role_can_not_delete = [1,2,3,4,5,6];
        $role_id = $request->post('role_id');

        if(in_array($role_id, $role_can_not_delete)){
            return redirect()->route('roles')->with('error', 'Le rôle ne peut pas être supprimé !');
        } else {
            
            $role_attr = $this->user->getUsersByRole([$role_id]);

            if(count($role_attr) > 0){
                return redirect()->route('roles')->with('error', 'Impossible de supprimer un rôle déjà attribué !');
            } else {
                if($this->role->deleteRole($role_id)){
                    return redirect()->route('roles')->with('success', 'Rôle supprimé avec succès !');
                } else {
                    return redirect()->route('roles')->with('error', 'Le rôle n\'a pas pu être supprimé');
                }
            }
        }
    }

    public function distributors(){
        $distributors = $this->distributors->getDistributors();
        return view('admin.distributors', ['distributors' => $distributors]);
    }

    // Fonction pour récupérer la valeur avec une clé spécifique
    private function getValueByKey($array, $key) {
        foreach ($array as $item) {
            if ($item['key'] === $key) {
                return $item['value'];
            }
        }
        return null; // Si la clé n'est pas trouvée
    }

    public function emailPreview(){
        return view('email.email-preview');
    }

    public function printers(){
        $preparateurs = $this->user->getUsersAndRoles();
        $printers = $this->printer->getPrinters();
        return view('admin.printers', ['printers' => $printers, 'preparateurs' => $preparateurs]);
    }

    public function addPrinter(Request $request){
        $name = $request->post('name');
        $address_ip  = $request->post('address_ip');
        $port  = $request->post('port') ?? 9100;
        $user_id = $request->post('user_id');

        $data = [
            'name' => $name,
            'address_ip' => $address_ip,
            'port' => $port,
            'user_id' => $user_id
        ];

        $printers = $this->printer->getPrinters();
        foreach($printers as $printer){
            if($printer->user_id == $user_id){
                return redirect()->route('printers')->with('error', 'Ce préparateur possède déjà une imprimante');
            }
        }

        try{
            if($this->printer->addPrinter($data)){
                return redirect()->route('printers')->with('success', 'Imprimante ajoutée avec succès !');
            } else {
                return redirect()->route('printers')->with('error', 'L\'imprimante n\'a pas pu être ajoutée');
            }
        } catch(Exception $e){
            if(str_contains($e->getMessage(), 'Duplicate ')){
                return redirect()->route('printers')->with('error', 'L\'adresse IP de l\'imprimante doit être unique !');
            } else {
                return redirect()->route('printers')->with('error', $e->getMessage());
            }
        }
       
    }

    public function updatePrinter(Request $request){
        $data = [];
        $update_name = $request->post('update_name');
        $update_address_ip  = $request->post('update_address_ip');
        $update_port  = $request->post('update_port') ?? 9100;
        $update_user_id = $request->post('update_user_id');
        $printer_id = $request->post('printer_id');
        
        if ($update_name !== null) {
            $data['name'] = $update_name;
        }
        
        if ($update_address_ip !== null) {
            $data['address_ip'] = $update_address_ip;
        }
        
        if ($update_port !== null) {
            $data['port'] = $update_port;
        }
        
        if ($update_user_id !== null) {
            $data['user_id'] = $update_user_id;
        }

        try{
            $this->printer->updatePrinterAttributionByUser($update_user_id, null); 
            if($this->printer->updatePrinter($data, $printer_id)){
                return redirect()->route('printers')->with('success', 'Imprimante modifié avec succès !');
            } else {
                return redirect()->route('printers')->with('error', 'L\'imprimante n\'a pas pu être modifié');
            }
            
        } catch(Exception $e){
            if(str_contains($e->getMessage(), 'Duplicate')){
                return redirect()->route('printers')->with('error', 'L\'adresse IP de l\'imprimante doit être unique !');
            } else {
                return redirect()->route('printers')->with('error', $e->getMessage());
            }
        }
    }

    public function deletePrinter(Request $request){
        $printer_id = $request->post('printer_id');

        if($this->printer->deletePrinter($printer_id)){
            return redirect()->route('printers')->with('success', 'Imprimante supprimée avec succès !');
        } else {
            return redirect()->route('printers')->with('error', 'L\'imprimante n\'a pas pu être supprimée');
        }
    }


    public function colissimo(){
        $colissimo = $this->colissimoConfiguration->getConfiguration();
        $list_format_colissimo = [
            'PDF_A4_300dpi' => 'Impression bureautique en PDF, de dimension A4 et de résolution 300dpi',
            'PDF_10x15_300dpi' => 'Impression bureautique en PDF, de dimension 10cm par 15cm, et de résolution 300dpi',
            'ZPL_10x15_203dpi' => 'Impression thermique en ZPL, de dimension 10cm par 15cm, et de résolution 203dpi',
            'ZPL_10x15_300dpi' => 'Impression thermique en ZPL, de dimension 10cm par 15cm, et de résolution 300dpi'
        ];

        $list_format_chronopost = [
            'PDF' => 'LT avec preuve de dépôt destinée à être imprimée sur une imprimante papier, format A4',
            'ZPL' => 'LT au format 11x15 pour impression sur imprimante thermique compatible ZPL (sans preuve de dépôt)',
            'ZPL_300' => 'LT au format ZPL et destinée à être imprimée sur une imprimante thermique 300dp',
        ];

        return view('admin.colissimo', ['list_format_colissimo' => $list_format_colissimo, 'list_format_chronopost' => $list_format_chronopost, 
        'colissimo' => count($colissimo) > 0 ? $colissimo[0] : null]);
    }

    public function updateColissimo(Request $request){
        $format_colissimo = $request->post('format_colissimo');
        $format_chronopost = $request->post('format_chronopost');

        $address_ip = $request->post('address_ip');
        $port = $request->post('port');

        $data = [
            'format_colissimo' => $format_colissimo,
            'format_chronopost' => $format_chronopost,
            'address_ip' => $address_ip,
            'port' => $port,
        ];

        try{
            if($this->colissimoConfiguration->save($data)){
                return redirect()->route('colissimo')->with('success', 'Modifications enregistrées !');
            } else {
                return redirect()->route('colissimo')->with('error', 'Les modifications n\'ont aps été prise en compte !');
            }
        } catch(Exception $e){
            return redirect()->route('colissimo')->with('error', $e->getMessage());
        }
    }

    public function reinvoice(){
        return view('admin.reinvoice');
    }

    public function reInvoiceOrder(Request $request){
        $orders_id = $request->post('order_id');
        $orders = [];

        foreach($orders_id as $order_id){
            $order =  $this->order->getOrderByIdWithCustomer($order_id);
            if($order && count($order) > 0){
                $orders[] = $this->woocommerce->transformArrayOrder($order)[0];
            }
        }

        if(count($orders) == 0){
            return redirect()->route('admin.reinvoice')->with('error', 'Commande inexistante !');
        } else{
            //dd($orders);
            $this->facture->Updatefacture($orders);
        }
   
    }

    public function billing(){
        return view('admin.billing');
    }

    public function billingOrder(Request $request){

    
          
        $order_id = $request->post('order_id');
        $order = $this->order->getOrderByIdWithCustomer($order_id);

        if($order_id == ""){
            return redirect()->route('admin.billing')->with('error', 'Veuillez renseigner un numéro de commande');
        } else {

            if(str_contains($order_id, 'CO') || str_contains($order_id, 'BP')){
                $order = $this->orderDolibarr->getOrdersDolibarrById($order_id)->toArray();
                if(count($order) > 0){
                    $order = $this->woocommerce->transformArrayOrderDolibarr($order);
                    $order[0]['emballeur'] = "Admin";
                } else {
                    return redirect()->route('admin.billing')->with('error', 'Commande inexistante !');
                }
            } else {
                if($order){
                    $order = $this->woocommerce->transformArrayOrder($order);
                    $order[0]['emballeur'] = "Admin";
                } else {
                    // Récupère directement sur Woocommerce si pas en local et l'attribue à l'admin qui à l'id 1
                    $order[1][0] = $this->api->getOrdersWoocommerceByOrderId($order_id);
                    if(isset($order[1][0]['code'])){
                        return redirect()->route('admin.billing')->with('error', 'Commande inexistante en local et sur Woocommerce !');
                    } else {
                        // Insert la commande
                        $insert = $this->order->insertOrdersByUsers($order);
                        $order_insert = $this->order->getOrderByIdWithCustomer($order_id);
                        $order_insert[0]['emballeur'] = "Admin";
                        $order = $this->woocommerce->transformArrayOrder($order_insert);
                    }
                }
            }

            try {

                $this->transfers->Transfertext($order);
                //$this->factorder->Transferorder($order);  

                // Stock historique
                $data = [
                    'order_id' => $order_id,
                    'user_id' => Auth()->user()->id,
                    'status' => 'finished',
                    'poste' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'total_product' => isset($order[0]['total_product']) ? $order[0]['total_product'] : null
                ];
          
                $this->history->save($data);
                // Modifie le status de la commande sur Woocommerce en "Prêt à expédier"
                $this->order->updateOrdersById([$order_id], "finished");
               
                $status_finished = "lpc_ready_to_ship";
                if(isset($order[0]['shipping_method'])){
                    if(str_contains($order[0]['shipping_method'], 'chrono')){
                        $status_finished = "chronopost-pret";
                    }
                }
                $this->api->updateOrdersWoocommerce($status_finished, $order_id);

                return redirect()->route('admin.billing')->with('success', 'Commande facturée avec succès !');
            } catch(Exception $e){
                return redirect()->route('admin.billing')->with('error', $e->getMessage());
            }
        }
    }

    private function buildHistory($histories){
        $list_histories = [];
        // Historique des commandes préparées, emballées et des produits bippés pour chaque préparateur & emballeurs
        foreach($histories as $histo){
            $id = date('d/m/Y', strtotime($histo['created_at']));
            if(!isset($list_histories[$id][$histo['id']])){
                $list_histories[date('d/m/Y', strtotime($histo['created_at']))][$histo['id']] = [
                    'name' => $histo['name'],
                    'poste' => isset($histo['poste']) ? [$histo['poste']] : null,
                    'prepared_order' => $histo['status'] == "prepared" ? [$histo['order_id']] : [],
                    'finished_order' => $histo['status'] == "finished" ? [$histo['order_id']] : [],
                    'prepared_count' => $histo['status'] == "prepared" ? 1 : 0,
                    'finished_count' => $histo['status'] == "finished" ? 1 : 0,
                    'items_picked' => $histo['status'] == "prepared" ? $histo['total_product'] : 0,
                    'items_packed' => $histo['status'] == "finished" ? $histo['total_product'] : 0,
                    'date' => date('d/m/Y', strtotime($histo['created_at'])),
                ];
            } else {
                $histo['status'] == "prepared" ? array_push($list_histories[$id][$histo['id']]['prepared_order'],$histo['order_id']) : array_push($list_histories[$id][$histo['id']]['finished_order'],$histo['order_id']);
                $list_histories[$id][$histo['id']]['poste'][] = $histo['poste'] ?? null;
                $list_histories[$id][$histo['id']]['prepared_order'] = array_unique($list_histories[$id][$histo['id']]['prepared_order']);
                $list_histories[$id][$histo['id']]['finished_order'] = array_unique($list_histories[$id][$histo['id']]['finished_order']);
                $list_histories[$id][$histo['id']]['poste'] = array_unique($list_histories[$id][$histo['id']]['poste']);
                $list_histories[$id][$histo['id']]['prepared_count'] = count($list_histories[$id][$histo['id']]['prepared_order']);
                $list_histories[$id][$histo['id']]['finished_count'] = count($list_histories[$id][$histo['id']]['finished_order']);
                $histo['status'] == "prepared" ? $list_histories[$id][$histo['id']]['items_picked'] = $list_histories[$id][$histo['id']]['items_picked'] + $histo['total_product'] : 0;
                $histo['status'] == "finished" ? $list_histories[$id][$histo['id']]['items_packed'] = $list_histories[$id][$histo['id']]['items_packed'] + $histo['total_product'] : 0;
            }
        }

        return $list_histories;
    }

    public function missingLabels(){
        // Get list of lables with status ok
        $labelMissingStatusArray = [];
        $labelMissingStatus = $this->labelMissing->getAllLabelsMissingStatusValid();
        foreach($labelMissingStatus as $l){
            $labelMissingStatusArray[$l->order_id] = true;
        }

        $missingLabels = $this->order->getOrdersWithoutLabels();
        $orders = [];
        $orders_with_date = [];

        if(count($missingLabels) > 0){
            foreach($missingLabels as $order){
                $orders[] = $order->order_woocommerce_id;
                $orders_with_date[$order->order_woocommerce_id] = date('d/m/Y', strtotime($order->date));
            }
    
            $checkWoocommerceOrders= [];
            $checkWoocommerce = $this->api->getLabelsfromOrder($orders); 
    
            if($checkWoocommerce){
                foreach($checkWoocommerce as $check){
                    $checkWoocommerceOrders[] = intval($check['order_id']);
                }
        
                $missingLabels = array_diff($orders, $checkWoocommerceOrders);
                return view('admin.missing_labels', ['missingLabels' => $missingLabels, 'orders_with_date' => $orders_with_date, 'labelMissingStatusArray' => $labelMissingStatusArray]);
            } else {
                return redirect()->route('labels')->with('error', 'Problème base de données woocommerce');        
            }
        } else {
            return view('admin.missing_labels', ['missingLabels' => $missingLabels, 'orders_with_date' => [], 'labelMissingStatusArray' => []]);
        }
       
       
    }

    public function validLabelMissing(Request $request){
        $order_id = $request->post('order_id');
        echo json_encode(['success' => $this->labelMissing->insert(1, $order_id)]);
    }

    public function cancelLabelMissing(Request $request){
        $order_id = $request->post('order_id');
        echo json_encode(['success' => $this->labelMissing->delete($order_id)]);
    }

    public function configDolibarr(){

        $datas = array();

        $dateLast_date_categorie_dolibarr = Categorie_dolibarr::first()->updated_at;
        $dateLast_date_products_categories = products_categories::first()->updated_at;
        $dateLast_date_products_association = Products_association::first()->updated_at;
        $dateLast_date_product_dolibarr = ProductDolibarr::first()->updated_at;
        


        array_push($datas,
            [
                "name_table" => "Categorie_dolibarr",
                "last_update" => $dateLast_date_categorie_dolibarr ? $dateLast_date_categorie_dolibarr->format('d/m/Y à H:i:s'): "Jamais",
                "route" => route("updatePrepaCategoriesDolibarr")
            ],
            [
                "name_table" => "products_categories",
                "last_update" => $dateLast_date_products_categories ? $dateLast_date_products_categories->format('d/m/Y à H:i:s'): "Jamais",
                "route" => route("updatePrepaProductsCategories")
            ],
            [
                "name_table" => "products_association",
                "last_update" => $dateLast_date_products_association ? $dateLast_date_products_association->format('d/m/Y à H:i:s'): "Jamais",
                "route" => route("updatePrepaProductsAssociation")
            ],
            [
                "name_table" => "product_dolibarr",
                "last_update" => $dateLast_date_product_dolibarr ? $dateLast_date_product_dolibarr->format('d/m/Y à H:i:s'): "Jamais",
                "route" => route("updatePrepaProductsDolibarr")
            ]
        );


        return view('admin.configDolibarr',
        [
           "datas" => $datas
        ]);

    }

    public function updatePrepaCategoriesDolibarr(){
   
        
        try {
            $pdoDolibarr = new PdoDolibarr(env('HOST_ELYAMAJE'),env('DBNAME_DOLIBARR'),env('USER_DOLIBARR'),env('PW_DOLIBARR'));

            //Une érreur s'est produite => SQLSTATE[HY000] [1045] Access denied for user 'admineu13'@'82.96.133.166' (using password: YES)



            $tab_categories = $pdoDolibarr->getCategoriesDolibarr();
           
            DB::beginTransaction();
            DB::table('categories_dolibarr')->truncate();
            DB::table('categories_dolibarr')->insert($tab_categories);

            // Si tout se passe bien, commit la transaction
            DB::commit();

            return redirect()->back()->with('success', 'Table mise à jour');
        } catch (\Throwable $th) {
            // En cas d'erreur, annuler la transaction
            DB::rollBack();
            return redirect()->back()->with('error',  "Une érreur s'est produite => ".$th->getMessage());
        }
    }

    public function updatePrepaProductsCategories(){
        
        try {
            $pdoDolibarr = new PdoDolibarr(env('HOST_ELYAMAJE'),env('DBNAME_DOLIBARR'),env('USER_DOLIBARR'),env('PW_DOLIBARR'));
            $products_categories = $pdoDolibarr->getCategories();
           
            DB::beginTransaction();
            DB::table('products_categories')->truncate();
            DB::table('products_categories')->insert($products_categories);

            // Si tout se passe bien, commit la transaction
            DB::commit();

            return redirect()->back()->with('success', 'Table mise à jour');
        } catch (\Throwable $th) {
            // En cas d'erreur, annuler la transaction
            DB::rollBack();
            return redirect()->back()->with('error',  "Une érreur s'est produite => ".$th->getMessage());
        }
    }

    public function updatePrepaProductsAssociation(){
        
        try {
            $pdoDolibarr = new PdoDolibarr(env('HOST_ELYAMAJE'),env('DBNAME_DOLIBARR'),env('USER_DOLIBARR'),env('PW_DOLIBARR'));
            $products_associations = $pdoDolibarr->getProductsAssociations();
           
            DB::beginTransaction();
            DB::table('products_association')->truncate();
            DB::table('products_association')->insert($products_associations);

            // Si tout se passe bien, commit la transaction
            DB::commit();
            return redirect()->back()->with('success', 'Table mise à jour');
        } catch (\Throwable $th) {

            // En cas d'erreur, annuler la transaction
            DB::rollBack();
            return redirect()->back()->with('error',  "Une érreur s'est produite => ".$th->getMessage());
        }
    }

    public function updatePrepaProductsDolibarr(){   



        try {

       

            $products_dolibarrs_save = array();

            // $apiUrl = env('KEY_API_URL');
            $apiKey = env('KEY_API_DOLIBAR');

            $apiUrl = config('app.dolibarr_api_url');


            $produitParamProduct = array(
                'apikey' => $apiKey,
                'limit' => 10000,
            );


            $all_products = $this->api->CallAPI("GET", $apiKey, $apiUrl."products",$produitParamProduct);  
            $all_products = json_decode($all_products,true);


            // foreach ($all_products as $key => $value) {
            //     if ($value["id"] == 6597) {
            //         dd($value);
            //     }
            // }

            // dd("dddddddddddddd");


            if ($all_products) {
                
                foreach ($all_products as $key => $product) {

               

                //   if ($product["status"] == 1) {
                    $qte = 0;

                    if ($product["warehouse_array_list"]) {
                        // if ($product["id"] == 4981) {
                        //     dd($product["warehouse_array_list"][$product["id"]]);
                        // }
                        foreach ($product["warehouse_array_list"][$product["id"]] as $key => $value) {
                            if ($value["warehouse"] == "Entrepot Malpasse") {
                                if ($qte == 0) {
                                    $qte = $value["stock"];
                                }
                            }
                        }
                    }

                    

                    array_push($products_dolibarrs_save, [
                        "product_id" => $product["id"],
                        "label" => $product["label"],
                        "price_ttc" =>$product["price"]? ($product["price"]*(($product["tva_tx"]*0.01)+1)):$product["price_ttc"],
                        "barcode" => $product["barcode"],
                        "poids" => 0,
                        "warehouse_array_list" => $qte
                    ]);
                //   }

                   

                }

                // foreach ($products_dolibarrs_save as $key => $value) {
                //     if ($value["product_id"] == 6597) {
                //             dd($value);
                //         }
                //     }

                // dd("products_dolibarrs_save");
    
                DB::beginTransaction();
                DB::table('products_dolibarr')->truncate();
                DB::table('products_dolibarr')->insert($products_dolibarrs_save);

                // Si tout se passe bien, commit la transaction
                DB::commit();
                return redirect()->back()->with('success', 'Table mise à jour');
                
            }


        } catch (\Throwable $th) {
            return redirect()->back()->with('error',  "Une érreur s'est produite => ".$th->getMessage());
        }
    }

    public function errorLogs(){
        $logs = $this->logError->getAllLogs();
        return view('admin.logs', ['logs' => $logs]);
    }


    /* ------------ Beauty Prof details ------------ */
    public function seller(Request $request){
        return view('admin.seller');
    }

    // Details orders 
    public function analyticsSeller(Request $request){
        $date = $request->get('date') ?? date('Y-m-d');
        $ordersBeautyProf = $this->orderDolibarr->getOrdersBeautyProf($date);
        $list_histories = [];
        try{
            $list_histories = $this->buildHistoryBeautyProf($ordersBeautyProf);
            echo json_encode(['success' => true, 'histories' => $list_histories['details'], 'status' => $list_histories['status'], 
            'total_amount_order' => $list_histories['total_amount_order']]);
        } catch(Exception $e){
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function analyticsSellerTotal(){
        try{
            $histories = $this->orderDolibarr->getAllOrdersBeautyProf();
            $status_to_exclude = ['canceled', 'pending'];
            $order_by_name = [];
   
            foreach($histories as $histo){
                if(!isset($order_by_name[$histo['id']])){
                    $order_by_name[$histo['id']] = [
                        'name' => $histo['name'],
                        'total_order' => 1,
                        'total_amount' => !in_array($histo['status'], $status_to_exclude) ? $histo['total_order_ttc'] : 0,
                    ];
                } else {
                    $order_by_name[$histo['id']]['total_order']++;
                    !in_array($histo['status'], $status_to_exclude) ? $order_by_name[$histo['id']]['total_amount'] += $histo['total_order_ttc'] 
                    : $order_by_name[$histo['id']]['total_amount'] += 0;
                }
            }

            // Trie par commandes préparées
            usort($order_by_name, function($a, $b) {
                return $b['total_order'] <=> $a['total_order'];
            });
            

            echo json_encode(['success' => true, 'average' => $order_by_name]);
        } catch (Exception $e){
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function buildHistoryBeautyProf($histories){
        $list_histories['details'] = [];
        $status_to_exclude = ['canceled', 'pending'];
        $total_amount_order = 0;

        // Status of orders
        $pending = 0;
        $paid = 0;
        $canceled = 0;

        // Historique des commandes préparées, emballées et des produits bippés pour chaque préparateur & emballeurs
        foreach($histories as $histo){
            $id = date('d/m/Y', strtotime($histo['created_at']));

            $pending =  $histo['status'] == "pending" ? $pending + 1 : $pending;
            $paid    =  $histo['status'] != "pending" && $histo['status'] != "canceled" ? $paid + 1 : $paid;
            $canceled  =   $histo['status'] == "canceled" ? $canceled + 1 : $canceled;

            
            if(!isset($list_histories['details'][$id][$histo['id']])){
                $total_amount_order =  !in_array($histo['status'], $status_to_exclude) ? $total_amount_order + $histo['total_order_ttc'] : $total_amount_order;
                $list_histories['details'][date('d/m/Y', strtotime($histo['created_at']))][$histo['id']] = [
                    'name' => $histo['name'],
                    'number_order' => 1,
                    'date' => date('d/m/Y', strtotime($histo['created_at'])),
                    'total_amount' => $histo['total_order_ttc']
                ];
            } else {
                $list_histories['details'][$id][$histo['id']]['number_order']++;
                $list_histories['details'][$id][$histo['id']]['total_amount'] += $histo['total_order_ttc'];
                !in_array($histo['status'], $status_to_exclude) ? $total_amount_order += $histo['total_order_ttc'] : $total_amount_order = $total_amount_order;
            }
        }

        // Calcule moyenne du panier
        foreach($list_histories['details'] as $key => $data) {
            foreach($data as $key2 => $dt) {
                $list_histories['details'][$key][$key2]['average'] =  floatval($dt['total_amount'] / $dt['number_order']);
            }
        }

        $list_histories['status'] = 
        [
            'pending' => $pending,
            'paid'    => $paid,
            'canceled' => $canceled
        ];

        $list_histories['total_amount_order'] = $total_amount_order;
        return $list_histories;
    }

    public function cashierWaiting(Request $request){
        $ref_order = $request->get('ref_order') ?? false;
        $date = $request->get('created_at') ?? false;
        $orders_status = ['pending', 'processing', 'canceled'];
        $orders = $this->orderDolibarr->getAllOrdersPendingBeautyProf($ref_order, $date);

        // Check if order date time is older than 2 hours
        foreach($orders as $ord => $or){
            $date1 = new DateTime(date('Y-m-d H:i:s'));
            $date2 = new DateTime($or['created_at']);
            $diff = $date1->diff($date2);

            if($diff->h >= 2 || $diff->y > 0 || $diff->m > 0 || $diff->d > 0){
                $orders[$ord]['need_action'] = true;
            } else {
                $orders[$ord]['need_action'] = false;
            }
        }

        foreach(__('status') as $key => $st){
            if(in_array($key, $orders_status)){
                $new_orders_status[$key] = $st;
            }
        }

        return view('admin.cashierWaiting', ['orders' => $orders, 'list_status' => $new_orders_status, 'parameter' => $request->all()]);
    }

    public function caisse(){
        $caisses = $this->caisse->getCaisse();
        return view('admin.caisse', ['caisses' => $caisses]);
    }

    public function addCaisse(Request $request){
        $request->validate([
            'name' => 'required',
            'uniqueId'  => 'required',
        ]);

        $data = [
            'name' => $request->post('name'),
            'uniqueId' => $request->post('uniqueId'),
        ];

        try{
            $this->caisse->insert($data);
            return redirect()->back()->with('success', 'Caisse ajoutée avec succès !');
        } catch (Exception $e){
            return redirect()->back()->with('error', str_contains($e->getMessage(), 'Duplicate') ? 'Cette caisse existe déjà' : 'Oops, une erreur est survenue !');
        }
    }

    public function updateCaisse(Request $request){
        $request->validate([
            'update_name' => 'required',
            'update_uniqueId' => 'required',
        ]);

        $data = [
            'name' => $request->post('update_name'),
            'uniqueId' => $request->post('update_uniqueId'),
        ];

        try{
            $this->caisse->update($request->post('caisse_id'), $data);
            return redirect()->back()->with('success', 'Caisse modifiée avec succès !');
        } catch (Exception $e){
            return redirect()->back()->with('error', str_contains($e->getMessage(), 'Duplicate') ? 'Cette caisse existe déjà' : 'Oops, une erreur est survenue !');
        }
    }

    public function deleteCaisse(Request $request){

        $request->validate([
            'caisse_id' => 'required'
        ]);

        try{
            $this->caisse->delete($request->post('caisse_id'));
            return redirect()->back()->with('success', 'Caisse supprimé avec succès !');
        } catch (Exception $e){
            return redirect()->back()->with('error', 'Oops, une erreur est survenue !');
        }
    }

    public function cashier(Request $request){
        $date = $request->get('date') ?? date('Y-m-d');
        $dateRange = date("Y-m-d", strtotime($date . " +1 day")); // Ajoute un jour à la date actuelle

        // List orders for each caisse
        $detailsCaisse = $this->caisse->getAllDetailsUniqueId($date);
        $caisse = [];

        // List movements for each caisse
        $movements = $this->cashMovement->getMovements($date);
        $list_movements = [];
        $ammount_to_deduct = [];
        $ammount_to_add = [];

        // Sum des montant transféré pour chaque caisse
        foreach($movements as $movement){
            // Amount to deduct
            if($movement->type == "withdrawal"){
                if(!isset($ammount_to_deduct[$movement->caisse])){
                    $ammount_to_deduct[$movement->caisse] = $movement->status == 1 ? floatval($movement->amount) : 0;
                } else {
                    $ammount_to_deduct[$movement->caisse] += $movement->status == 1 ? floatval($movement->amount): 0;
                }
            }

             // Amount to add
            if($movement->type == "deposit"){
                if(!isset($ammount_to_add[$movement->caisse])){
                    $ammount_to_add[$movement->caisse] = $movement->status == 1 ? floatval($movement->amount) : 0;
                } else {
                    $ammount_to_add[$movement->caisse] += $movement->status == 1 ? floatval($movement->amount): 0;
                }
            }
          
            
            // Details movement
            $list_movements[$movement->caisse][] = [
                'name' => $movement->name,
                'date' => $movement->created_at ? $movement->created_at->format('H:i') : 'Inconnu',
                'before_movement' => $movement->before_movement,
                'status' => $movement->status,
                'amount' => floatval($movement->amount),
                'caisse' => $movement->deviceName,
                'movementId' => $movement->id,
                'type' =>  $movement->type,
                'comment' => $movement->comment
            ];
        }

        foreach ($detailsCaisse as $detail) {
            
            // Si caisseId n'existe pas encore dans $caisse, on initialise avec un tableau vide
            if (!isset($caisse[$detail->caisseId])) {
                $caisse[$detail->caisseId] = [
                    'name' => $detail->caisseName,
                    'total_card' => 0,
                    'total_cash' => 0,
                    'cash' => 0,
                    'details' => [],
                    'details_orders' => []
                ];
            }

            // Check if date is same than choice date
            if($detail->date >= $date && $detail->date < $dateRange && ($detail->statut != "canceled" && $detail->statut != "pending")){
                // Mise à jour des totaux card et cash pour cette commande
                $caisse[$detail->caisseId]['total_card'] += $detail->amountCard != null ? $detail->amountCard : 0;
                $caisse[$detail->caisseId]['total_cash'] += $detail->amountCard != null ? $detail->total_order_ttc - $detail->amountCard : $detail->total_order_ttc;

                if($detail->ref_order){
                    $caisse[$detail->caisseId]['details_orders'][] = $detail;
                }

                // Ajouter les détails de l'utilisateur à $caisse[$order->caisseId]['details']
                $userName = $detail->cashierName;
                if($userName){
                    if (!isset($caisse[$detail->caisseId]['details'][$userName])) {
                        $caisse[$detail->caisseId]['details'][$userName] = [
                            'card' => 0,
                            'cash' => 0,
                        ];
                    }
        
                    // Mise à jour des détails pour cet utilisateur
                    $caisse[$detail->caisseId]['details'][$userName]['card'] += $detail->amountCard;
                    $caisse[$detail->caisseId]['details'][$userName]['cash'] += $detail->total_order_ttc - $detail->amountCard;
                }
            }
           
        }

        return view('admin.cashier', ['caisse' => $caisse, 'ammount_to_deduct' => $ammount_to_deduct, 
        'ammount_to_add' => $ammount_to_add, 'list_movements' => $list_movements, 'date' => $date]);
    }

    public function cashMovement(Request $request){

        $caisse = $request->post('caisse');

        // Check if cash movement already in processing for this caisse
        $movement_processing = count($this->cashMovement->getMovementsByCaisse($caisse)->toArray());
        if($movement_processing > 0){
            return redirect()->back()->with('error',  "Veuillez d'abord valider ou annuler les mouvements en cours pour cette caisse");
        }


        $amount = str_replace(',', '.', $request->post('amount'));
        $amount = str_replace([" ", ","], "", $amount);

        $amountCaisse = str_replace(',', '.', $request->post('amountCaisse'));
        $amountCaisse = str_replace([" ", ","], "", $amountCaisse);

        if($amount == 0.0){
            return redirect()->back()->with('error',  "Le montant renseigné n'est pas correct !");
        }

        $data = [
            'before_movement' => $amountCaisse,
            'amount' => $amount,
            'caisse' => $caisse,
            'user_id' => Auth()->user()->id,
            'status' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 'withdrawal',
            'comment' => $request->post('comment') ?? null
        ];

        if(floatval($amount) <= 0){
            return redirect()->back()->with('error',  "Le montant à décaisser doit être supérieur à 0 !");
        }

        // Check if amount is not stronger than caisse amount
        if(floatval($amountCaisse) >= floatval($amount)){
            if($this->cashMovement->addMovement($data)){
                return redirect()->back()->with('warning',  "Le montant à décaisser à été mis en attente");
            } else {
                return redirect()->back()->with('error',  "Le montant n'a pas pu être décaissé");
            }
        } else {
            return redirect()->back()->with('error',  "Le montant à décaisser est supérieur au montant de la caisse !");
        }
    }

    public function updateCashMovement(Request $request){
        $data = [
            'status' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $caisse = $request->post('caisse');
        $movementId = $request->post('movement_id');

        if($this->cashMovement->updateMovement($movementId, $data)){
            return redirect()->back()->with('success',  "Le montant à bien été décaissé de la caisse ".$caisse);
        } else {
            return redirect()->back()->with('error',  "Le montant n'a pas été décaissé");
        }
    }

    public function cancelCashMovement(Request $request){

        $caisse = $request->post('caisse');
        $movementId = $request->post('movement_id');

        if($this->cashMovement->deleteMovement($movementId)){
            return redirect()->back()->with('success',  "Le mouvement à bien été annulé pour la caisse ".$caisse);
        } else {
            return redirect()->back()->with('error',  "Le mouvement n'a pas été annulé");
        }
    }

    public function addCashMovement(Request $request) {

        // Check if cash movement already in processing for this caisse
        $movement_processing = count($this->cashMovement->getMovementsByCaisse($request->post('caisse'))->toArray());
        if($movement_processing > 0){
            return redirect()->back()->with('error',  "Veuillez d'abord valider ou annuler les mouvements en cours pour cette caisse");
        }

        $amount = floatval(str_replace(',', '.', $request->post('amount')));
        if($amount == 0.0){
            return redirect()->back()->with('error',  "Le montant renseigné n'est pas correct !");
        }

        $data = [
            'caisse' => $request->post('caisse'),
            'before_movement' => $request->post('amountCaisse'),
            'amount' => $amount,
            'status' => 0,
            'type' => 'deposit',
            'created_at' => date('Y-m-d H:i:s'),
            'user_id' => Auth()->user()->id,
            'comment' => $request->post('comment') ?? null
        ];

        if($this->cashMovement->addMovement($data)){
            return redirect()->back()->with('warning',  "Le montant à ajouter est en attente de validation");
        } else {
            return redirect()->back()->with('error',  "Le montant n'a pas pu être ajouté");
        }
    }

    public function beautyProfHistory(Request $request){
        $orders = $this->orderDolibarr->getAllOrdersBeautyProfHistory($request->all());
        return view('admin.beautyProfHistory', ['orders' => $orders, 'list_status' => __('status'), 'parameter' => $request->all()]);
    }

    public function paymentTerminal(){
        $terminal = $this->terminal->getTerminal();
        return view('admin.paymentTerminal', ['terminals' => $terminal]);
    }

    public function addTerminal(Request $request){
        $request->validate([
            'name' => 'required',
            'ip_adress' => 'required',
            'poiid' => 'required',
            'mac_adress' => 'required',

        ]);

        $data = [
            'name' => $request->post('name'),
            'ip_adress' => $request->post('ip_adress'),
            'poiid' => $request->post('poiid'),
            'mac' => $request->post('mac_adress') ?? null
        ];

        try{
            $this->terminal->insert($data);
            return redirect()->back()->with('success', 'Terminal ajouté avec succès !');
        } catch (Exception $e){
            return redirect()->back()->with('error', str_contains($e->getMessage(), 'Duplicate') ? 'L\'adress IP ou le PoiId existe déjà' : 'Oops, une erreur est survenue !');
        }
    }

    public function updateTerminal(Request $request){
        $request->validate([
            'update_name' => 'required',
            'update_ip_adress' => 'required',
            'update_poiid' => 'required',
            'update_mac_adress' => 'required',
        ]);

        $data = [
            'name' => $request->post('update_name'),
            'ip_adress' => $request->post('update_ip_adress'),
            'poiid' => $request->post('update_poiid'),
            'mac' => $request->post('update_mac_adress') ?? null
        ];

        try{
            $this->terminal->update($request->post('terminal_id'), $data);
            return redirect()->back()->with('success', 'Terminal modifiée avec succès !');
        } catch (Exception $e){
            return redirect()->back()->with('error', str_contains($e->getMessage(), 'Duplicate') ? 'L\'adress IP ou le PoiId existe déjà' : 'Oops, une erreur est survenue !');
        }
    }

    public function deleteTerminal(Request $request){

        $request->validate([
            'terminal_id' => 'required'
        ]);

        try{
            $this->terminal->delete($request->post('terminal_id'));
            return redirect()->back()->with('success', 'Terminal supprimé avec succès !');
        } catch (Exception $e){
            return redirect()->back()->with('error', 'Oops, une erreur est survenue !');
        }
    }

    
    public function stockscat(){
        $data = $this->construcstocks->Constructstocks();
        $message="";
         // recupérer les produit en stocks
         $list_faible_stocks = $this->construcstocks->getStocksproduct();
        // recupérer les lines qu'il faut dans notification
        $list_product = $this->stocks->getAll();
        if(count($list_product)==0){
            $list_product[] =[
                  "libelle"=>"Aucun mouvement de stock"

            ];
         }

         if(count($list_faible_stocks)==0){
            $list_faible_stocks[]=[
              'produit'=>"Aucun mouvement produit"

            ];
         }

        return view('admin.stockscat',['data'=>$data,'message'=>$message,'list_product'=>$list_product,'list_faible_stocks'=>$list_faible_stocks]);
    }

    public function poststock(Request $request){
           
         $data = $this->construcstocks->Constructstocks();
         $data_id = $this->construcstocks->getdata();

         // recupérer les produit en stocks
         $list_faible_stocks = $this->construcstocks->getStocksproduct();
         $array_list1 = array_flip($data_id);//

          $existant = $request->get('qts');
          //dd($existant);
           $donnees = $request->get('qte');
           //dump($donnees);
          // construire des jeu de données.
           $datac =[];
           $datas =[];
           $regroup_data =[];
          foreach($donnees as $ky =>$val){
           if($val==""){
                $index="no";
           }else{
                $index=$val;
           }
             $datac[$index.','.$ky] = $ky;
             $datas[$val] = $ky;
          }


           // reconstruire le tableau.
            $tab_data_qte =[];
           foreach($existant as $kelc => $vald){
                $index_te =  array_search($kelc,$datac);
                 // recupérer le cas 
                $index_s = explode(',',$index_te);
                // recupérer le nombre finale de mutiplication
                $s = (int)$index_s[0] - (int)$vald;

                if($s>0){
                    $a ="a";
                }
                if($s<0){
                    $a ="b";
                }

                if($s==""){
                    $a="c";
                }

                $tab_data_qte[] = $index_te.','.$s.','.$a;
 
           }
        
            $datacc =array_flip($tab_data_qte);
             $array_list2 = $datacc;
             $array_id_groups =[];// recupérer tous les id des produit a update 
               // faire une list de array.
             if(count($datas)!=1){
              // faire le traitement
                  foreach($array_list2 as $ms => $valus){
                //
                $index_key = explode(',',$ms);
                if($index_key[0]!="no"){
                    // va contruire les données
                    $regroup_data[$ms] =$valus;
                }
               }
             
              //dd($regroup_data);
              // chercher les correspondance souhaite. pour crée des les données 
              $group_data =[];
              $data_array1 =[];
              foreach($regroup_data as $keu=>$val){
                   $chaine_data = array_search($val,$array_list1);
                   if($chaine_data!=false){
                        // recupérer 
                      $chaine_qte_coeff = explode(',',$keu);
                      $index_search = explode('%',$chaine_data);
                      
                      // fabirquer les données ....
                      $line_create = $index_search[0]*$chaine_qte_coeff[2];// modifier ici ancien $chaine_qte_coeff[0]
                      $line_create1 = $index_search[1];
                       // créer un 1 er jeu de tableau. pour actuliser les quantite rentré .
                       $data_array1[$index_search[1]]=$chaine_qte_coeff[0];
                         //  un second jeu de données.
                        $data_array2[] =  [
                          'id_parent'=> $index_search[2],
                          'quantite'=> $line_create
                       ];
                       
                         // recupérer la quantite unite
                         $data_array3[$index_search[2]] = $index_search[3];
                           // recupere les qauntite en line ..
                          $list_qte[] = $line_create;
                           // recupérer les id de produit
                          $array_id_groups[]=$index_search[2];
                   }

                   
              }

              //dump($data_array2);
              
              // caluler sur le second tableau les quantite a decremente
              $result = array_reduce($data_array2,function($carry,$item){
              if(!isset($carry[$item['id_parent']])){ 
                $carry[$item['id_parent']] = ['id_parent'=>$item['id_parent'],'quantite'=>$item['quantite']]; 
               } else { 
                $carry[$item['id_parent']]['quantite'] += $item['quantite']; 
             } 
               return $carry; 
              });

           // dd($result);
             $restriction =[];
             $data_flush =[];// construire le tableau pour update multiple en bdd
             $ids_restriction =[];// recupérer les ids .
             foreach($result as $lg => $valo){
                 $line = $lg.','.$valo['quantite'];
                 $data_flush[$line]= $lg;
             }
              // regouper la somme a deduire 
               // reconstruire le tableau finale pour qte de l'unite
              $line_final_unite =[];
              foreach($data_array3 as $ky=>$valc){
                  
                  $qte_line = array_search($ky,$data_flush);
                  $line_qte = explode(',',$qte_line);

                  // verifier la quantité trouvé
                   $line_final_unite[$ky]=$valc-$line_qte[1];
              }
              
              // construire mes datas requete
              foreach($data_array1 as $ken =>$vn){
                   $data_finale1[] =[
                      ['product_id'=>$ken, 'warehouse_array_list'=>$vn]
                      
                    ];
              }
                // construire les data pour les unite a decremente
               foreach($line_final_unite as $kens =>$vns){
                   $data_finale2[] =[
                      ['product_id'=>$kens, 'warehouse_array_list'=>$vns]
                      
                    ];

                   // recupérer les quantité negative
                  if($vns<0){
                     $restriction[] = $vns;
                     $ids_restriction[] =$kens;
                  }
              }

             // recupérer le array ici
              $index_label = $this->construcstocks->getLinesproduct();
              $list_product =[];

              foreach($ids_restriction as $vam){
                  $ls =  array_search($vam,$index_label);
                  $mms = explode('%',$ls);
                
                  $list_product[] =  $mms[1];
              }

              $list_libelle = implode(',',$list_product);
              
              if(count($restriction)!=0){
                // bloquer ici
                 $list_product = $this->stocks->getAll();
                if(count($list_product)==0){
                      $list_product[] =[
                        "libelle"=>"Aucun mouvement de stock"

                     ];
                 }
                
                 if(count($list_faible_stocks)==0){
                    $list_faible_stocks[]=[
                      'produit'=>"Aucun mouvement produit"
        
                    ];
                 }
        

                  $message ="Stock de $list_libelle insuffisant pour créer le kit";
                  return view('admin.stockscat',['data'=>$data,'message'=>$message,'list_product'=>$list_product,' list_faible_stocks'=> $list_faible_stocks]);
              }
              
              
              $data_f = array_merge($data_finale1,$data_finale2);
             
              // excuté les chamgement.
              foreach ($data_f as $valus) {
                  $userId = $valus[0]['product_id'];
                   $status = $valus[0]['warehouse_array_list'];
                  // Effectuer la mise à jour pour chaque enregistrement
                   ProductDolibarr::where('product_id', $userId)->update(['warehouse_array_list' => $status]);
                 }

                 foreach($array_id_groups as $vlo){
                    $lc =  array_search($vlo,$index_label);
                    $ms = explode('%',$lc);
                    $data_libelle_listing[] = [
                         'libelle'=>$ms[1],
                         'quantite'=>1,
                         'created_at'=>date('Y-m-d H:i:s'),
                         'updated_at'=>date('Y-m-d H:i:s'),
    
                    ];
    
                 }

                 // faire un trucate avant
                 $this->stocks->deletedatable();
    
                 // faire un insert ici
                 $this->stocks->insert($data_libelle_listing);

                   // retour sur la page
                   $message ="des lignes bien modifiées";
                return view('admin.confirm',['data'=>$data,'message'=>$message]);
             }

            if(count($datas)==1){
                $message ="Aucune ligne modifie";
                 $list_product = $this->stocks->getAll();
                   if(count($list_product)==0){
                         $list_product[] =[
                           "libelle"=>"Aucun mouvement de stock"

                        ];
                   }

                   if(count($list_faible_stocks)==0){
                     $list_faible_stocks[] =[
                      'produit'=>"Aucun mouvement produit"

                    ];
                 }

                
                return view('admin.stockscat',['data'=>$data,'message'=>$message,'list_product'=>$list_product,'list_faible_stocks'=> $list_faible_stocks]);
              
          }
    }

    public function poststockrap(Request $request){
           
         $data = $this->construcstocks->Constructstocks();
         $data_id = $this->construcstocks->getRapes();

         $list_faible_stocks = $this->construcstocks->getStocksrape();
         $array_list1 = $data_id;//

           // recupérer les deux premiere chaine..
           $array_normale =[];
           $array_normale[] = $array_list1[0];
           $array_normale[] = $array_list1[1];
 
           // vider le tableau des deux index
           
            $list_array = array_values(array_diff($array_list1,$array_normale));
          
           $array_list1 = array_flip($list_array);
           
            $existant = $request->get('qts');


            $donnees = $request->get('qte');
           // recupérer les quantité à l'unité
            $donnes = $request->get('qte1');
        

          $datac =[];
          $datas =[];

          $regroup_data =[];
          foreach($donnees as $ky =>$val){
           if($val==""){
                $index="no";
           }else{
                $index=$val;
           }
             $datac[$index.','.$ky] = $ky;
             $datas[$val] = $ky;
          }

          

          $tab_data_qte =[];
          foreach($existant as $kelc => $vald){
               $index_te =  array_search($kelc,$datac);

               // recupérer le cas 
               $index_s = explode(',',$index_te);
               // recupérer le nombre finale de mutiplication
               $s = (int)$index_s[0] - (int)$vald;

               if($s>0){
                   $a ="a";
               }
               if($s<0){
                   $a ="b";
               }

               if($s==""){
                  $a ="c";
               }

               $tab_data_qte[] = $index_te.','.$s.','.$a;

          }
       
           $datacc =array_flip($tab_data_qte);

        $array_list2 = $datacc;
        
            // faire une list de array.
         if(count($datas)!=1){
             // faire le traitement
             // afficher les choses
              foreach($array_list2 as $ms => $valus){
                //
                $index_key = explode(',',$ms);
                  if($index_key[0]!="no"){
                   // va contruire les données
                   $regroup_data[$ms] =$valus;
                  }
              }

            $group_data =[];
            $data_array1 =[];
            $list_qte =[];
            $array_id_groups =[];// recupérer tous les id des produit a update 
           foreach($regroup_data as $keu=>$val){
                $chaine_data = array_search($val,$array_list1);
                  if($chaine_data!=false){
                   // recupérer 
                  $chaine_qte_coeff = explode(',',$keu);
                  $index_search = explode('%',$chaine_data);
                    // fabirquer les données ....
                  $line_create = $index_search[0]*$chaine_qte_coeff[2];// modifier ici ancien $chaine_qte_coeff[0]
                   $line_create1 = $index_search[1];
                  // créer un 1 er jeu de tableau. pour actuliser les quantite rentré .
                  $data_array1[$index_search[1]]=$chaine_qte_coeff[0];
                     
                //  un second jeu de données.
                   $data_array2[] =  [
                     'id_parent'=> $index_search[2],
                     'quantite'=> $line_create
                  ];
                  
                  // recupérer la quantite unite
                  $data_array3[$index_search[2]] = $index_search[3];

                   // recupere les qauntite en line ..
                   $list_qte[] = $line_create;

                   // recupérer les id de produit
                   $array_id_groups[]=$index_search[2];
              }

         }

              // caluler sur le second tableau les quantite a decremente
              $result = array_reduce($data_array2,function($carry,$item){
                if(!isset($carry[$item['id_parent']])){ 
                  $carry[$item['id_parent']] = ['id_parent'=>$item['id_parent'],'quantite'=>$item['quantite']]; 
                 } else { 
                  $carry[$item['id_parent']]['quantite'] += $item['quantite']; 
               } 
                 return $carry; 
                });
  
               // dd($result);
                $data_flush =[];// construire le tableau pour update multiple en bdd
               foreach($result as $lg => $valo){
                   $line = $lg.','.$valo['quantite'];
                   $data_flush[$line]= $lg;
               }
  
                // regouper la somme a deduire 
                 // reconstruire le tableau finale pour qte de l'unite
                $line_final_unite =[];
                $restriction =[];
                $ids_restriction =[];
                foreach($data_array3 as $ky=>$valc){
                    
                    $qte_line = array_search($ky,$data_flush);
                    $line_qte = explode(',',$qte_line);
                    // verifier la quantité trouvé
                      $line_final_unite[$ky]=$valc-$line_qte[1];
                }

                // construire mes datas requete pour line des produits ..
            
               foreach($data_array1 as $ken =>$vn){
                   $data_finale1[] =[
                    ['product_id'=>$ken, 'warehouse_array_list'=>$vn]
                   
                   ];
                   // recupere les qauntite en line ..
                   
              }
           
           
           // construire les data pour les unite a decremente
            foreach($line_final_unite as $kens =>$vns){
                $data_finale2[] =[
                   ['product_id'=>$kens, 'warehouse_array_list'=>$vns]
                   
                 ];

                  // recupérer les quantité negative
                  if($vns<0){
                    $restriction[] = $vns;
                    $ids_restriction[] = $kens;
                 }
           }
            
               // recupérer le array ici
               $index_label = $this->construcstocks->getLinesproduct();
               $list_product =[];
 
               foreach($ids_restriction as $vam){
                   $ls =  array_search($vam,$index_label);
                   $mms = explode('%',$ls);
                 
                   $list_product[] =  $mms[1];
               }
 
               $list_libelle = implode(',',$list_product);
               
               if(count($restriction)!=0){

                $list_product = $this->stocks->getAlls();
                if(count($list_product)==0){
                      $list_product[] =[
                        "libelle"=>"Aucun mouvement de stock"

                     ];
                }

                if(count($list_faible_stocks)==0){
                    $list_faible_stocks[]=[
                      'produit'=>"Aucun mouvement produit"
        
                    ];
                 }
        
                 // bloquer ici
                   $message ="Stock de $list_libelle insuffisant pour créer le kit";
                   return view('admin.stocksrape',['data'=>$data,'message'=>$message,'list_product'=>$list_product,'list_faible_stocks'=>$list_faible_stocks]);
               }
          

             // exécuter sur les manche en unites.
             $data_rape = explode('%',$array_normale[0]);
             $somme_qte = array_sum($list_qte);
             $vnss = $data_rape[1]-$somme_qte;

             $data_finale3[] =[
                ['product_id'=>$data_rape[2], 'warehouse_array_list'=>$vnss]
                   
            ];

             // construire mon tableau pour decremente l'unité.;;
              $data_f = array_merge($data_finale1,$data_finale2,$data_finale3);
             
              // excuté les chamgement.
              $array_id_group =[];
              foreach ($data_f as $valus) {
                $userId = $valus[0]['product_id'];
                 $status = $valus[0]['warehouse_array_list'];
              // Effectuer la mise à jour pour chaque enregistrement
                 ProductDolibarr::where('product_id', $userId)->update(['warehouse_array_list' => $status]);

            }

             // lister les produit qui ont eu un changement de stocks
             
             foreach($array_id_groups as $vlo){
                $lc =  array_search($vlo,$index_label);
                $ms = explode('%',$lc);
                $data_libelle_listing[] = [
                     'libelle'=>$ms[1],
                     'quantite'=>2,
                     'created_at'=>date('Y-m-d H:i:s'),
                     'updated_at'=>date('Y-m-d H:i:s'),

                ];

             }

               // faire un trucate avant
               $this->stocks->deletedatables();
               // faire un insert ici
               $this->stocks->insert($data_libelle_listing);
                // retour sur la page
                $message ="des lignes bien modifiées";
                return view('admin.confirmrape',['data'=>$data,'message'=>$message]);

       }

        if(count($datas)==1){
            $this->construcstocks->Constructstocks();
           $data = $this->construcstocks->getRape();
          $message ="Aucune ligne modifie";
          $list_product = $this->stocks->getAlls();
          if(count($list_product)==0){
            $list_product[]=[
             'libelle'=>"Aucun mouvement de stock"

            ];
        }
        
        if(count($list_faible_stocks)==0){
             $list_faible_stocks[] =[
              "produit"=>"Aucun mouvement produit"

            ];
         }

          return view('admin.stocksrape',['data'=>$data,'message'=>$message,'list_product'=>$list_product,'list_faible_stocks'=>$list_faible_stocks]);
        }

          
    }

    public function stockgroupe(){

         $this->construcstocks->listcategories();
    }

    public function postrape(){
        $this->construcstocks->Constructstocks();
        $data = $this->construcstocks->getRape();
        $list_faible_stocks = $this->construcstocks->getStocksrape();
        $message="";
        $list_product = $this->stocks->getAlls();
        if(count($list_product)==0){
            $list_product[]=[
             'libelle'=>"Aucun mouvement de stock"

            ];
        }

        if(count($list_faible_stocks)==0){
             $list_faible_stocks[] =[
              'produit'=>"Aucun mouvement produit"

            ];
         }

        return view('admin.stocksrape',['data'=>$data,'message'=>$message,'list_product'=>$list_product,'list_faible_stocks'=>$list_faible_stocks]);
    }

    
  public function generateinvoices(){
      $message="";
      $css="no";
      $divid="no";
       return view('admin.generateinvoices',['message'=>$message,'css'=>$css,'divid'=>$divid]);
   }



  public function generatefacture(Request $request){
      
      $ref_commande = $request->get('order_id');// recupérer ref_order entrées par le user.
      $data = $this->orderDolibarr->getAllReforder();// recupérer le tableau des arrays(ref_order)
      $indexs = $request->get('index_value');
      
     // verifie si y'a une clé existant renvoyé
      if(array_search($ref_commande,$data)!=false){
           $this->orderDolibarr->getOrderidfact($ref_commande,$indexs);
            $message ="facture à eté bien envoyé au client";
            $css ="success";
        }else{
            $css="danger";
            $message ="Attention cette commande est introuvable !";
      }

        $divid="yescam";
        return view('admin.generateinvoices',['message'=>$message,'css'=>$css,'divid'=>$divid]);

    }
  

    public function generatefactures(Request $request){

        $ref_commande = $request->get('order_id');// recupérer ref_order entrées par le user.
        $data = $this->orderDolibarr->getAllReforder();// recupérer le tableau des arrays(ref_order)
        $indexs = $request->get('index_value');

        if(array_search($ref_commande,$data)!=false){
            $this->orderDolibarr->getOrderidfact($ref_commande,$indexs);
             $message ="facture à eté bien envoyé au client";
             $css ="success";
         }else{
             $css="danger";
             $message ="Attention cette commande est introuvable !";
       }
 
         $divid="yescam";
         return view('admin.generateinvoices',['message'=>$message,'css'=>$css,'divid'=>$divid]);
 
    }


    function initialQtyLot(){


        try {
            $res = $this->construcstocks->updateinitialQtyLotToZero();

            if ($res["success"]) {
                return redirect()->back()->with('success', $res["message"]);
            }else {
                return redirect()->back()->with('error', 'Oops, une erreur est survenue !');
            }
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    function updateProducts(){      

        try {
            $res = $this->construcstocks->updateProductsCaisse();
           
            if ($res["success"]) {
                return redirect()->back()->with('success', $res["message"]);
            }else {
                return redirect()->back()->with('error', 'Oops, une erreur est survenue !');
            }
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
    
   
}
