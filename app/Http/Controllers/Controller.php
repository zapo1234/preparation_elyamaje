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

    public function __construct(Order $orderController, 
        UserRepository $users,
        RoleRepository $role,
        OrderRepository $orders,
        CategoriesRepository $categories
    ){
      $this->orderController = $orderController;
      $this->users = $users;
      $this->role = $role;
      $this->orders = $orders;
      $this->categories = $categories;

    }
    
     // INDEX ADMIN
    public function index(Request $request){
        return view('index');
    }

    // CONFIGURATION ADMIN
    public function configuration(){
        $categories = $this->categories->getAllCategories();
        return view('admin.configuration', ['categories' => $categories]);
    }

    // PRÉPARATEUR COMMANDES CLASSIQUES
    public function orderPreparateur(){
        $orders = $this->orderController->getOrder();
        return view('preparateur.index_preparateur', ['orders' => $orders[0] ?? $orders /* Show only first order */, 'number_orders' =>  count($orders)]);
    }

     // PRÉPARATEUR COMMANDES DISTRIBUTEURS
    public function ordersDistributeurs(){
        $orders = $this->orderController->getOrderDistributeur();
        return view('preparateur.distributeur.index_preparateur', ['orders' => $orders[0] ?? $orders /* Show only first order */, 'number_orders' =>  count($orders)]);
    }

    // INDEX CHEF D'ÉQUIPE
    public function dashboard(){
        $teams = $this->users->getUsersByRole([2, 3, 5])->toArray();
        $teams_have_order = $this->orders->getUsersWithOrder()->toArray();

        $ids = array_column($teams, "role_id");
        $number_preparateur = count(array_keys($ids,  2));

        $roles = $this->role->getRoles();
        return view('leader.dashboard', ['teams' => $teams, 'roles' => $roles, 'teams_have_order' => $teams_have_order, 'number_preparateur' => $number_preparateur]);
    }

    // INDEX EMBALLEUR 
    public function wrapOrder(){
        return view('emballeur.index');
    }
}
