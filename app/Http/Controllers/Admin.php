<?php

namespace App\Http\Controllers;

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
use Illuminate\Routing\Controller as BaseController;
use App\Repository\Distributor\DistributorRepository;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Repository\OrderDolibarr\OrderDolibarrRepository;
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
        LabelMissingRepository $labelMissing,
        OrderDolibarrRepository $orderDolibarr,
        TransferOrder $facture,
        LogErrorRepository $logError
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
        $this->labelMissing = $labelMissing;
        $this->orderDolibarr = $orderDolibarr;
        $this->facture = $facture;
        $this->logError = $logError;
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
                        ];
                    }
                }
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
                ];
            }
        }

   
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
        $list_histories = [];
        try{
            $list_histories = $this->buildHistory($histories);
            echo json_encode(['success' => true, 'histories' => $list_histories /*, 'average_by_name' => $average_by_name*/]);
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
                    $average[$l['name']][$l['date']] = ['finished_count' => $l['finished_count'], 'prepared_count' => $l['prepared_count'], 'items_picked' => $l['items_picked']];
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
                $average_by_name[$key] = ['avg_prepared' => round($number_prepared / count($avg), 2), 'avg_finished' => round($number_finished / count($avg), 2), 'avg_items_picked' => round($number_items_picked / count($avg), 2)];
                $number_prepared = 0;
                $number_finished = 0;
                $number_items_picked = 0;
            }

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
        $update_name = $request->post('update_name');
        $update_address_ip  = $request->post('update_address_ip');
        $update_port  = $request->post('update_port') ?? 9100;
        $update_user_id = $request->post('update_user_id');
        $printer_id = $request->post('printer_id');

        $data = [
            'name' => $update_name,
            'address_ip' => $update_address_ip,
            'port' => $update_port,
            'user_id' => $update_user_id
        ];

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
        $list_format = [
            'PDF_A4_300dpi' => 'Impression bureautique en PDF, de dimension A4 et de résolution 300dpi',
            'PDF_10x15_300dpi' => 'Impression bureautique en PDF, de dimension 10cm par 15cm, et de résolution 300dpi',
            'ZPL_10x15_203dpi' => 'Impression thermique en ZPL, de dimension 10cm par 15cm, et de résolution 203dpi',
            'ZPL_10x15_300dpi' => 'Impression thermique en ZPL, de dimension 10cm par 15cm, et de résolution 300dpi'
        ];

        return view('admin.colissimo', ['list_format' => $list_format, 'colissimo' => count($colissimo) > 0 ? $colissimo[0] : null]);
    }

    public function updateColissimo(Request $request){
        $format = $request->post('format');
        $address_ip = $request->post('address_ip');
        $port = $request->post('port');

        $data = [
            'format' => $format,
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

            if(str_contains($order_id, 'CO')){
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
                $this->factorder->Transferorder($order);  

                // Stock historique
                $data = [
                    'order_id' => $order_id,
                    'user_id' => Auth()->user()->id,
                    'status' => 'finished',
                    'poste' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
          
                $this->history->save($data);
                // Modifie le status de la commande sur Woocommerce en "Prêt à expédier"
                $this->order->updateOrdersById([$order_id], "finished");
                $this->api->updateOrdersWoocommerce("lpc_ready_to_ship", $order_id);

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
                    'poste' => [$histo['poste']],
                    'prepared_order' => $histo['status'] == "prepared" ? [$histo['order_id']] : [],
                    'finished_order' => $histo['status'] == "finished" ? [$histo['order_id']] : [],
                    'prepared_count' => $histo['status'] == "prepared" ? 1 : 0,
                    'finished_count' => $histo['status'] == "finished" ? 1 : 0,
                    'items_picked' =>  $histo['status'] == "prepared" ? $histo['total_quantity'] : 0,
                    'date' => date('d/m/Y', strtotime($histo['created_at']))
                ];
            } else {
                $histo['status'] == "prepared" ? array_push($list_histories[$id][$histo['id']]['prepared_order'],$histo['order_id']) : array_push($list_histories[$id][$histo['id']]['finished_order'],$histo['order_id']);
                $list_histories[$id][$histo['id']]['poste'][] = $histo['poste'];
                $list_histories[$id][$histo['id']]['prepared_order'] = array_unique($list_histories[$id][$histo['id']]['prepared_order']);
                $list_histories[$id][$histo['id']]['finished_order'] = array_unique($list_histories[$id][$histo['id']]['finished_order']);
                $list_histories[$id][$histo['id']]['poste'] = array_unique($list_histories[$id][$histo['id']]['poste']);
                $list_histories[$id][$histo['id']]['prepared_count'] = count($list_histories[$id][$histo['id']]['prepared_order']);
                $list_histories[$id][$histo['id']]['finished_count'] = count($list_histories[$id][$histo['id']]['finished_order']);
                $histo['status'] == "prepared" ? $list_histories[$id][$histo['id']]['items_picked'] = $list_histories[$id][$histo['id']]['items_picked'] + $histo['total_quantity'] : 0;
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
            $apiUrl = env('KEY_API_URL');
            $apiKey = env('KEY_API_DOLIBAR');

            $produitParamProduct = array(
                'apikey' => $apiKey,
                'limit' => 10000,
            );


            $all_products = $this->api->CallAPI("GET", $apiKey, $apiUrl."products",$produitParamProduct);  
            $all_products = json_decode($all_products,true);

            if ($all_products) {
                foreach ($all_products as $key => $product) {

                    

                    array_push($products_dolibarrs_save, [
                        "product_id" => $product["id"],
                        "label" => $product["label"],
                        "price_ttc" =>$product["price"]? ($product["price"]*(($product["tva_tx"]*0.01)+1)):$product["price_ttc"],
                        "barcode" => $product["barcode"],
                        "poids" => 0,
                        "warehouse_array_list" => json_encode($product["warehouse_array_list"])
                    ]);

                }
    
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

}
