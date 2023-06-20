<?php

namespace App\Http\Service\Api;

use Exception;
use Illuminate\Support\Facades\Http;

class Colissimo
{

    public function generateLabel($order, $weight, $order_id){
        
        $productCode_array = [
            'lpc_expert'     => 'DOS',
            'lpc_nosign'     => 'DOM',
            'lpc_sign'       => 'DOS',
            'local_pickup'   => false
        ];

        $nonMachinable = ["BPR", "A2P", "BDP", "CMT"];
        $productCode = $order['product_code'] ?? $productCode_array[$order['shipping_method']];

        if($productCode){
            try {
                $requestParameter = [
                    'contractNumber' => config('app.colissimo_contractNumber'),
                    'password' => config('app.colissimo_password'),
                    'outputFormat' => [
                        'x' => 0,
                        'y' => 0,
                        'outputPrintingType' => 'PDF_A4_300dpi',
                    ],
                    'letter' => [
                        'service' => [
                            'productCode' =>  $productCode,
                            'depositDate' => date('Y-m-d'), // Date du dépôt du colis
                            'orderNumber ' => $order_id,
                            'commercialName' => $order['shipping']['last_name'].' '.$order['shipping']['first_name'],
                            'returnTypeChoice' => 3 // Ne pas retourner
                        ],
                        'parcel' => [
                            'weight' => $weight, // Poids du colis
                            'insuranceValue' => $order['total_order'] * 100,
                            'nonMachinable' => in_array($productCode, $nonMachinable) ? false : true, //  // Format du colis, true pour non standard
                            'pickupLocationId' => $order['pick_up_location_id'] ?? null
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
                        'customsDeclarations' => [
                            'includeCustomsDeclarations' => 1,
                            'invoiceNumber'              => $order_id,
                            'original' => [
                                'originalInvoiceNumber' => $order_id,
                                'originalInvoiceDate' => $order['date']
                            ],
                        ],
                        'addressee' => [
                            'addresseeParcelRef' => $order_id,
                            'codeBarForReference' => false,
                            'address' => [
                                'lastName' => $order['shipping']['last_name'],
                                'firstName' => $order['shipping']['first_name'],
                                'line2' => $order['shipping']['address_1'],
                                'countryCode' =>$order['shipping']['country'],
                                'city' => $order['shipping']['city'],
                                'zipCode' => $order['shipping']['postcode'],
                                'email' => $order['billing']['email'],
                                'mobileNumber' =>  $order['billing']['phone']
                            ]
                        ]
                    ]
                ];


                

                $url = "https://ws.colissimo.fr/sls-ws/SlsServiceWSRest/2.0/generateLabel";
                $data = $requestParameter;

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json'
                ])->post($url, $data);
        
                preg_match('/--(.*)\b/', $response, $boundary);
        
                $content = empty($boundary)
                ? $this->parseMonoPartBody($response)
                : $this->parseMultiPartBody($response, $boundary[0]);

                $label = isset($content['<label>']) ? mb_convert_encoding($content['<label>'], 'UTF-8', 'ASCII') : false;

                if(!$label){
                    return $content['<jsonInfos>']['messages'][0]['messageContent'];
                }

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
                    
                } else {

                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        } else {
           return 'Impossible de générer une étiquette pour ce choix d\'expédition';
        }
    }


    public function generateBordereauByParcelsNumbers($parcelNumbers_array){


            $request = [
                'contractNumber'                    => config('app.colissimo_contractNumber'),
                'password'                          => config('app.colissimo_password'),
                'generateBordereauParcelNumberList' => [
                    'parcelsNumbers' => [
                        implode(',',$parcelNumbers_array)
                    ]
                ]
            ];


          
            try {
                $url = "https://ws.colissimo.fr/sls-ws/SlsServiceWSRest/2.0/generateBordereauByParcelsNumbers";
    
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json'
                ])->post($url, $request);

                preg_match('/--(.*)\b/', $response, $boundary);
    
                $content = empty($boundary)
                ? $this->parseMonoPartBody($response)
                : $this->parseMultiPartBody($response, $boundary[0]);

                if($content['<jsonInfos>']['messages'][0]['messageContent'] == "La requête a été traitée avec succès"){
                    return $content;
                } else {
                    return isset($content) ? $content : false;
                }
              
            } catch(Exception $e) {
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


    public function deleteOutwardLabelWordPress($order_id){

        $customer_key = config('app.woocommerce_customer_key');
        $customer_secret = config('app.woocommerce_customer_secret');
      
        try {
            $response = Http::withBasicAuth($customer_key, $customer_secret) 
                ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/colissimo/delete", [
                    'data' => $order_id
                ]); 
            return $response->json();
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

