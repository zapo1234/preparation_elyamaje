<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Http\Controllers\Order;
use App\Repository\User\UserRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $orderController;
    private $users;

    public function __construct(Order $orderController, UserRepository $users){
      $this->orderController = $orderController;
      $this->users = $users;

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

        $teams = $this->users->getUsersByRole(3);
        return view('emballeur.dashboard', ['dashboard' => $teams]);
    }
    
}
