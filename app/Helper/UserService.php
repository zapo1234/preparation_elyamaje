<?php 

namespace App\Helper;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserService{
    public $email, $password;

    public function __construct($email, $password){
        $this->email = $email;
        $this->password = $password;
    }

    public function validateInput(){
        $validator = Validator::make(['email' => $this->email, 'password' => $this->password],
        [
            'email' => ['required', 'email:rfc,dns', 'exists:users'],
            'password' => ['required', 'string']
        ]);

        if($validator->fails()){
            return ['status' => false, 'emailError' => 'Email incorrect'];
        } else {
            return ['status' => true];
        }
    }

    public function login(){
        $validate = $this->validateInput();
        if($validate['status'] == false){
            return $validate;
        } else {
            $user = User::where('email', $this->email)->first();
            if(Hash::check($this->password, $user->password)){
                $token = $user->createToken('token')->plainTextToken;
                return ['status' => true, 'token' => $token, 'user' => $user];
            } else {
                return ['status' => false, 'passwordError' => 'Mot de passe incorrect'];
            }
        }
    }
}


?>