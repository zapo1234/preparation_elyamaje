<?php

namespace App\Http\Service\Api;

use Exception;
use Illuminate\Support\Facades\Http;

class Colissimo
{

    function generateLabel($order, $weight, $order_id){
        
        $productCode_array = [
            'lpc_expert'     => 'DOS',
            'lpc_nosign'     => 'DOM',
            'lpc_sign'       => 'DOS',
        ];

        try {
            $requestParameter = [
                'contractNumber' => config('app.colissimo_contractNumber'),
                'password' =>config('app.colissimo_password'),
                'outputFormat' => [
                    'x' => 0,
                    'y' => 0,
                    'outputPrintingType' => 'PDF_A4_300dpi',
                ],
                'letter' => [
                    'service' => [
                        'productCode' =>  $order['product_code'] ?? $productCode_array[$order['shipping_method']],
                        'depositDate' => date('Y-m-d'), // Date du dépôt du colis (au moins égale à la date actuelle)
                        'orderNumber ' => $order_id,
                        'commercialName' => $order['shipping']['last_name'].' '.$order['shipping']['first_name'],
                    ],
                    'parcel' => [
                      'weight' => $weight, // Poids du colis
                      'insuranceValue' => $order['total_order'] * 100,
                    ],
                    'sender' => [
                        'senderParcelRef' => $order_id,
                        'address' => [
                            'companyName' => config('app.companyName'),
                            'line2' => config('app.line2'),
                            'countryCode' => config('app.countryCode'),
                            'city' => config('app.city'),
                            'zipCode' => config('app.zipCode'),
                        ]
                    ],
                    'addressee' => [
                        'addresseeParcelRef' => true,
                        'codeBarForReference' => false,
                        'address' => [
                            'lastName' => $order['shipping']['last_name'],
                            'firstName' => $order['shipping']['first_name'],
                            'line2' => $order['shipping']['address_1'],
                            'countryCode' =>$order['shipping']['country'],
                            'city' => $order['shipping']['city'],
                            'zipCode' => $order['shipping']['postcode'],
                            'email' => $order['billing']['email'],
                        ]
                    ]
                ]
            ];

            $url = "https://ws.colissimo.fr/sls-ws/SlsServiceWSRest/2.0/generateLabel";
            $data = $requestParameter; // Remplacez les crochets par les données que vous souhaitez envoyer

            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post($url, $data);
       
            preg_match('/--(.*)\b/', $response, $boundary);
    
            $content = empty($boundary)
            ? $this->parseMonoPartBody($response)
            : $this->parseMultiPartBody($response, $boundary[0]);


            $label = mb_convert_encoding($content['<label>'], 'UTF-8', 'ASCII');
            $trackingNumber = isset($content['<jsonInfos>']['labelV2Response']['parcelNumber']) ? $content['<jsonInfos>']['labelV2Response']['parcelNumber'] : null;

            if($trackingNumber){
                $data = [
                    'order_id' => $order_id,
                    'label' => $label,
                    'label_format' => 'PDF',
                    'label_created_at' => date('Y-m-d h:i:s'),
                    'tracking_number' => $trackingNumber
                ];

                try{
                    return $this->postOutwardLabelWordPress($data);
                } catch(Exception $e){
                    return $e->getMessage();
                }
                
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    public function postOutwardLabelWordPress($data){

        $customer_key = config('app.woocommerce_customer_key');
        $customer_secret = config('app.woocommerce_customer_secret');
      
        try {
            $response = Http::withBasicAuth($customer_key, $customer_secret) 
                ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/colissimo/", [
                    'data' => $data
                ]); 

            if($response){
                $data['success'] = true;
            }

            return $data;
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    protected function parseMultiPartBody($body, $boundary) {
        $messages = array_filter(
            array_map(
                'trim',
                explode($boundary, $body)
            )
        );

        $parts = [];
        foreach ($messages as $message) {
            if ('--' === $message) {
                break;
            }

            if (strpos($message, "\r\n\r\n") === false) {
                return $message;
                continue;
            }

            $headers = [];
            [$headerLines, $body] = explode("\r\n\r\n", $message, 2);

            foreach (explode("\r\n", $headerLines) as $headerLine) {
                [$key, $value] = preg_split('/:\s+/', $headerLine, 2);
                $headers[strtolower($key)] = $value;
            }

            if (!empty($headers['content-type']) && 'application/json' === $headers['content-type']) {
                $body = json_decode($body, true);
            }

            if (!empty($headers['content-id'])) {
                $parts[$headers['content-id']] = '<jsonInfos>' === $headers['content-id']
                    ? json_decode($body, true)
                    : $body;
            }
        }

        return $parts;
    }

    protected function parseMonoPartBody($body) {
        return json_decode($body, true);
    }
 
}

