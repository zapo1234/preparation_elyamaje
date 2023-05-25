<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repository\User\UserRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class User extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $users;
   
    public function __construct( UserRepository $users){
        $this->users = $users;
    }
    
    public function updateRole(Request $request){
        $user_id = $request->post('user_id');
        $role_id = $request->post('role_id');
        echo json_encode(['success' => $this->users->updateRoleByUser($user_id, $role_id)]);
    }  
}
