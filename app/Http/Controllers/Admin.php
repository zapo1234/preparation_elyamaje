<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Repository\Categorie\CategoriesRepository;
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

    public function __construct(Api $api, CategoriesRepository $category){
        $this->api = $api;
        $this->category = $category;
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
        echo json_encode(['success' => $this->category->updateCategoryOrder($id, $order_display)]);
    }
}
