<?php

namespace App\Http\Controllers;
use App\Http\Service\Api\Api;
use App\Repository\Distributor\DistributorRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Distributors extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $distributor;
    private $api;

    public function __construct(DistributorRepository $distributor, Api $api){
        $this->distributor = $distributor;
        $this->api = $api;
    }

    public function getAllDistributors(){

        $roles =  $this->api->getListRole();
        $roles = $roles['routes']['/wc/v3/customers']['endpoints'][0]['args']['role']['enum'];
        $list_role_distributeur = [];
        $insert_distributeurs = [];

        foreach($roles as $role){
            if(str_contains($role, 'tributeur')){
                $list_role_distributeur[] = $role;
            }
        }

        foreach($list_role_distributeur as $role){
            $per_page = 100;
            $page = 1;
            $distributeurs = $this->api->getDistributeurs($per_page, $page, $role);
            $count = count($distributeurs);

            // Check if others page
            if($count == 100){
              while($count == 100){
                $page = $page + 1;
                $distributeurs_other = $this->api->getDistributeurs($per_page, $page, $role);
            
                if(count($distributeurs_other ) > 0){
                  $distributeurs = array_merge($distributeurs, $distributeurs_other);
                }
            
                $count = count($distributeurs_other);
              }
            }  

            foreach($distributeurs as $distributeur){
                $insert_distributeurs [] = [
                    'customer_id' => $distributeur['id'],
                    'first_name' => $distributeur['first_name'] != "" && $distributeur['first_name'] != null ? $distributeur['first_name'] : $distributeur['username'],
                    'last_name' => $distributeur['last_name'],
                    'role' => $distributeur['role'],
                ];
            }
        }


        $sync = $this->distributor->insertDistributorsOrUpdate($insert_distributeurs);

        if($sync){
            return redirect()->route('distributors')->with('success', 'Distributeurs synchronisés avec succès !');
        } else {
            return redirect()->route('distributors')->with('error', $sync);
        }
    }
}
