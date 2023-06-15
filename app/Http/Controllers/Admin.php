<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Repository\Categorie\CategoriesRepository;
use App\Repository\Distributor\DistributorRepository;
use App\Repository\History\HistoryRepository;
use App\Repository\Product\ProductRepository;
use App\Repository\Role\RoleRepository;
use App\Repository\User\UserRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;

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

    public function __construct(
        Api $api, 
        CategoriesRepository $category,
        UserRepository $user, 
        RoleRepository $role,
        HistoryRepository $history,
        ProductRepository $products,
        DistributorRepository $distributors
    ){
        $this->api = $api;
        $this->category = $category;
        $this->user = $user;
        $this->role = $role;
        $this->history = $history;
        $this->products = $products;
        $this->distributors = $distributors;
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

            if($product['meta_data']){
                $barcode = $product['meta_data'][array_key_last($product['meta_data'])]['key'] == "barcode" ? $product['meta_data'][array_key_last($product['meta_data'])]['value'] : null;
             } else {
                $barcode = null;
            }  

            $category_name = [];
            $category_id = [];

            foreach($product['categories'] as $cat){
                $category_name[] = $cat['name'];
                $category_id[] = $cat['id'];
            }
            
            $ids = array_column($product['attributes'], "name");
            $clesRecherchees = array_keys($ids,  "Volume");

            if(count($clesRecherchees) > 0 && count($product['variations']) > 0){
                $option = $product['attributes'][$clesRecherchees[0]]['options'];
                foreach($option as $key => $op){
                    $insert_products [] = [
                        'product_woocommerce_id' => $product['variations'][$key],
                        'category' =>  implode(',', $category_name),
                        'category_id' => implode(',', $category_id),
                        'variation' => 1,
                        'name' => $product['name'].' - '.$op,
                        'status' => $product['status'],
                        'price' => $product['variation_prices'][$key],
                        'barcode' => $product['barcodes_list'][$key],
                        'manage_stock' => $product['manage_stock_variation'][$key] == "yes" ? 1 : 0,
                        'stock' => $product['stock_quantity_variation'][$key]
                    ];
                }
            } else {
                $insert_products [] = [
                    'product_woocommerce_id' => $product['id'],
                    'category' =>  implode(',', $category_name),
                    'category_id' => implode(',', $category_id),
                    'variation' => 0,
                    'name' => $product['name'],
                    'status' => $product['status'],
                    'price' => $product['price'],
                    'barcode' => $barcode,
                    'manage_stock' => $product['manage_stock'],
                    'stock' => $product['stock_quantity']
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
        return view('admin.analytics');
    }

    public function getAnalytics(Request $request){
        $date = $request->get('date') != "false" ? $request->get('date') : date('Y-m-d');
        $histories = $this->history->getHistoryByDate($date);
  
        $name = [];
        $prepared_count = [];
        $finished_count = [];

        foreach($histories as $history){
            $name[] = $history['name'];
            $prepared_count[] = $history['prepared_count'];
            $finished_count[] = $history['finished_count'];
        }

        echo json_encode(['name' => $name, 'prepared_count' => $prepared_count, 'finished_count' => $finished_count]);
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

    public function createDistributors(Request $request){
        $name = $request->post('name');
        $identifiant = $request->post('identifiant');

        $data = [
            'name' => $name,
            'customer_id' => $identifiant
        ];
        
        if($this->distributors->createDistributor($data)){
            return redirect()->route('distributors')->with('success', 'Distributeur ajouté avec succès !');
        } else {
            return redirect()->route('distributors')->with('error', 'Le distributeur n\'a pas pu être ajouté');
        }
    }

    public function updateDistributors(Request $request){
        $update_name = $request->post('update_name');
        $update_identifiant = $request->post('update_identifiant');
        $distributor_id = $request->post('distributor_id');
        $data = [
            'name' => $update_name,
            'customer_id' => $update_identifiant,
            'id' => $distributor_id
        ];

        if($this->distributors->updateDistributors($data)){
            return redirect()->route('distributors')->with('success', 'Distributeur modifié avec succès !');
        } else {
            return redirect()->route('distributors')->with('error', 'Le distributeur n\'a pas pu être modifié');
        }
    }

    public function deleteDistributors(Request $request){
        $distributor_id = $request->post('distributor_id');

        if($this->distributors->deleteDistributor($distributor_id)){
            return redirect()->route('distributors')->with('success', 'Distributeur supprimé avec succès !');
        } else {
            return redirect()->route('distributors')->with('error', 'Le distributeur n\'a pas pu être supprimé');
        }
    }
    
}
