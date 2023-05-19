<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Order;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $orderController;

    public function __construct(Order $orderController){
      $this->orderController = $orderController;
    }
 
    public function index(Request $request){
      if(Auth()->user()->role == 1){
          return view('index');
      } else if(Auth()->user()->role == 2){
          $orders = $this->orderController->getOrder();
          return view('index_preparateur', ['orders' => $orders]);
      }
    }
    
}
