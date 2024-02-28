<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Repository\History\HistoryRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Repository\User\UserRepository;
use Illuminate\Support\Facades\Storage;
use App\Repository\Order\OrderRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Repository\OrderDolibarr\OrderDolibarrRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class User extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $users;
    private $orders;
    private $api;
    private $orderDolibarr;
    private $history;

    public function __construct(
        UserRepository $users, 
        OrderRepository $orders, 
        Api $api,
        OrderDolibarrRepository $orderDolibarr,
        HistoryRepository $history
    ){
        $this->users = $users;
        $this->orders = $orders;
        $this->api = $api;
        $this->orderDolibarr = $orderDolibarr;
        $this->history = $history;
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
        $type =  $input['type'] ?? "warehouse";
        $password = $input['password'] != null && $input['password'] != "" ? $input['password'] : false;
        $send_mail = false;

        // Check if email is unique
        $email_already_exist = $this->users->getUserByEmail($email);
        if(count($email_already_exist) > 0){
            if($email_already_exist[0]->active == 0){
                $this->users->updateUserActive($email);
                return redirect()->back()->with('success',  'Le compte associé à cette adresse '.$email.' à été réactivé');
            } else if($email_already_exist[0]->active == 1){
                return redirect()->back()->with('error',  'Cet email existe déjà !');
            }
        }
        
        if(!$password){
            $rand_pass = rand(136,50000);
            $password = "elyamaje@$rand_pass";
            $send_mail = true;
        }
       
        // crypter l'email.
        $password_hash = Hash::make($password);
        $create = $this->users->createUser($user_name_last_name, $email, $role, $password_hash, $poste, $type);

        if($create){
            if($send_mail){
                // ENVOIE EMAIL
                Mail::send('email.newAccount', ['email' => $email, 'name' => $user_name_last_name, 'password'=> $password], function($message) use($email){
                    $message->to($email);
                    $message->from('no-reply@elyamaje.com');
                    $message->subject('Confirmation de création de compte Préparation Elyamaje');
                });
            }
            return redirect()->back()->with('success', 'Compte créé avec succès !');
        } else {
            return redirect()->back()->with('error',  $create);
        }
        
    }

    public function deleteAccount(Request $request){
        $user_id = $request->post("account_user");

        if($user_id != 1){
            $delete = $this->users->deleteUser($user_id);

            if($delete){
                return redirect()->back()->with('success', 'Compte désactivé avec succès !');
            } else {
                return redirect()->back()->with('error',  $delete);
            }
        } else {
            return redirect()->back()->with('error',  'L\'administrateur principal ne peut pas être supprimé !');
        }  
    }

    public function activeAccount(Request $request){
        $user_id = $request->post("account_to_active");

        if($user_id){
            $update = $this->users->updateUserActiveById($user_id);
            $role = $this->users->addRole($user_id, 5);

            if($update && $role){
                return redirect()->back()->with('success', 'Compte activé avec succès !');
            } else {
                return redirect()->back()->with('error', 'Oops, une erreur est survenue !');
            }
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
        $type =  $input['update_type'] ?? null;
        
        // Check if email is unique
        $email_already_exist = $this->users->getUserByEmail($email, $user_id);
        if($email_already_exist > 0){
            return redirect()->back()->with('error',  'Cet email existe déjà !');
        }
  
        $update = $this->users->updateUserById($user_id, $user_name_last_name, $email, $role, $poste, $type);

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

    public function accountDetails(Request $request){
        $user_id = $request->get('user_id');
        $accessAccount = Auth()->user()->hasRole(1) || Auth()->user()->hasRole(4) ? true : false;

        // Only Super Admin can update Super Admin profile
        if(Auth()->user()->id != 1 && $user_id == 1){
            abort(403);
        }

        // If use have not right to update other user
        if($user_id != Auth()->user()->id && (!Auth()->user()->hasRole(1) && !Auth()->user()->hasRole(4))){
            abort(403);
        }

        $user = $this->users->getUserById($user_id);
        $user = count($user) > 0 ? $user[0] : [];

        // Not admin user can't update admin user
        if(!Auth()->user()->hasRole(1) && in_array(1, $user['roles'])){
            abort(403);
        }
     
        $parameter = $request->all();
        
        // If not user found return 404
        if(count($user) == 0){
            abort(404);
        } else {
            $type = $user['type'];
        }

        $histories = [];

        // Calcule stats vendeuse / caissière
        if($type == "shop"){
            $orders = $this->orderDolibarr->getAllOrdersBeautyProf($user_id, $parameter);
            $total_order = 0;

            foreach($orders as $ord){
                $histories['details'] = 
                $total_order = $total_order + $ord['total_order_ttc'];
            }

            $histories['details'] = $orders;
            $histories['average'] = $total_order > 0 ? number_format($total_order / count($orders), 2) : $total_order;
            $histories['total_order'] = count($orders) ?? 0;
            $histories['total_amount_order'] = number_format($total_order, 2) ?? 0;

        }

        // Calcule stats préparation / emballage
        if($type == "warehouse"){
            $history = $this->history->getHistoryByIdUser($user_id);
            $orders_prepared = 0;
            $orders_finished = 0;
            $products = 0;

            foreach($history as $histo){
                if($histo['status'] == "finished"){
                    $orders_finished = $orders_finished + 1;
                } else if($histo['status'] == "prepared"){
                    $orders_prepared = $orders_prepared + 1;
                    $products = $products + intval($histo['total_product']);
                }
            }

            $data_warehouse_user = [
                'order_prepared' => $orders_prepared,
                'order_finished' => $orders_finished,
                'products' => $products,
            ];
        }

        return view('admin.accountDetails', ['user' => $user, 'type' => $type, 'histories' => $histories, 'status' => __('status'), 'parameter' => $parameter, 
        'accessAccount' => $accessAccount, 'data_warehouse_user' => isset($data_warehouse_user) ? $data_warehouse_user : false ]);
    }

    public function updateImageProfil(Request $request){

        $need_validate = true;
        if($request->post('browse_image')){
            $request->validate([
                'browse_image' => 'required|file|mimes:jpg,jpeg,png|max:2048', 
            ]);
          
        } else {
            $need_validate = false;
        }

        if ($request->hasFile('browse_image') && $request->file('browse_image')->isValid() || $need_validate == false) {
            $image_parts = explode(";base64,", $request->post('cropped_image_data'));
            $extension = explode('/', mime_content_type($request->post('cropped_image_data')))[1];
            $image_base64 = base64_decode($image_parts[1]);
            $imageName = $request->post('user_id').'.'.$extension;
            $store = Storage::disk('local')->put('images/'.$imageName, $image_base64);

            if($store){
                // Save to database
                if($this->users->updatePictureById($request->post('user_id'), $imageName)){

                    // Save to Auth Session
                    if(Auth()->user()->id == $request->post('user_id')){
                        Auth()->user()->picture = $imageName;
                    }
                    return redirect()->back()->with('success', 'Image de profil modifiée avec succès !');
                }
            }
        }
    }

    public function updateAccountDetails(Request $request){

        $data = $request->all();

        if($data['email'] == "" || $data['name'] == ""){
            return redirect()->back()->with('error', 'Veuillez renseigner les champs obligatoires');
        }
  
        // Actual password missing
        if(($data['new_password'] || $data['new_password2']) && $data['password'] == null){
            return redirect()->back()->with('error', 'Veuillez renseigner votre mot de passe actuel');
        } else if($data['new_password'] != $data['new_password2']){
            return redirect()->back()->with('error', 'Les mots de passes sont différents !');
        } else if($data['new_password'] && $data['new_password2'] && $data['password']){

            // Check password
            $user = $this->users->getUserById(Auth()->user()->id);
            $user = count($user) > 0 ? $user[0] : [];

            $pass_check = Hash::check($data['password'], $user['password']);
            if(!$pass_check){
                return redirect()->back()->with('error', 'Mot de passe actuel incorrect !');
            } else {

                // Update data user
                $data_to_update = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['new_password'])
                ];

                if($this->users->updateUserDetails(Auth()->user()->id, $data_to_update)){
                    return redirect()->back()->with('success', 'Profil modifié avec succès !');
                } else {
                    return redirect()->back()->with('error', 'Oops, une erreur est survenue !');
                }
            }

        } else {
            
            if(str_contains($data['email'], '@') && (str_contains($data['email'], '.fr') || str_contains($data['email'], '.com'))){
                
                // Update only email or name
                $data_to_update = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                ];

                Auth()->user()->name = $data['name'];
                Auth()->user()->email = $data['email'];

                if($this->users->updateUserDetails(Auth()->user()->id, $data_to_update)){
                    return redirect()->back()->with('success', 'Profil modifié avec succès !');
                } else {
                    return redirect()->back()->with('error', 'Oops, une erreur est survenue !');
                }
            } else {
                return redirect()->back()->with('error', 'Email au mauvais format !');
            }
          
        }
    }
}
