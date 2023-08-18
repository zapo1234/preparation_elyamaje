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

    public function __construct(Order $orderController, 
        UserRepository $users,
        RoleRepository $role,
        OrderRepository $orders,
        CategoriesRepository $categories,
        ProductRepository $products,
        PrinterRepository $printer
    ){
      $this->orderController = $orderController;
      $this->users = $users;
      $this->role = $role;
      $this->orders = $orders;
      $this->categories = $categories;
      $this->products = $products;
      $this->printer = $printer;
    }
    
     // INDEX ADMIN
    public function index(){
        $teams = $this->users->getUsersByRole([2, 3, 5]);
        $teams_have_order = $this->orders->getUsersWithOrder()->toArray();
        $products =  $this->products->getAllProductsPublished();
        $number_preparateur = 0;
        
        foreach($teams as $team){
            foreach($team['role_id'] as $role){
                if($role == 2){
                    $number_preparateur = $number_preparateur + 1;
                }
            }
        }   

        $roles = $this->role->getRoles();
        return view('index', ['teams' => $teams, 'products' => $products, 'roles' => $roles, 'teams_have_order' => $teams_have_order, 'number_preparateur' => $number_preparateur]);
    }

    // INDEX CHEF D'ÉQUIPE
    public function dashboard(){
        $teams = $this->users->getUsersByRole([2, 3, 5]);
        $teams_have_order = $this->orders->getUsersWithOrder()->toArray();
        $products =  $this->products->getAllProductsPublished();
        $number_preparateur = 0;
         
         foreach($teams as $team){
             foreach($team['role_id'] as $role){
                 if($role == 2){
                     $number_preparateur = $number_preparateur + 1;
                 }
             }
         }   
 
        $roles = $this->role->getRoles();
        return view('leader.dashboard', ['teams' => $teams, 'products' => $products, 'roles' => $roles, 'teams_have_order' => $teams_have_order, 'number_preparateur' => $number_preparateur]);
    }

    // CONFIGURATION ADMIN
    public function categories(){
        $categories = $this->categories->getAllCategories();
        return view('admin.categories', ['categories' => $categories]);
    }

    // CONFIGURATION LIST PRODUCTS ADMIN
    public function products(){
        // Get all products
        $products = $this->products->getAllProducts();
        // Get all categories products
        $categories = $this->categories->getAllCategories();
        // dd($categories);
        return view('admin.products', ['products' => $products, 'categories' => $categories]);
    }

    // PRÉPARATEUR COMMANDES CLASSIQUES
    public function orderPreparateur(){
        $printer = $this->printer->getPrinterByUser(Auth()->user()->id);
        $orders = $this->orderController->getOrder();
        $order_process = [] ;
        $orders_waiting_to_validate = [];
        $orders_validate = [];

        foreach($orders as $order){
            if($order['details']['status'] == "waiting_to_validate"){
                $orders_waiting_to_validate[] = $order;
            } else if($order['details']['status'] == "waiting_validate"){
                $orders_validate[] = $order;
            } else {
                $order_process[] = $order;
            }
        }

        return view('preparateur.index_preparateur', ['user' => Auth()->user()->name, 
            'orders_waiting_to_validate' => $orders_waiting_to_validate, 
            'orders_validate' => $orders_validate, 
            'orders' => isset($order_process[0]) ? $order_process[0] : [] /* Show only first order */, 
            'number_orders' =>  count($order_process),
            'number_orders_waiting_to_validate' =>  count($orders_waiting_to_validate),
            'number_orders_validate' =>  count($orders_validate),
            'printer' => $printer
        ]);
    }

    // PRÉPARATEUR COMMANDES DISTRIBUTEURS
    public function ordersDistributeurs(){
        $printer = $this->printer->getPrinterByUser(Auth()->user()->id);
        $orders = $this->orderController->getOrderDistributeur();
        $order_process = [] ;
        $orders_waiting_to_validate = [];
        $orders_validate = [];

        foreach($orders as $order){
            if($order['details']['status'] == "waiting_to_validate"){
                $orders_waiting_to_validate[] = $order;
            } else if($order['details']['status'] == "waiting_validate"){
                $orders_validate[] = $order;
            } else {
                $order_process[] = $order;
            }
        }

        return view('preparateur.distributeur.index_preparateur', ['user' => Auth()->user()->name, 
            'orders_waiting_to_validate' => $orders_waiting_to_validate, 
            'orders_validate' => $orders_validate, 
            'orders' => isset($order_process[0]) ? $order_process[0] : [] /* Show only first order */, 
            'number_orders' =>  count($order_process),
            'number_orders_waiting_to_validate' =>  count($orders_waiting_to_validate),
            'number_orders_validate' =>  count($orders_validate),
            'printer' => $printer
        ]);
    }

    // INDEX EMBALLEUR 
    public function wrapOrder(){
        return view('emballeur.index');
    }
}
