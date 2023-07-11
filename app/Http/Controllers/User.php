<?php

namespace App\Http\Controllers;

use App\Http\Service\Api\Api;
use App\Repository\Order\OrderRepository;
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
    private $orders;
    private $api;

   
    public function __construct(UserRepository $users, OrderRepository $orders, Api $api){
        $this->users = $users;
        $this->orders = $orders;
        $this->api = $api;

    }
    
    public function updateRole(Request $request){
        $user_id = $request->post('user_id');
        $role_id = $request->post('role_id');
        echo json_encode(['success' => $this->users->updateRoleByUser($user_id, $role_id)]);
    }  

    public function createAccount(Request $request) {

        $input = $request->all();

        if(!isset($input['role']) || !isset($input['email']) || !isset($input['name_last_name'])){
            return redirect()->back()->with('error',  'Veuillez renseigner les champs obligatoire');
        }

        $user_name_last_name =   $input['name_last_name'];
        $email =  $input['email'];
        $role =  $input['role'];
        $poste =  $input['poste'] ?? 0;

        // Check if email is unique
        $email_already_exist = $this->users->getUserByEmail($email);
        if($email_already_exist > 0){
            return redirect()->back()->with('error',  'Cet email existe déjà !');
        }

        $rand_pass = rand(136,50000);
        $password = "elyamaje@$rand_pass";
        // crypter l'email.
        $password_hash = Hash::make($password);
        $create = $this->users->createUser($user_name_last_name, $email, $role, $password_hash, $poste);

        if($create){
            
            // ENVOIE EMAIL
            Mail::send('email.newAccount', ['email' => $email, 'name' => $user_name_last_name, 'password'=> $password], function($message) use($email){
                $message->to($email);
                $message->from('no-reply@elyamaje.com');
                $message->subject('Confirmation de création de compte Préparation Elyamaje');
            });

            return redirect()->back()->with('success', 'Compte créé avec succès !');
        } else {
            return redirect()->back()->with('error',  $create);
        }
        
    }

    public function deleteAccount(Request $request){
        $user_id = $request->post("account_user");

        if($user_id != 1){
            // Vérifie si l'utilisateur à des commandes en cours
            $orders_users = $this->orders->getAllOrdersByIdUser($user_id)->toArray();
           
            if(count($orders_users) > 0){
                return redirect()->back()->with('error',  'Cet utilisateur à des commandes en cours !');
            } else {
                $delete = $this->users->deleteUser($user_id);

                if($delete){
                    return redirect()->back()->with('success', 'Compte supprimé avec succès !');
                } else {
                    return redirect()->back()->with('error',  $delete);
                }
            }
        } else {
            return redirect()->back()->with('error',  'L\'administrateur principal ne peut pas être supprimé !');
        }
       
    }

    public function updateAccount(Request $request){

        $input = $request->all();

        if(!isset($input['update_role']) || !isset($input['update_email']) || !isset($input['update_name_last_name'])){
            return redirect()->back()->with('error',  'Veuillez renseigner les champs obligatoire');
        }

        $user_id =   $input['account_user_update'];
        $user_name_last_name =  $input['update_name_last_name'];
        $email =  $input['update_email'];
        $role =  $input['update_role'];
        $poste =  $input['update_poste'] ?? 0;
        
        // Check if email is unique
        $email_already_exist = $this->users->getUserByEmail($email, $user_id);
        if($email_already_exist > 0){
            return redirect()->back()->with('error',  'Cet email existe déjà !');
        }
  
        $update = $this->users->updateUserById($user_id, $user_name_last_name, $email, $role, $poste);

        if($update){
            return redirect()->back()->with('success', 'Compte modifié avec succès !');
        } else {
            return redirect()->back()->with('error',  $update);
        }
    }


    public function getUser(Request $request){
        $user_id = $request->get('user_id');
        $user = $this->users->getUserById($user_id);
       
        if(count($user) > 0){
            echo json_encode(['success' => true, 'user' => $user[0] ]);
        } else {
            echo json_encode(['success' => false ]);
        }
       
    }

    public function noRole(){
        return view('norole');
    }
}
