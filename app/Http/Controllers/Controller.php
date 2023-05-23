<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Http\Controllers\Order;
use App\Repository\Role\RoleRepository;
use App\Repository\User\UserRepository;
use App\Repository\Order\OrderRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
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

    public function __construct(Order $orderController, 
        UserRepository $users,
        RoleRepository $role,
        OrderRepository $orders
    ){
      $this->orderController = $orderController;
      $this->users = $users;
      $this->role = $role;
      $this->orders = $orders;
    }
 
    public function index(Request $request){
        return view('index');
    }

    public function orderPreparateur(){
        $orders = $this->orderController->getOrder();
        return view('preparateur.index_preparateur', ['orders' => $orders[0] ?? $orders /* Show only first order */, 'number_orders' =>  count($orders)]);
    }

    public function ordersDistributeurs(){
        $orders = $this->orderController->getOrderDistributeur();
        return view('preparateur.distributeur.index_preparateur', ['orders' => $orders[0] ?? $orders /* Show only first order */, 'number_orders' =>  count($orders)]);
    }

    public function dashboard(){
        $teams = $this->users->getUsersByRole([2, 3])->toArray();
        $teams_have_order = $this->orders->getUsersWithOrder()->toArray();
        $roles = $this->role->getRoles();
        return view('leader.dashboard', ['teams' => $teams, 'roles' => $roles, 'teams_have_order' => $teams_have_order]);
    }

    public function updateRole(Request $request){
        $user_id = $request->post('user_id');
        $role_id = $request->post('role_id');
        echo json_encode(['success' => $this->users->updateRoleByUser($user_id, $role_id)]);
    }
    
}
