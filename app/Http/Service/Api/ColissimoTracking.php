<?php

namespace App\Http\Service\Api;

use Exception;
use Illuminate\Support\Facades\Http;

class ColissimoTracking
{
    public function getStatus($trackingNumbers, $details = false){
        $orders_status = [];

        foreach($trackingNumbers as $key => $trackingNumber){
            try {
                $data =[
                    'login' => config('app.colissimo_contractNumber'),
                    'password' => config('app.colissimo_password'),
                    'parcelNumber' => $trackingNumber->tracking_number,
                    'lang' => 'fr_FR'
                ];
                
                $url = "https://ws.colissimo.fr/tracking-timeline-ws/rest/tracking/timelineCompany/";
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json'
                ])->post($url, $data);


            } catch(Exception $e){
                return;
                // dd($e->getMessage());
            }

            try {

                if ($response->status() === 200) {

                    if($details){
                        $orders_status = $response->json();
                    } else {
                        $responseData = $response->json();
                        $orders_status[] = [
                            'order_id' => $trackingNumber->order_id,
                            'step' => 0,
                        ];
    
                        
                        if(isset($responseData['parcel'])){
                            foreach($responseData['parcel']['step'] as $step){
                                if($step['status'] == "STEP_STATUS_ACTIVE"){
                                    if(isset($step['labelShort']) && $step['stepId'] == 4){
                                        if($step['labelShort'] == "Votre colis vous attend dans votre point de retrait"){
                                            $step['stepId'] = 5;
                                        }
                                    }
                                    $orders_status[$key]['step'] = $step['stepId'];
                                    $orders_status[$key]['message'] = isset($step['labelShort']) ? $step['labelShort'] : '';
                                }
                            }
                        }
                    } 
                }
            } catch(Exception $e){
                return;
                // dd($e->getMessage());
            }
        }
       
        return $orders_status;
    }
}
