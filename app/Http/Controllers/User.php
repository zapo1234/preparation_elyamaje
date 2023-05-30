<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Repository\User\UserRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class User extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $users;
   
    public function __construct(UserRepository $users){
        $this->users = $users;
    }
    
    public function updateRole(Request $request){
        $user_id = $request->post('user_id');
        $role_id = $request->post('role_id');
        echo json_encode(['success' => $this->users->updateRoleByUser($user_id, $role_id)]);
    }  

    public function createAccount(Request $request) {
        $input = $request->all();
        $user_name_last_name =   $input['name_last_name'];
        $email =  $input['email'];
        $role =  $input['role'];

        $rand_pass = rand(136,50000);
        $password = "elyamaje@$rand_pass";
        // crypter l'email.
        $password_hash = Hash::make($password);
        $create = $this->users->createUser($user_name_last_name, $email, $role, $password_hash);

        if($create){
            // ENVOIE EMAIL
            Mail::send('email.newAccount', ['email' => $email, 'password'=> $password], function($message) use($email){
                $message->to($email);
                $message->from('no-reply@elyamaje.com');
                $message->subject('Confirmation de création de compte Préparation Elyamaje');
            });

            return redirect()->back()->with('success', 'Compte créé avec succès !');
        } else {
            return redirect()->back()->with('success',  $create);
        }
        
    }
}
