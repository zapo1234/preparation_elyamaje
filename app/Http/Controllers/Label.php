<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Service\Api\Colissimo;
use App\Repository\Bordereau\BordereauRepository;
use Illuminate\Support\Facades\Response;
use App\Repository\Label\LabelRepository;
use App\Repository\Order\OrderRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Label extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $label;
    private $colissimo;
    private $bordereau;
    private $order;

    public function __construct(
        LabelRepository $label, 
        Colissimo $colissimo,
        BordereauRepository $bordereau,
        OrderRepository $order
    ){
        $this->label = $label;
        $this->colissimo = $colissimo;
        $this->bordereau = $bordereau;
        $this->order = $order;
    }

    public function getlabels(){
        $orders_labels = $this->order->getOrderAndLabel();
        return view('labels.label', ['orders_labels' => $orders_labels]);
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
        $order_id = $request->post('order_id');
        $label_id = $request->post('label_id');

        if(is_int($this->colissimo->deleteOutwardLabelWordPress($order_id))){
            if($this->label->deleteLabelById($label_id)){
                return redirect()->route('labels')->with('success', 'Étiquette supprimée avec succès !');
            } else {
                return redirect()->route('labels')->with('error', 'Erreur de suppression d\'étiquette !');
            }
        } else {
            return redirect()->route('labels')->with('error', 'Erreur de suppression d\'étiquette Woocommerce !');
        }
    }

    public function bordereaux(){
        $bordereaux = $this->bordereau->getBordereaux()->toArray();
        $bordereaux_array = [];

        $ids = array_column($bordereaux, "parcel_number");
        foreach($bordereaux as $bordereau){
            $clesRecherchees = array_keys($ids,  $bordereau['parcel_number']);
            $newDate = date("d/m/Y", strtotime($bordereau['created_at']));  
        
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
            return redirect()->route('labels')->with('error', 'Bordereau déjà généré ou aucune étiquette pour cette date !');
        } else {
            $bordereau = $this->colissimo->generateBordereauByParcelsNumbers($parcelNumbers_array);

            
            if($bordereau['<jsonInfos>']['messages'][0]['messageContent'] == "La requête a été traitée avec succès"){
                // Enregistre le bordereau_id dans la table labels liés aux parcelNumber
                $bordereau_id = $bordereau['<jsonInfos>']['bordereauHeader']['bordereauNumber'];
                $this->label->saveBordereau($bordereau_id, $parcelNumbers_array);
                $this->bordereau->save($bordereau_id, $bordereau['<deliveryPaper>']);
    
                $pdf = $bordereau['<deliveryPaper>'];
                $headers = [
                    'Content-Type' => 'application/pdf',
                ];
            
                // Renvoyer le contenu en tant que réponse
                return Response::make($pdf, 200, $headers);
            } else {
                return redirect()->route('labels')->with('error', $bordereau['<jsonInfos>']['messages'][0]['messageContent']);
            }
        }
    }

    public function bordereauPDF(Request $request){
        $blob = $this->bordereau->getBordereauById($request->post('bordereau_id'));
        $headers = [
            'Content-Type' => 'application/pdf',
        ];

        // $filename = basename('Bordereau(' . $request->post('bordereau_id') . ').pdf');
    
        // Renvoyer le contenu blob en tant que réponse
        return Response::make($blob[0]->bordereau, 200, $headers);
    }
}
