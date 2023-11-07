<?php

namespace App\Http\Service\Api;

use Exception;
use Illuminate\Support\Facades\Http;
use App\Repository\Label\LabelRepository;
use App\Repository\Bordereau\BordereauRepository;

class Colissimo
{
    private $label;
    private $bordereau;

    public function __construct(
        LabelRepository $label, 
        BordereauRepository $bordereau
    ){
        $this->label = $label;
        $this->bordereau = $bordereau;
    }

    public function generateLabel($order, $weight, $order_id, $colissimo, $items){

        $productCode = $this->getProductCode($order);
        // $isCN22 = $this->isCn22($order['total_order'], $weight);
        // $isCN23 = $this->isCn23($order['total_order'], $weight);
        $customsArticle = $this->customsArticle($order, $items);

        // $nonMachinable = $this->isMachinable($productCode);
        $insuranceValue = $this->getInsuranceValue($productCode, $order);
        $format = $colissimo ? $colissimo->format : "PDF_A4_300dpi";
        $address = $this->getAddress($order);


        if($productCode){
            try {
                $requestParameter = [
                    'contractNumber' => config('app.colissimo_contractNumber'),
                    'password' => config('app.colissimo_password'),
                    'outputFormat' => [
                        'x' => 0,
                        'y' => 0,
                        'outputPrintingType' => $format,
                    ],
                    'letter' => [
                        'service' => [
                            'productCode' =>  $productCode,
                            'depositDate' => date('Y-m-d'), // Date du dépôt du colis
                            'orderNumber ' => $order_id,
                            'commercialName' => config('app.companyName') /*$order['shipping']['last_name'].' '.$order['shipping']['first_name']*/,
                            'returnTypeChoice' => 3, // Ne pas retourner
                            'totalAmount' => $insuranceValue
                        ],
                        'parcel' => [
                            'weight' => $weight, // Poids du colis
                            'insuranceValue' => $insuranceValue,
                            // 'nonMachinable' => $nonMachinable, //Format du colis, true pour non standard
                            'pickupLocationId' => $order['pick_up_location_id'] ?? null
                        ],
                        'sender' => [
                            'senderParcelRef' => $order_id,
                            'address' => [
                                'companyName' => config('app.companyName'),
                                "firstName" => "",
                                "lastName" => "",
                                'line2' => config('app.line2'),
                                'countryCode' => config('app.countryCode'),
                                'city' => config('app.city'),
                                'zipCode' => config('app.zipCode'),
                                "email" => config('app.email'),
                            ]
                        ],
                        'customsDeclarations' => [
                            'includeCustomsDeclarations' => 1,
                            'contents'                   => [
                                'article'  => $customsArticle,
                                'category' => [
                                    'value' => 3,
                                ],
                            ],
                            'invoiceNumber'             => $order_id,
                            'original' => [
                                'originalInvoiceNumber' => $order_id,
                                'originalInvoiceDate'   => $order['date']
                            ],
                        ],
                        'addressee' => [
                            'addresseeParcelRef' => $order_id,
                            'codeBarForReference' => false,
                            'address' => $address
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
                $cn23 = isset($content['<cn23>']) ? mb_convert_encoding($content['<cn23>'], 'UTF-8', 'ASCII') : false;

                if(!$label){
                    return $content['<jsonInfos>']['messages'][0]['messageContent'];
                }

                $trackingNumber = isset($content['<jsonInfos>']['labelV2Response']['parcelNumber']) ? $content['<jsonInfos>']['labelV2Response']['parcelNumber'] : null;

               
                if($trackingNumber){
                    $data = [
                        'order_id' => $order_id,
                        'label' => $label,
                        'origin' => 'colissimo',
                        'label_format' => explode('_', $format)[0],
                        'label_created_at' => date('Y-m-d h:i:s'),
                        'tracking_number' => $trackingNumber,
                        'cn23' => $cn23
                    ];

                    if(!$order['from_dolibarr']){
                        try{
                            return $this->postOutwardLabelWordPress($data);
                        } catch(Exception $e){
                            return $e->getMessage();
                        }
                    } else {
                        return true;
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


    public function generateBordereauByParcelsNumbers($parcelNumbers_array, $date){

            $max_package = 250;
            $new_array = $this->diviserTableau($parcelNumbers_array, $max_package);
            foreach($new_array as $key => $array){
                $request = [
                    'contractNumber'                    => config('app.colissimo_contractNumber'),
                    'password'                          => config('app.colissimo_password'),
                    'generateBordereauParcelNumberList' => [
                        'parcelsNumbers' => $array
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
                        $bordereau_id = $content['<jsonInfos>']['bordereauHeader']['bordereauNumber'];
                        $this->label->saveBordereau($bordereau_id, $array);
                        $this->bordereau->save($bordereau_id, $content['<deliveryPaper>'], $date);

                        if($key == (count($new_array) -1)){
                            return $content;
                        }
                    } else {
                        return isset($content) ? $content : false;
                    }
                  
                } catch(Exception $e) {
                    return $e->getMessage();
                }
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


    public function deleteOutwardLabelWordPress($tracking_number){

        $customer_key = config('app.woocommerce_customer_key');
        $customer_secret = config('app.woocommerce_customer_secret');
      
        try {
            $response = Http::withBasicAuth($customer_key, $customer_secret) 
                ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/colissimo/delete", [
                    'data' => $tracking_number
                ]); 
            return $response->json();
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }


    public function trackingStatusLabel($tracking_number){

        $customer_key = config('app.woocommerce_customer_key');
        $customer_secret = config('app.woocommerce_customer_secret');
    
        try {
            $response = Http::withBasicAuth($customer_key, $customer_secret) 
                ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/colissimo/trackingStatusLabel", [
                    'data' => $tracking_number
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
    

    protected function getProductCode($order){

        $productCode_array = [
            'lpc_expert'     => 'DOS',
            'lpc_expert_ddp'     => 'DOS',
            'lpc_nosign'     => 'DOM',
            'lpc_sign'       => 'DOS',
            'local_pickup'   => false
        ];

        if($order['product_code']){
            return $order['product_code'];
        } else {
            if(isset($productCode_array[$order['shipping_method']])){
                return $productCode_array[$order['shipping_method']];
            } else {
                return false;
            }
        }
    }

    protected function getInsuranceValue($product_code, $order){
        $tranches_except = ["BPR", "A2P", "CMT", "PCS"];
        if(in_array($product_code, $tranches_except)){
            if($order['total_order'] * 100 > 100000){
                return 100000;
            } else {
                return $order['total_order'] * 100;
            }
        } else {
            if($order['total_order'] * 100 > 500000){
                return 500000;
            } else {
                return $order['total_order'] * 100;
            }
        }
    }

    protected function isMachinable($product_code){
        $nonMachinable = ["BPR", "A2P", "BDP", "CMT"];
        return in_array($product_code, $nonMachinable) ? false : true;
    }

    protected function getAddress($order){

        if(isset($order['billing']['phone'])){
            $phoneNumber              = str_replace(' ', '', $order['billing']['phone']);
            $frenchMobileNumberRegex  = '/^(?:(?:\+|00)33|0)(?:6|7)\d{8}$/';
    
            if($order['shipping']['country'] == "FR" && !preg_match($frenchMobileNumberRegex, $phoneNumber)){
                $address = [
                    'companyName' => $order['shipping']['company'] ?? '',
                    'lastName' => $order['shipping']['last_name'] != "" ? $order['shipping']['last_name'] : $order['shipping']['first_name'],
                    'firstName' => $order['shipping']['first_name'],
                    'line2' => $order['shipping']['address_1'],
                    'line3' => $order['shipping']['address_2'] ?? '',
                    'countryCode' => $order['shipping']['country'],
                    'city' => $order['shipping']['city'],
                    'zipCode' => $order['shipping']['postcode'],
                    'email' => $order['billing']['email'],
                    'phoneNumber' => $phoneNumber
                ];
            
                return $address;
            } else {
                $address = [
                    'companyName' => $order['shipping']['company'] ?? '',
                    'lastName' => $order['shipping']['last_name'] != "" ? $order['shipping']['last_name'] : $order['shipping']['first_name'],
                    'firstName' => $order['shipping']['first_name'],
                    'line2' => $order['shipping']['address_1'],
                    'line3' => $order['shipping']['address_2'] ?? '',
                    'countryCode' => $order['shipping']['country'],
                    'city' => $order['shipping']['city'],
                    'zipCode' => $order['shipping']['postcode'],
                    'email' => $order['billing']['email'],
                    'mobileNumber' => $this->getMobilePhone($phoneNumber, $order['shipping']['country'])
                ];

                return $address;
            }
        } else {
            $address = [
                'companyName' =>$order['shipping']['company'] ?? '',
                'lastName' => $order['shipping']['last_name'] != "" ? $order['shipping']['last_name'] : $order['shipping']['first_name'],
                'firstName' => $order['shipping']['first_name'],
                // 'line2' => preg_replace('/[^(\x20-\x7F)]*/', ' ', $order['shipping']['address_1']),
                // 'line3' => preg_replace('/[^(\x20-\x7F)]*/', ' ', $order['shipping']['address_2'] ?? ''),
                'line2' => $order['shipping']['address_1'],
                'line3' => $order['shipping']['address_2'] ?? '',
                'countryCode' => $order['shipping']['country'],
                'city' => $order['shipping']['city'],
                'zipCode' => $order['shipping']['postcode'],
                'email' => $order['billing']['email'],
                // 'mobileNumber' => ''
            ];
            return $address;
        }
    }

    protected function getMobilePhone($mobile, $country){
      
        if($mobile != "" && $mobile != null && !str_contains($mobile, '+')){
            if($country == "FR"){
                $mobile = $mobile;
            } else if($country == "BE"){
                $mobile = "+32".substr($mobile, 1);
            } else if($country == "CH"){
                $mobile = "+41".substr($mobile, 1);
            } else {
                $mobile = $mobile;
            }

            return $mobile;
        } else if(str_contains($mobile, '+')){
            return $mobile;
        } else {
            return "";
        }
    }

    // protected function isCn22($total, $weight){
    //     if($total <= 425 && $weight <= 2 ){
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }

    // protected function isCn23($total, $weight){
    //     if($total >= 425 && ($weight >= 2 && $weight <= 20)){
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }
    // XC2F000423
    
    protected function customsArticle($order, $items){
        $customsArticle = [];
        foreach($order['line_items'] as $key => $item){
            if(in_array($item['product_id'], $items)){
                $customsArticle[] = [
                    'description'   => $item['name'],
                    'quantity'      => $item['quantity'],
                    'value'         => $item['total'] != 0 ? $item['total'] : ($item['real_price'] ?? 1),
                    'currency'      => config('app.currency'),
                    'artref'        => $item['ref'] ?? '',
                    'originalIdent' => 'A',
                    'originCountry' => 'FR',
                    'hsCode'        => '33049900', // code pour produits esthétique, beauté
                    'weight'        => is_numeric($item['weight']) ? $item['weight'] : 0
                ];
            }
           
        }   
        return $customsArticle;
    }

    protected function diviserTableau($array, $max) {
        $resultat = array();
        $sousTableau = array();
        $total = 0;
    
        foreach ($array as $valeur) {
            if ($total < $max) {
                $sousTableau[] = $valeur;
                $total += 1;
            } else {
                $resultat[] = $sousTableau;
                $sousTableau = array($valeur);
                $total = 1;
            }
        }
    
        if (!empty($sousTableau)) {
            $resultat[] = $sousTableau;
        }
    
        return $resultat;
    }
 
}

