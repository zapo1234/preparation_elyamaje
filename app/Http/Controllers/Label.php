<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Service\Api\Colissimo;
use Illuminate\Support\Facades\Response;
use App\Repository\Label\LabelRepository;
use App\Repository\Order\OrderRepository;
use App\Http\Service\Woocommerce\WoocommerceService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Repository\Bordereau\BordereauRepository;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Repository\LabelProductOrder\LabelProductOrderRepository;

class Label extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $label;
    private $colissimo;
    private $bordereau;
    private $order;
    private $woocommerce;
    private $labelProductOrder;

    public function __construct(
        LabelRepository $label, 
        Colissimo $colissimo,
        BordereauRepository $bordereau,
        OrderRepository $order,
        WoocommerceService $woocommerce,
        LabelProductOrderRepository $labelProductOrder
    ){
        $this->label = $label;
        $this->colissimo = $colissimo;
        $this->bordereau = $bordereau;
        $this->order = $order;
        $this->woocommerce = $woocommerce;
        $this->labelProductOrder = $labelProductOrder;
    }

    public function getlabels(){
        // Liste des commandes
        $orders = $this->order->getAllOrdersAndLabel()->toArray();
        $labels = $this->label->getAllLabels()->toArray();
        $array_order = [];

        foreach($orders as $order){
            if(!isset($array_order[$order['order_woocommerce_id']])){
                $array_order[$order['order_woocommerce_id']][] = $order;
            }

            $ids = array_column($labels, "order_id");
            $clesRecherchees = array_keys($ids,  $order['order_woocommerce_id']);
            
            if(!isset($array_order[$order['order_woocommerce_id']]['labels'][$order['label_id']]) && $order['label_id']){
                $array_order[$order['order_woocommerce_id']]['labels'][$order['label_id']]= [
                    'label_id' => $order['label_id'],
                    'tracking_number' => $order['tracking_number'],
                    'label_created_at' => $order['label_created_at']
                ];
            } else if(count($clesRecherchees) > 0){
                $array_order[$order['order_woocommerce_id']]['labels'][$labels[$clesRecherchees[0]]['id']]= [
                    'label_id' => $labels[$clesRecherchees[0]]['id'],
                    'tracking_number' => $labels[$clesRecherchees[0]]['tracking_number'],
                    'label_created_at' => $labels[$clesRecherchees[0]]['created_at']
                ];
            }
        }

        // Liste des status commandes
        $status_list = __('status_order');

        return view('labels.label', ['orders' => $array_order, 'status_list' => $status_list]);
    }

    public function labelPDF(Request $request){
      
        $order_id = $request->post('order_id');
        $blob = $this->label->getLabelById($request->post('label_id'));
        $fileContent = $blob[0]->label;
        $fileName = 'label_'.$order_id.'.pdf';
    
        $headers = [
            'Content-Type' => 'application/pdf',
        ];
    
        return Response::make($fileContent, 200, $headers)
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    public function labelShow(Request $request){
        $blob = $this->label->getLabelById($request->post('label_id'));
        $headers = [
            'Content-Type' => 'application/pdf',
        ];

        $fileContent = $blob[0]->label;
    
        return Response::make($fileContent, 200, $headers);
    }

    public function labelDelete(Request $request){
        $tracking_number = $request->post('tracking_number');
        $label_id = $request->post('label_id');

        if(is_int($this->colissimo->deleteOutwardLabelWordPress($tracking_number))){
            // Update des champs products_order à null et delete label
            if($this->label->deleteLabelByTrackingNumber($tracking_number)){
                $this->labelProductOrder->deleteLabelProductOrderById($label_id);
                return redirect()->route('labels')->with('success', 'Étiquette supprimée avec succès !');
            } else {
                return redirect()->route('labels')->with('error', 'Erreur de suppression d\'étiquette !');
            }
        } else {
            return redirect()->route('labels')->with('error', 'Erreur de suppression d\'étiquette Woocommerce !');
        }
    }

    public function bordereauDelete(Request $request){
        $parcel_number = $request->post('parcel_number');
        // Delete bordereau
        $delete_bordereau = $this->bordereau->deleteBordereauByParcelNumber($parcel_number);
        // Update label bordereau_id to null
        $update_label = $this->label->updateLabelBordereau($parcel_number);

        if(is_int($delete_bordereau) && is_int($update_label)){
            return redirect()->route('bordereaux')->with('success', 'Le bordereau a été supprimé avec succès !');
        } else {
            return redirect()->route('bordereaux')->with('error', 'Le bordereau n\'a pas pu être supprimé !');
        }
    }

    public function bordereaux(){
        $bordereaux = $this->bordereau->getBordereaux()->toArray();
        $bordereaux_array = [];

        $ids = array_column($bordereaux, "parcel_number");
        foreach($bordereaux as $bordereau){
            $clesRecherchees = array_keys($ids,  $bordereau['parcel_number']);
            $newDate = date("d/m/Y", strtotime($bordereau['bordereau_created_at']));  
        
            $bordereaux_array[$bordereau['parcel_number']] = [
                'parcel_number' => $bordereau['parcel_number'],
                'created_at' => $newDate,
                'number_order' => count($clesRecherchees)
            ];
        }
        return view('labels.bordereau', ['bordereaux' => array_values($bordereaux_array)]);
    }

    public function generateBordereau(Request $request){

        $date = $request->post('date');
        // Récupère l'ensemnle des commandes en fonction de la date et qui n'ont pas de bordereau généré
        $parcelNumbers = $this->label->getParcelNumbersyDate($date);
        $parcelNumbers_array = [];

        foreach($parcelNumbers as $parcel){
            $parcelNumbers_array[] = $parcel->tracking_number;
        }

        if(count($parcelNumbers_array) == 0){
            return redirect()->route('bordereaux')->with('error', 'Bordereau déjà généré ou aucune étiquette pour cette date !');
        } else {
            $bordereau = $this->colissimo->generateBordereauByParcelsNumbers($parcelNumbers_array);

            
            if($bordereau['<jsonInfos>']['messages'][0]['messageContent'] == "La requête a été traitée avec succès"){
                // Enregistre le bordereau_id dans la table labels liés aux parcelNumber
                $bordereau_id = $bordereau['<jsonInfos>']['bordereauHeader']['bordereauNumber'];
                $this->label->saveBordereau($bordereau_id, $parcelNumbers_array);
                $this->bordereau->save($bordereau_id, $bordereau['<deliveryPaper>']);
    
                // $pdf = $bordereau['<deliveryPaper>'];
                // $headers = [
                //     'Content-Type' => 'application/pdf',
                // ];
            
                // Renvoyer le contenu en tant que réponse
                return redirect()->route('bordereaux')->with('success', 'Borderau généré avec succès !');
                // return Response::make($pdf, 200, $headers);
            } else {
                return redirect()->route('bordereaux')->with('error', $bordereau['<jsonInfos>']['messages'][0]['messageContent']);
            }
        }
    }

    public function bordereauPDF(Request $request){
        $blob = $this->bordereau->getBordereauById($request->post('bordereau_id'));
        $headers = [
            'Content-Type' => 'application/pdf',
        ];

        // Renvoyer le contenu blob en tant que réponse
        return Response::make($blob[0]->bordereau, 200, $headers);
    }

    public function getProductOrderLabel(Request $request){
        $order_id = $request->post('order_id');

        $product_order = $this->order->getProductOrder($order_id)->toArray();
        $label_product_order = $this->labelProductOrder->getLabelProductOrder($order_id)->toArray();
        $column = array_column($label_product_order, "product_id");

        foreach($product_order as $key => $product){
            $product_found = array_keys($column,  $product['product_woocommerce_id']);
            if(count($product_found) > 0){
                $quantity = 0;
                foreach($product_found as $found){
                    $quantity = $quantity + $label_product_order[$found]['quantity'];
                }
                $product_order[$key]['total_quantity'] = $quantity;
            } else {
                $product_order[$key]['total_quantity'] = 0;
            }
        }

        if(count($product_order) > 0){
            echo json_encode(['success' => true, 'products_order' => $product_order]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Impossible de générer une étiquette']);
        }
    }

    public function generateLabel(Request $request){
        $product_to_add_label = $request->post('label_product');
        $order_id = $request->post('order_id');
        $order_by_id = $this->order->getOrderById($order_id);
        $quantity_product = $request->post('quantity');
        

        if($order_by_id && $product_to_add_label){
            $order = $this->woocommerce->transformArrayOrder($order_by_id, $product_to_add_label);
            $weight = 0; // Kg
            $subtotal = 0;
            
                foreach($order[0]['line_items'] as $or){
                    
                  $quantity = $quantity_product[$or['product_id']];
                  $subtotal = $subtotal + $or['subtotal'] * $quantity;
                  $weight = $weight + number_format(($or['weight'] * $quantity), 2);
                } 

                $order[0]['total_order'] = $subtotal;
                $label = $this->colissimo->generateLabel($order[0], $weight, $order[0]['order_woocommerce_id']);
                
                if(isset($label['success'])){
                  $label['label'] =  mb_convert_encoding($label['label'], 'ISO-8859-1');
                  $insert_label = $this->label->save($label);
                  $insert_product_label_order = $this->labelProductOrder->insert($order_id, $insert_label, $product_to_add_label, $quantity_product);
 
                  if(is_int($insert_label) && $insert_label != 0 && $insert_product_label_order){
                    
                    if($label['label']){
                      return redirect()->route('labels')->with('success', 'Étiquette générée pour la commande '.$order[0]['order_woocommerce_id']);
                    } 
                  } else {
                    return redirect()->route('labels')->with('error', 'Étiquette générée et disponible sur Woocommerce mais erreur base préparation');
                  }
                } else {
                    return redirect()->route('labels')->with('error', $label);
                }
        } else {
            return redirect()->route('labels')->with('error', "Veuillez séléctionner des produits");
        }
        

        // if($order){
        //     $weight = 0; // Kg
    
        //     foreach($order[0]['line_items'] as $or){
        //       $weight = $weight + ($or['weight'] *$or['quantity']);
        //     } 
    
        //     $label = $this->colissimo->generateLabel($order[0], $weight, $order[0]['order_woocommerce_id']);
        //     // $label['label'] = file_get_contents('labelPDF.pdf');
    
        //     if(isset($label['success'])){
        //       $label['label'] =  mb_convert_encoding($label['label'], 'ISO-8859-1');
        //       if($this->label->save($label)){
        //         if($label['label']){
        //           echo json_encode(['success' => true, 'message'=> 'Étiquette générée pour la commande '.$order[0]['order_woocommerce_id']]);
        //         } 
        //       } else {
        //         echo json_encode(['success' => false, 'message'=> 'Étiquette générée et disponible sur Woocommerce mais erreur base préparation']);
        //       }
        //     } else {
        //       echo json_encode(['success' => false, 'message'=> $label]);
        //     }
        // }

    }
}
