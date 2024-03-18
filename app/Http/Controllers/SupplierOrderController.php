<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupplierOrderService;


class SupplierOrderController extends Controller
{
    protected $supplierOrderService;

    public function __construct(SupplierOrderService $supplierOrderService)
    {
        $this->supplierOrderService = $supplierOrderService;
    }

    public function listeSupplierorders()
    {

        // supplierorders
        $datasOrders = array();
        $listeOrders = $this->supplierOrderService->getAllSupplierOrders();


        foreach ($listeOrders as $key => $order) {
            // dd($order);
             // Remplissage des lines
            $tabStatus = [
            0 => '<span class="badge bg-light text-dark" style="padding: 0.7em 1em;font-weight:200;border: 1px solid;">Brouillon</span>', 
            1 => '<span class="badge bg-warning text-dark" style="padding: 0.7em 1em;font-weight:200">Validée</span>', 
            2 => '<span class="badge bg-success" style="padding: 0.7em 1em;font-weight:200">Approuvée</span>',
            3 => '<span class="badge bg-secondary" style="padding: 0.7em 1em;font-weight:200">Commandée</span>',
            4 => '<span class="badge bg-primary" style="padding: 0.7em 1em;font-weight:200">Reçu partiellement</span>',
            5 => '<span class="badge bg-dark" style="padding: 0.7em 1em;font-weight:200">Produits reçu</span>', 
            6 => '<span class="badge bg-danger" style="padding: 0.7em 1em;font-weight:200">Annulée</span>', 
            7 => '<span class="badge bg-danger" style="padding: 0.7em 1em;font-weight:200">Annulée</span>', 
            9 => '<span class="badge bg-danger" style="padding: 0.7em 1em;font-weight:200">Refusée</span>', 

            ];


            $lines = array();
            foreach ($order["lines"] as $k => $line) {
                array_push($lines,[
                    'ref_fornisseur' => $line["ref_supplier"]? $line["ref_supplier"] : "Aucune",
                    'ref_product' => $line["ref"],
                    'libelle_product' => $line["libelle"],
                    'product_barcode' => $line["product_barcode"],
                    'qty' => $line["qty"],
                    'fk_product' => $line["fk_product"],
                    'total_ht' => round($line["total_ht"],2),
                    'total_ttc' => round($line["total_ttc"],2),
                ]);
            }


            array_push($datasOrders,[
                'id' => $order["id"],
                'ref' => $order["ref"],
                'ref_supplier' => $order["ref_supplier"],
                'statut' => $tabStatus[$order["statut"]],
                'total_ht' => round($order["total_ht"],2),
                'total_ttc' => round($order["total_ttc"],2),
                'lines' => $lines,
                'private_note' => str_replace(array("\n", "\r"), '', $order["note_private"]),
                'date_commande' => $order["date_commande"]? date('d/m/y', $order["date_commande"]) : "sans_date",
                'date_livraison' =>$order["date_livraison"]? date('d/m/y', $order["date_livraison"]) : "sans_date",
                'delivery_date' => $order["delivery_date"]? date('d/m/y', $order["delivery_date"]) : "sans_date",

            ]);

           
        }

        return view('admin.supplyOrder',
            [
                "datasOrders" => $datasOrders,
                              
            ]);
    }

    // Autres méthodes du contrôleur...
}