<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Repository\Role\RoleRepository;
use App\Repository\User\UserRepository;
use App\Repository\History\HistoryRepository;
use App\Repository\Printer\PrinterRepository;
use App\Repository\Product\ProductRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Repository\Categorie\CategoriesRepository;
use Illuminate\Routing\Controller as BaseController;
use App\Repository\Distributor\DistributorRepository;
use Illuminate\Foundation\Validation\ValidatesRequests;
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

    public function __construct(
        Api $api, 
        CategoriesRepository $category,
        UserRepository $user, 
        RoleRepository $role,
        HistoryRepository $history,
        ProductRepository $products,
        DistributorRepository $distributors,
        PrinterRepository $printer
    ){
        $this->api = $api;
        $this->category = $category;
        $this->user = $user;
        $this->role = $role;
        $this->history = $history;
        $this->products = $products;
        $this->distributors = $distributors;
        $this->printer = $printer;
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
                if($attribut['variation']){
                    $variation = $attribut['name'];
                }
            }

            if($variation){
                $ids = array_column($product['attributes'], "name");
                $clesRecherchees = array_keys($ids,  $variation);
            }

            if($variation && count($product['variations']) > 0){
                $option = $product['attributes'][$clesRecherchees[0]]['options'];

                // Insertion du produit de base sans les variations
                $insert_products [] = [
                    'product_woocommerce_id' => $product['id'],
                    'category' =>  implode(',', $category_name),
                    'category_id' => implode(',', $category_id),
                    'variation' => 0,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'barcode' => $barcode,
                    'status' => $product['status'],
                    'manage_stock' => $product['manage_stock'],
                    'stock' => $product['stock_quantity'],
                    'is_variable' => 1,
                    'weight' =>  $product['weight'],
                    'menu_order' => $product['menu_order'],
                ];

                foreach($option as $key => $op){
                    if(isset($product['variations'][$key])){
                        $insert_products [] = [
                            'product_woocommerce_id' => $product['variations'][$key],
                            'category' =>  implode(',', $category_name),
                            'category_id' => implode(',', $category_id),
                            'variation' => 1,
                            'name' => $product['name'].' - '.$op,
                            'price' => $product['variation_prices'][$key],
                            'barcode' => $product['barcodes_list'][$key],
                            'status' => $product['status'],
                            'manage_stock' => $product['manage_stock_variation'][$key] == "yes" ? 1 : 0,
                            'stock' => $product['stock_quantity_variation'][$key],
                            'is_variable' => 0,
                            'weight' =>  $product['weights_variation'][$key] != "" ? $product['weights_variation'][$key] : $product['weight'],
                            'menu_order' => $product['menu_order'],
                        ];
                    }
                }
            } else {
                $insert_products [] = [
                    'product_woocommerce_id' => $product['id'],
                    'category' =>  implode(',', $category_name),
                    'category_id' => implode(',', $category_id),
                    'variation' => 0,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'barcode' => $barcode,
                    'status' => $product['status'],
                    'manage_stock' => $product['manage_stock'],
                    'stock' => $product['stock_quantity'],
                    'is_variable' => 0,
                    'weight' =>  $product['weight'],
                    'menu_order' => $product['menu_order'],
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

    public function updateOrderCategory(Request $request){
        $id = $request->post('id');
        $order_display = $request->post('order_display');
        $parent = $request->post('parent');
        echo json_encode(['success' => $this->category->updateCategoryOrder($id, $order_display, $parent)]);
    }

    public function account(){
        $users = $this->user->getUsersAndRoles();
        $rolesUser =  Auth()->user()->roles->toArray();
        $ids = array_column($rolesUser, "id");
        $isAdmin = count(array_keys($ids,  1)) > 0 ? true : false;
        $roles = $this->role->getRoles();
        
        return view('admin.account', ['users' => $users, 'roles' => $roles, 'isAdmin' => $isAdmin]);
    }

    public function analytics(){
        $date = date('Y-m-d');
        $histories = $this->history->getHistoryAdmin($date);
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

        return view('admin.analytics', ['histories' => $list_histories, 'average_by_name' => $average_by_name]);
    }

    public function roles(){
        $roles = $this->role->getRoles();
        $role_can_not_delete = [1,2,3,4];
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
        $role_can_not_delete = [1,2,3,4];
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
        $preparateurs = $this->user->getUsersByRole([2]);
        $printers = $this->printer->getPrinters();
        return view('admin.printers', ['printers' => $printers, 'preparateurs' => $preparateurs]);
    }

    public function addPrinter(Request $request){
        $name = $request->post('name');
        $address_ip  = $request->post('address_ip');
        $port  = $request->post('port');
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
        $update_port  = $request->post('update_port');
        $update_user_id = $request->post('update_user_id');
        $printer_id = $request->post('printer_id');

        $data = [
            'name' => $update_name,
            'address_ip' => $update_address_ip,
            'port' => $update_port,
            'user_id' => $update_user_id
        ];

        try{
            if($this->printer->updatePrinter($data, $printer_id)){
                return redirect()->route('printers')->with('success', 'Imprimante modifié avec succès !');
            } else {
                return redirect()->route('printers')->with('error', 'L\'imprimante n\'a pas pu être modifié');
            }
        } catch(Exception $e){
            if(str_contains($e->getMessage(), 'Duplicate ')){
                return redirect()->route('printers')->with('error', 'L\'adresse IP de l\'imprimante doit être unique !');
            } else {
                return redirect()->route('printers')->with('error', $e->getMessage());
            }
        }
    }

    public function deletePrinter(Request $request){
        $printer_id = $request->post('printer_id');

        if($this->printer->deletePrinter($printer_id)){
            return redirect()->route('printers')->with('success', 'Imprimante modifié avec succès !');
        } else {
            return redirect()->route('printers')->with('error', 'L\'imprimante n\'a pas pu être modifié');
        }
    }
}
