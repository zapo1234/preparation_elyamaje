<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Http\Controllers\Order;
use App\Repository\Role\RoleRepository;
use App\Repository\User\UserRepository;
use App\Repository\Order\OrderRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Repository\Categorie\CategoriesRepository;
use App\Repository\Product\ProductRepository;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $orderController;
    private $orders;
    private $users;
    private $role;
    private $categories;
    private $products;

    public function __construct(Order $orderController, 
        UserRepository $users,
        RoleRepository $role,
        OrderRepository $orders,
        CategoriesRepository $categories,
        ProductRepository $products
    ){
      $this->orderController = $orderController;
      $this->users = $users;
      $this->role = $role;
      $this->orders = $orders;
      $this->categories = $categories;
      $this->products = $products;
    }
    
     // INDEX ADMIN
    public function index(Request $request){
        $teams = $this->users->getUsersByRole([2, 3, 5]);
        $teams_have_order = $this->orders->getUsersWithOrder()->toArray();
        $number_preparateur = 0;
        
        foreach($teams as $team){
            foreach($team['role_id'] as $role){
                if($role == 2){
                    $number_preparateur = $number_preparateur + 1;
                }
            }
        }   

        // dd($teams);

        $roles = $this->role->getRoles();

        return view('index', ['teams' => $teams, 'roles' => $roles, 'teams_have_order' => $teams_have_order, 'number_preparateur' => $number_preparateur]);
    }


    // CONFIGURATION ADMIN
    public function categories(){
        $categories = $this->categories->getAllCategories();
        return view('admin.categories', ['categories' => $categories]);
    }

    // CONFIGURATION LIST PRODUCTS ADMIN
    public function products(){
        $products = $this->products->getAllProducts();
        return view('admin.products', ['products' => $products]);
    }

    // PRÉPARATEUR COMMANDES CLASSIQUES
    public function orderPreparateur(){
        $orders = $this->orderController->getOrder();
        return view('preparateur.index_preparateur', ['user' => Auth()->user()->name, 'orders' => $orders[0] ?? $orders /* Show only first order */, 'number_orders' =>  count($orders)]);
    }

    // PRÉPARATEUR COMMANDES DISTRIBUTEURS
    public function ordersDistributeurs(){
        $orders = $this->orderController->getOrderDistributeur();
        return view('preparateur.distributeur.index_preparateur', ['user' => Auth()->user()->name, 'orders' => $orders[0] ?? $orders /* Show only first order */, 'number_orders' =>  count($orders)]);
    }

    // INDEX CHEF D'ÉQUIPE
    public function dashboard(){
       $teams = $this->users->getUsersByRole([2, 3, 5]);
       $teams_have_order = $this->orders->getUsersWithOrder()->toArray();
       $number_preparateur = 0;
        
        foreach($teams as $team){
            foreach($team['role_id'] as $role){
                if($role == 2){
                    $number_preparateur = $number_preparateur + 1;
                }
            }
        }   

        $roles = $this->role->getRoles();
        return view('leader.dashboard', ['teams' => $teams, 'roles' => $roles, 'teams_have_order' => $teams_have_order, 'number_preparateur' => $number_preparateur]);
    }

    // INDEX EMBALLEUR 
    public function wrapOrder(){
        return view('emballeur.index');
    }
}
