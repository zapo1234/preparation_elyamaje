<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Repository\Categorie\CategoriesRepository;
use App\Repository\History\HistoryRepository;
use App\Repository\Role\RoleRepository;
use App\Repository\User\UserRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;

class Admin extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $api;
    private $category;
    private $user;
    private $role;
    private $history;

    public function __construct(
        Api $api, 
        CategoriesRepository $category,
        UserRepository $user, 
        RoleRepository $role,
        HistoryRepository $history
    ){
        $this->api = $api;
        $this->category = $category;
        $this->user = $user;
        $this->role = $role;
        $this->history = $history;
    }

    
    public function syncCategories(){
        $per_page = 100;
        $page = 1;
        $categories = $this->api->getAllCategories($per_page, $page);
        $count = count($categories);
        $insert_categories = [];

        // Check if others page
        if($count == 100){
          while($count == 100){
            $page = $page + 1;
            $categories_other = $this->api->getAllCategories($per_page, $page);
           
            if(count($categories_other ) > 0){
              $categories = array_merge($categories, $categories_other);
            }
          
            $count = count($categories_other);
          }
        }  


        foreach($categories as $category){
            $insert_categories [] = [
                'name' => $category['name'],
                'category_id_woocommerce' => $category['id'],
                'parent_category_id' => $category['parent'],

            ];
        }

        $sync = $this->category->insertCategoriesOrUpdate($insert_categories);

        if($sync){
            return redirect()->route('admin.configuration')->with('success', 'Catégories synchronisées avec succès !');
        } else {
            return redirect()->route('admin.configuration')->with('error', $sync);
        }
    }

    public function updateOrderCategory(Request $request){
        $id = $request->post('id');
        $order_display = $request->post('order_display');
        $parent = $request->post('parent');
        echo json_encode(['success' => $this->category->updateCategoryOrder($id, $order_display, $parent)]);
    }

    public function account(){
        $users = $this->user->getUsersAndRoles();
        $rolesUser =  Auth()->user()->roles->toArray();

        $ids = array_column($rolesUser, "id");
        $isAdmin = count(array_keys($ids,  1)) > 0 ? true : false;
        $roles = $this->role->getRoles();
        
        return view('admin.account', ['users' => $users, 'roles' => $roles, 'isAdmin' => $isAdmin]);
    }

    public function analytics(){
        return view('admin.analytics');
    }

    public function getAnalytics(Request $request){
        $date = $request->get('date') != "false" ? $request->get('date') : date('Y-m-d');
        $histories = $this->history->getHistory($date);
        $name = [];
        $prepared_count = [];
        $finished_count = [];

        foreach($histories as $history){
            $name[] = $history['name'];
            $prepared_count[] = $history['prepared_count'];
            $finished_count[] = $history['finished_count'];
        }

        echo json_encode(['name' => $name, 'prepared_count' => $prepared_count, 'finished_count' => $finished_count]);

    }
}
