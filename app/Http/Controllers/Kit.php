<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Repository\Reassort\ReassortRepository;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class Kit extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $api; 
    private $reassort;

    public function __construct(
        Api $api,
        ReassortRepository $reassort
    ){
        $this->api = $api;
        $this->reassort = $reassort;
    }

    public function kit(){
        $kits = $this->reassort->getAllKits();
        $sort = $this->groupProductsByName($kits);
        return view('preparateur.kits.kit', ['kits' => $sort]);
    }

    // Fonction pour regrouper les produits par noms similaires
    public function groupProductsByName($products) {

        $groups = [
            ["name" => "Lot de limes", "compare" => "Lot", "image" => "Limes.png"],
            ["name" => "Kits", "compare" => "Kits", "image" => "default_product.png"],
            ["name" => "Kits de la Prothésiste", "compare" => "prothésiste", "image" => "prothesiste.png"],
            ["name" => "Coffrets", "compare" => "Coffrets", "image" => "Coffrets.png"],
            ["name" => "Râpes", "compare" => "Rapes", "image" => "Rapes.png"],
            ["name" => "VSP", "compare" => "Gamme", "image" => "VSP.png"],
        ];

        foreach ($products as $productId => $product) {
            $found = false;
            foreach ($groups as &$group) {
                similar_text(explode(" ", $product['name'])[0], $group['compare'], $percent);
             
                if ($percent > 50) {
                    $group['kits'][] = $product;
                    $found = true;
                    break;
                } 
            }
            if (!$found) {
                $groups[] = [
                    'name' => explode(" ", $product['name'])[0],
                    'kits' => [$product],
                    "compare" => explode(" ", $product['name'])[0],
                    'image' => false
                ];
            }
        }

        return $groups;
    }
}