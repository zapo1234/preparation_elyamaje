<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Repository\History\HistoryRepository;
use App\Repository\Printer\PrinterRepository;
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
    private $history;
    private $printer;

    public function __construct(
        Api $api,
        ReassortRepository $reassort,
        HistoryRepository $history,
        PrinterRepository $printer
    ){
        $this->api = $api;
        $this->reassort = $reassort;
        $this->history = $history;
        $this->printer = $printer;
    }

    public function kit(){
        $kits = $this->reassort->getAllKits();
        $printer = $this->printer->getPrinterByUser(Auth()->user()->id);
        $sort = $this->groupProductsByName($kits);
        return view('preparateur.kits.kit', ['kits' => $sort, 'printer' => isset($printer[0]) ? $printer[0] : false]);
    }

    // Fonction pour regrouper les produits par noms similaires
    public function groupProductsByName($products) {

        $group_to_ignore = ['Carte', 'Kits-ls-académie-formation'];

        $groups = [
            ["name" => "Kits de la Prothésiste", "compare" => "Kit", "image" => "prothesiste.png"],
            ["name" => "Lot de limes", "compare" => "Lot", "image" => "Limes.png"],
            // ["name" => "Kits", "compare" => "Kits", "image" => "default_product.png"],
            ["name" => "Coffrets", "compare" => "Coffrets", "image" => "Coffrets.png"],
            ["name" => "Râpes", "compare" => "Rapes", "image" => "Rapes.png"],
            ["name" => "VSP", "compare" => "Gamme", "image" => "VSP.png"],
            ["name" => "Lovely Box", "compare" => "Lovely", "image" => "lovelybox.png"],
        ];

        // Sorting name asc
        usort($products, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        foreach ($products as $productId => $product) {
            if (!in_array(explode(" ", $product['name'])[0], $group_to_ignore) && !in_array($product['name'], $group_to_ignore)) {
                $found = false;
                foreach ($groups as &$group) {
                    similar_text(explode(" ", $product['name'])[0], $group['compare'], $percent);;
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
                        'image' => false,
                    ];
                }
            } 
        }

        return $groups;
    }

    public function kitPrepared(Request $request){
        $barcode_array = $request->post('pick_items');
        $products_quantity = $request->post('pick_items_quantity');
        $kit_id = $request->post('kit_id');
        $check_if_done = true;
        $total_product = 0;

        // Check if all products are picked
        $kits = $this->reassort->getAllKits();
        if(isset($kits[$kit_id])){
            foreach($kits[$kit_id]['children'] as $key => $kit){
                $total_product = $total_product + intval($kit['quantity']);

                $found = array_search($kit['barcode'], $barcode_array);
                if ($found !== false) {
                    if(intval($kit['quantity']) != intval($products_quantity[$key])){
                        $check_if_done = false;
                    }
                } else {
                    $check_if_done = false;
                }
            }

            if($check_if_done){
                // Insert to history
                $unique_id =  "KIT-".time();
                try{
                    $this->history->save([
                        'order_id' => $unique_id,
                        'user_id' => Auth()->user()->id,
                        'status' => 'finished',
                        'created_at' => date('Y-m-d H:i:s'),
                        'total_product' => $total_product ?? null,
                        'kit' => $kit_id

                    ]);

                    echo json_encode(["success" => true, 'user' => Auth()->user()->name, 'date' => date('d/m/Y H:i'), 'unique_id' => $unique_id]);
                    return;
                } catch (Exception $e){
                    echo json_encode(["success" => false, "message" => "Oops, une erreur est survenue !"]);
                    return;
                }
                
            } else {
                echo json_encode(["success" => false, "message" => "Des produits sont manquants !"]);
                return;
            }
        } else {
            echo json_encode(["success" => false, "message" => "Kit non trouvé !"]);
            return;
        }
       


    }
}