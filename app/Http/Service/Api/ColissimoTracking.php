<?php

namespace App\Http\Service\Api;

use Exception;
use Illuminate\Support\Facades\Http;

class ColissimoTracking
{
    public function getStatus($trackingNumbers){
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
                dd($e->getMessage());
            }
    
            if ($response->status() === 200) {
                $responseData = $response->json();
                $orders_status[] = [
                    'order_id' => $trackingNumber->order_id,
                    'step' => 0,
                ];
                foreach($responseData['parcel']['step'] as $step){
                    if($step['status'] == "STEP_STATUS_ACTIVE"){
                        $orders_status[$key]['step'] = $step['stepId'];
                        $orders_status[$key]['message'] = isset($step['labelShort']) ? $step['labelShort'] : '';
                    }
                }
            }
        }

        return $orders_status;
    }
}
