<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ApiController extends BaseController
{
   public function login(Request $request){
      $input = $request->all();

      if(auth()->attempt(array('email' => $input['email'], 'password' => $input['password']))){
        return response()->json(['success' => true]);
      } else {
         return response()->json(['success' => false]);
      }
   }
}
