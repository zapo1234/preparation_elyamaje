<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;

class Auth extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function __construct(){
    
    }

    public function login(Request $request){
        if(!Auth()->user()){
            return view('login');
        } else {
            return redirect()->route('/');
        }
    }


    public function logout(Request $request){
        
       Auth()->guard()->logout();
       $request->session()->invalidate();
       $request->session()->regenerateToken();
    
        // detruire toutes les session
        $request->session()->flush();

        if($request->wantsJson()){
            return new Response('', 204) ;
        } else {
            return redirect()->route('login');
        }

           
    }

    public function postLogin(Request $request){

        $input = $request->all();
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);


        if(auth()->attempt(array('email' =>$input['email'], 'password' =>$input['password']))){
            return redirect()->route('/');
        } else {
            return redirect()->route('login')->with('error','Identifiants incorrectes !');
        }
      
    }
    
}
