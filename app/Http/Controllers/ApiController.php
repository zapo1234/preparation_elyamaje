<?php

namespace App\Http\Controllers;
use App\Helper\UserService;
use Illuminate\Http\Request;
// use Illuminate\Routing\Controller as BaseController;

class ApiController extends Controller
{
   public function login(Request $request){
      $response = (new UserService($request->email, $request->password))->login();
      return response()->json($response);
   }

   public function test(){
      return response()->json(['success' => true]);
   }
}
