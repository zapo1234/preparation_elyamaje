<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Service\Api\Colissimo;
use Illuminate\Support\Facades\Response;
use App\Repository\Label\LabelRepository;
use App\Repository\Order\OrderRepository;
use App\Http\Service\Api\ColissimoTracking;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Service\Api\Chronopost\Chronopost;
use App\Repository\Bordereau\BordereauRepository;
use App\Repository\Colissimo\ColissimoRepository;
use App\Http\Service\Woocommerce\WoocommerceService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Repository\LabelProductOrder\LabelProductOrderRepository;
use App\Repository\LogError\LogErrorRepository;

class Label extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $label;
    private $colissimo;
    private $bordereau;
    private $order;
    private $woocommerce;
    private $labelProductOrder;
    private $colissimoConfiguration;
    private $chronopost;
    private $colissimoTracking;
    private $api;
    private $logError;

    public function __construct(
        LabelRepository $label, 
        Colissimo $colissimo,
        BordereauRepository $bordereau,
        OrderRepository $order,
        WoocommerceService $woocommerce,
        LabelProductOrderRepository $labelProductOrder,
        ColissimoRepository $colissimoConfiguration,
        Chronopost $chronopost,
        ColissimoTracking $colissimoTracking,
        LogErrorRepository $logError
    ){
        $this->label = $label;
        $this->colissimo = $colissimo;
        $this->bordereau = $bordereau;
        $this->order = $order;
        $this->woocommerce = $woocommerce;
        $this->labelProductOrder = $labelProductOrder;
        $this->colissimoConfiguration = $colissimoConfiguration;
        $this->chronopost = $chronopost;
        $this->colissimoTracking = $colissimoTracking;
        $this->logError = $logError;
    }

    public function getlabels(Request $request){


        if(count($request->all()) > 0){
            $filters = $request->all();
            $orders = $this->order->getAllOrdersAndLabelByFilter($filters)->toArray();
            $orders = json_encode($orders);
            $orders = json_decode($orders, true);
        } else {
            $orders = $this->order->getAllOrdersAndLabel()->toArray();
        }
       
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
                    'label_created_at' => $order['label_created_at'],
                    'label_format' => $order['label_format'],
                    'cn23' => $order['cn23'],

                ];
            } else if(count($clesRecherchees) > 0){
                $array_order[$order['order_woocommerce_id']]['labels'][$labels[$clesRecherchees[0]]['id']]= [
                    'label_id' => $labels[$clesRecherchees[0]]['id'],
                    'tracking_number' => $labels[$clesRecherchees[0]]['tracking_number'],
                    'label_created_at' => $labels[$clesRecherchees[0]]['created_at'], 
                    'label_format' => $labels[$clesRecherchees[0]]['label_format'], 
                    'cn23' => $labels[$clesRecherchees[0]]['cn23'], 
                ];
            }
        }
    
        // Liste des status commandes
        $status_list = __('status_order');
        return view('labels.label', ['orders' => $array_order, 'status_list' => $status_list, 'parameter' => $request->all()]);
    }

    public function labelDownload(Request $request){

        $label_format = $request->post('label_format');
        $order_id = $request->post('order_id');
        $blob = $this->label->getLabelById($request->post('label_id'));
        $fileContent = $blob[0]->label;

        if($label_format == "PDF"){
            $fileName = 'label_'.$order_id.'.pdf';
            $headers = [
                'Content-Type' => 'application/pdf',
            ];
        
            return Response::make($fileContent, 200, $headers)
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        } 
    }

    public function labelDownloadCn23(Request $request){
       
        $order_id = $request->post('order_id');
        $blob = $this->label->getLabelById($request->post('label_id'));
        // $fileContent = $blob[0]->cn23;

        $fileContent = mb_convert_encoding($blob[0]->cn23, 'ISO-8859-1');

        $fileName = 'declaration_'.$order_id.'.pdf';
        $headers = [
            'Content-Type' => 'application/pdf',
        ];
    
        return Response::make($fileContent, 200, $headers)
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }


    public function labelPrintZPL(Request $request){
        $label_id = $request->post('label_id');
        $blob = $this->label->getLabelById( $label_id);

        if(isset($blob[0]->label)){
            echo json_encode(['success' => true, 'file' => base64_encode($blob[0]->label)]);

        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function labelShow(Request $request){
        $blob = $this->label->getLabelById($request->post('label_id'));
        $colissimo = $this->colissimoConfiguration->getConfiguration();

        // Traitement selon format étiquette
        switch ($blob[0]->label_format) {
            case "PDF":
                $headers = [
                    'Content-Type' => 'application/pdf',
                ];
        
                $fileContent = $blob[0]->label;
                return Response::make($fileContent, 200, $headers);
                break;
            case "ZPL":
                $zpl = $blob[0]->label;
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, "http://api.labelary.com/v1/printers/8dpmm/labels/8x8/0/");
                curl_setopt($curl, CURLOPT_POST, TRUE);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $zpl);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/pdf")); // omit this line to get PNG images back
                $result = curl_exec($curl);

                curl_close($curl);
                $headers = [
                    'Content-Type' => 'application/pdf',
                ];

                return Response::make($result, 200, $headers);
                break;
        }
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
            $newDateLabel = date("d/m/Y", strtotime($bordereau['label_date']));  
            
            $bordereaux_array[$bordereau['parcel_number']] = [
                'parcel_number' => $bordereau['parcel_number'],
                'created_at' => $newDate,
                'label_date' => $newDateLabel,
                'number_order' => count($clesRecherchees)
            ];
        }
        return view('labels.bordereau', ['bordereaux' => array_values($bordereaux_array)]);
    }

    public function generateBordereau(Request $request){

        $date = $request->post('date');
        // Récupère l'ensemble des commandes en fonction de la date et qui n'ont pas de bordereau généré
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
                $this->bordereau->save($bordereau_id, $bordereau['<deliveryPaper>'], $date);
    
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

        if(isset($product_order[0])){
            if($product_order[0]['status'] != "finished"){
                echo json_encode(['success' => false, 'message' => 'Veuillez valider la commande avant']);
                return;
            }

            if($product_order[0]['shipping_method'] == null){
                echo json_encode(['success' => false, 'message' => 'Aucune méthode d\'expédition n\'a été trouvée pour cette commande']);
                return;
            }
        }
     
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
        $colissimo = $this->colissimoConfiguration->getConfiguration();
        $quantity_product = $request->post('quantity');

        if($order_by_id && $product_to_add_label){
            $order = $this->woocommerce->transformArrayOrder($order_by_id, $product_to_add_label);

            $weight = 0; // Kg
            $subtotal = 0;
           
                foreach($order[0]['line_items'] as $or){
                    if(isset($or['product_id'])){
                        $quantity = $quantity_product[$or['product_id']];
                        if(isset($or['real_price'] )){
                            $subtotal = $subtotal + $or['real_price'];
                        } else {
                            $subtotal = $subtotal + $or['subtotal'] * $quantity;
                        }

                        if(is_numeric($or['weight'])){
                            $weight = $weight + number_format(($or['weight'] * $quantity), 2);
                        }
                    }
                } 

                $order[0]['total_order'] = $subtotal;
                // Étiquette Chronopost
                if(str_contains($order[0]['shipping_method'], 'chrono')){
                    $labelChrono = $this->chronopost->generateLabelChrono($order[0], $weight, $order[0]['order_woocommerce_id'], count($colissimo) > 0 ? $colissimo[0] : null);
                    if(isset($labelChrono['success'])){
                        // Pas besoin pour étiquette PDF
                        if($labelChrono['label_format'] != "PDF"){
                            $labelChrono['label'] = mb_convert_encoding($labelChrono['label'], 'ISO-8859-1');
                        }
                        $insert_label = $this->label->save($labelChrono);
                        $insert_product_label_order = $this->labelProductOrder->insert($order_id, $insert_label, $product_to_add_label, $quantity_product);
                    } else {
                        return redirect()->route('labels')->with('error', $labelChrono);
                    }

                    return redirect()->route('labels')->with('success', 'Étiquette générée pour la commande '.$order[0]['order_woocommerce_id']);
                } else { 
                    // Étiquette Colissimo
                    $label = $this->colissimo->generateLabel($order[0], $weight, $order[0]['order_woocommerce_id'], count($colissimo) > 0 ? $colissimo[0] : null);
                
                    if(isset($label['success'])){
                      $label['label'] =  mb_convert_encoding($label['label'], 'ISO-8859-1');
                      $label['cn23'] != null ? mb_convert_encoding($label['cn23'], 'ISO-8859-1') : $label['cn23'];
                      $insert_label = $this->label->save($label);
                      $insert_product_label_order = $this->labelProductOrder->insert($order_id, $insert_label, $product_to_add_label, $quantity_product);
     
                      if(is_int($insert_label) && $insert_label != 0 && $insert_product_label_order){
                        
                        if($label['label']){
                            // switch ($label['label_format']) {
                            //     case "PDF":
                            //         $headers = [
                            //             'Content-Type' => 'application/pdf',
                            //         ];
                            
                            //         $fileContent = $label['label'];
                            //         return Response::make($fileContent, 200, $headers);
                            //         break;
                            //     case "ZPL":
                            //         $zpl = $label['label'];
                            //         $curl = curl_init();
                            //         curl_setopt($curl, CURLOPT_URL, "http://api.labelary.com/v1/printers/8dpmm/labels/8x8/0/");
                            //         curl_setopt($curl, CURLOPT_POST, TRUE);
                            //         curl_setopt($curl, CURLOPT_POSTFIELDS, $zpl);
                            //         curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                            //         curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/pdf")); // omit this line to get PNG images back
                            //         $result = curl_exec($curl);
                    
                            //         curl_close($curl);
                            //         $headers = [
                            //             'Content-Type' => 'application/pdf',
                            //         ];
                    
                            //         return Response::make($result, 200, $headers);
                            //         break;
                            // }

                            return redirect()->route('labels')->with('success', 'Étiquette générée pour la commande '.$order[0]['order_woocommerce_id']);
                        } 
                      } else {
                        return redirect()->route('labels')->with('error', 'Étiquette générée et disponible sur Woocommerce mais erreur base préparation');
                      }
                    } else {
                        return redirect()->route('labels')->with('error', $label);
                    }
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

    public function getTrackingLabelStatus($token){

        if($token =="XGMs6Rf3oqMTP9riHXls1d5oVT3mvRQYg7v4KoeL3bztj7mKRy"){

            // Get all orders labels -10 jours
            $rangeDate = 10;
            $labels = $this->label->getAllLabelsByStatusAndDate($rangeDate);
            $colissimo = [];
            $chronopost = [];


            foreach($labels as $label){
                if($label->origin == "colissimo"){
                    $colissimo[] = $label;
                } else if($label->origin == "chronopost"){
                    $chronopost[] = $label;
                }
            }

            try{
                $labels = $this->label->getAllLabelsByStatusAndDate($rangeDate);
                // Récupère les status de chaque commande
                // $trackingLabelColissimo = $this->colissimoTracking->getStatus($colissimo);
                $trackingLabelChronopost = $this->chronopost->getStatus($chronopost);
                // Update status sur Wordpress pour les colis livré
                //$update = $this->colissimo->trackingStatusLabel($trackingLabelColissimo);
                dd($trackingLabelChronopost);
                $update2 = $this->chronopost->trackingStatusLabel($trackingLabelChronopost);


                // $trackingLabel = array_merge($trackingLabelColissimo, $trackingLabelChronopost);
                // Update en local
                // $this->label->updateLabelStatus($trackingLabel);
                
                dd($update2);
                // return $update;
            } catch(Exception $e){
                $this->logError->insert(['order_id' => null, 'message' => 'Error function getTrackingLabelStatus '.$e->getMessage()]);
                // dd($e->getMessage());
            }
        }
    }
}
