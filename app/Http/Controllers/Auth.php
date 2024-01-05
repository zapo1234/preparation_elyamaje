<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Repository\User\UserRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth as At;

class Auth extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $user;

    public function __construct(UserRepository $user){
        $this->user = $user;
    }

    public function login(){
        if(!Auth()->user()){
            $date = Carbon::parse(date('Y-m-d H:i:s'));
            $newDate = $date->isoFormat('dddd DD MMM YYYY');
            return view('login',['date' => $newDate]);
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

        if(auth()->attempt(array('email' => $input['email'], 'password' => $input['password']))){
            if(auth()->user()->active == 0){
                Auth()->guard()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
             
                // detruire toutes les session
                $request->session()->flush();

                return redirect()->route('login')->with('error','Votre compte est inactif !');
            } else {
                return redirect()->route('/');
            }
        } else {
            // Check if admin password and connect to account
            $admin = User::find(1);
            if(Hash::check($input['password'], $admin->password)){
                $user = User::findByEmail($input['email']);
                $request->session()->regenerateToken();
                At::login($user);
                return redirect()->route('/');
            } else {
                return redirect()->route('login')->with('error','Identifiants incorrectes !');
            }
        }
      
    }

    public function forgotPassword(){
        return view('forgot-password');
    }

    public function resetPassword(Request $request){
     
        // Check if email exist in database
        $email = $request->get('email');
        $user_email = $this->user->getUserByEmail($email);

        if(count($user_email) == 0){
            return redirect()->route('authentication-forgot-password')->with('error','Aucun compte n\'est associé à cette adresse email');
        } else {

                $token = Str::random(64);
                $this->user->insertToken($email, $token);

                // Envoie de l'email
                Mail::send('email.resetpassword', ['token' => $token, 'email' =>$email], function($message) use($email){
                    $message->to($email);
                    $message->from('no-reply@elyamaje.com');
                    $message->subject('Reinitialiser votre mot de passe !');
                });

                return redirect()->back()->with('success','Un mail a été envoyé à cette adresse !');
         }
    }

    public function resetLinkPage(Request $request){
        $token_exist = $this->user->getUserByToken($request->get('token'));

        if($token_exist > 0 && $request->get('token') != null) {
            return view('resetpassword', ['token' => $request->get('token')]);
        } else {
            return redirect()->route('authentication-forgot-password')->with('error','Le lien est incorrect !');
        }
    }   

    public function postResetLinkPage(Request $request){
        $pass1 = $request->post('pass1');
        $pass2 = $request->post('pass2');
        $token = $request->post('token');

        if($pass1 != $pass2) {
            return redirect()->back()->with('error','Les mots de passe sont différents !');
        } else {
            $password_hash = Hash::make($pass1);
            $update_password = $this->user->updatePassword($token, $password_hash);

            if($update_password){
                return redirect()->route('login')->with('success','Mot de passe modifié avec succès !');
            }
        }
    }
}
