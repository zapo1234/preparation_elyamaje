<?php

namespace App\Http\Service\Api\Chronopost;

use Exception;
use Illuminate\Support\Facades\Http;

// Simplified array for Chronopost Soap api client


class Chronopost
{
    public function generateLabelChrono($order, $weight, $order_id, $colissimo){

        $productCode = $this->getProductCode();
        $format = "PDF"; //$colissimo ? $colissimo->format : "PDF";
        $SaturdayShipping = 1;

        $shipping_params = [ 
            // Chronopost account api password / Mot de passe Api Chronopost
            'password'                      => '255562', //config('app.chronopost_password'), 
            // Chronopost account / Compte client chronopost
            'headerValue'                   => [
                "accountNumber"             => '19869502', //config('app.chronopost_accountNumber'),
                "idEmit"                    => 'CHRFR',
                'subAccount'                => ''
            ],
            // Shipper / Expediteur
            'shipperValue' => [
                "shipperCivility"           => 'M',
                "shipperName"               => config('app.companyName'),
                // "shipperName2"               => '',
                "shipperContactName"        => config('app.companyName'),
                "shipperAdress1"            => config('app.line2'),
                // "shipperAdress2"            => '',
                "shipperZipCode"            => config('app.zipCode'),
                "shipperCity"               => config('app.city'),
                "shipperCountry"            => config('app.countryCode'),
                "shipperCountryName"        =>  config('app.countryName'),
                "shipperEmail"              => config('app.email'),
                "shipperPhone"              => config('app.companyPhone'),  
                "shipperMobilePhone"        => config('app.companyMobilePhone'),
                "shipperPreAlert"           => '',
            ],
               // Customer / Client
            'customerValue' => [
                "customerCivility"          => ' ',
                "customerName"              => $this->getFilledValue($order['shipping']['company'] ?? ''),
                "customerName2"             => $this->getFilledValue($order['shipping']['last_name'].' '.$order['shipping']['first_name']),      
                "customerContactName"       => $this->getFilledValue($order['shipping']['first_name'].' '.$order['shipping']['last_name']),
                "customerAdress1"           => $this->getFilledValue($order['shipping']['address_1'] ?? ''),
                "customerAdress2"           => $this->getFilledValue($order['shipping']['address_2'] ?? ''),
                "customerCity"              => $this->getFilledValue($order['shipping']['city']),
                "customerZipCode"           => $order['shipping']['postcode'],
                "customerCountry"           => $order['shipping']['country'],
                // "customerCountryName"       => 'FRANCE',                                                                                       
                "customerEmail"             => $order['billing']['email'],
                "customerMobilePhone"       => str_replace(" ", "", $order['billing']['phone']),
                "customerPhone"             => str_replace(" ", "", $order['billing']['phone']),
                "customerPreAlert"          => '',
            ],
            // Recipient / Destinataire
            'recipientValue' => [
                "recipientCivility"         => ' ',
                "recipientName"             => $this->getFilledValue($order['shipping']['company'] ?? ''),
                "recipientName2"            => $this->getFilledValue($order['shipping']['first_name'].' '.$order['shipping']['last_name']), 
                "recipientContactName"      => $this->getFilledValue($order['shipping']['first_name'].' '.$order['shipping']['last_name']),
                "recipientAdress1"          => $this->getFilledValue($order['shipping']['address_1'] ?? ''),
                "recipientAdress2"          => $this->getFilledValue($order['shipping']['address_2'] ?? ''),
                "recipientCity"             => $this->getFilledValue($order['shipping']['city']),
                "recipientZipCode"          => $order['shipping']['postcode'],
                "recipientCountry"          => $order['shipping']['country'],
                // "recipientCountryName"      => 'FRANCE',
                "recipientEmail"            => $order['billing']['email'],
                "recipientMobilePhone"      => str_replace(" ", "", $order['billing']['phone']),
                "recipientPhone"            => str_replace(" ", "", $order['billing']['phone']),
                "recipientPreAlert"         => '',  
            ],
            // Sky Bill / Etiquette de livraison / Caractéristique du colis
            'skybillValue' => [
                "codCurrency"               => config('app.currency'),
                'codValue'                  => '',
                'content1'                  => '',
                'content2'                  => '',
                'content3'                  => '',
                'content4'                  => '',
                'content5'                  => '',
                "customsCurrency"           => config('app.currency'),
                "evtCode"                   => 'DC', 
                "insuredCurrency"           => config('app.currency'),
                "objectType"                => 'MAR',
                "productCode"               => $productCode,  
                "service"                   => $SaturdayShipping,          
                "shipDate"                  => date('c'),       
                "shipHour"                  => date('H'),      
                "weight"                    => $weight,  
                "weightUnit"                => 'KGM',                   
                "bulkNumber"                => 1, 
                'height'                    => 0,
				'length'                    => 0,
				'width'                     => 0,
            ],
            // client's ref. value / Code barre client
            'refValue' => [
                "shipperRef"                => $order['order_id'],            
                "recipientRef"              => $order['customer_id'],      
            ],
            // Skybill Params Value / Etiquette de livraison - format de fichiers /datas
            'skybillParamsValue' => [
                "mode"           => explode('_', $format)[0],
                'withReservation' => 2,
            ],
        ]; 

        try{
            $url = "https://ws.chronopost.fr/shipping-cxf/ShippingServiceWS?wsdl";
            $soap = new \SoapClient($url, ['trace' => 1]);
    
            $result = $soap->shippingV3($shipping_params);

            // file_put_contents('test.pdf', $result->return->skybill);
            if(isset($result->return->skybillNumber) && isset($result->return->skybill)){

                $data = [
                    'success' => true,
                    'order_id' => $order_id,
                    'label' => $result->return->skybill,
                    'origin' => 'chronopost',
                    'label_format' => explode('_', $format)[0],
                    'label_created_at' => date('Y-m-d h:i:s'),
                    'tracking_number' => $result->return->skybillNumber
                ];

                return $data;

            } else {
                return $result->return->errorMessage;
            }
        } catch (Exception $e){
            return $e->getMessage();
        }
    }


    public function getStatus($trackingNumbers){

        $orders_status = [];
        foreach($trackingNumbers as $key => $trackingNumber){
            try {

                $response = Http::withHeaders(['Content-Type' => 'application/json'])->get("https://ws.chronopost.fr/tracking-cxf/TrackingServiceWS/searchPOD?accountNumber=".config('app.chronopost_accountNumber')."&password=".config('app.chronopost_password')."&language=fr_FR&skybillNumber=XA165862625FR&pdf=true");

                // Vérifiez si la requête HTTP a réussi
                if ($response->successful()) {
                  
                    $xml = simplexml_load_string($response->body());
                    $statusCode = $xml->xpath('//statusCode');

                    if (!empty($statusCode)) {
                        $statusCodeValue = (string)$statusCode[0];
                        if($statusCodeValue == 5){
                            $orders_status[] = [
                                'order_id' => $trackingNumber->order_id,
                                'step' => 5,
                                'message' => ''
                            ];
                        }
                    } 
                }
            } catch(Exception $e){

            }
        }

        return $orders_status;
    }

    public function trackingStatusLabel($tracking_number){
        $customer_key = config('app.woocommerce_customer_key');
        $customer_secret = config('app.woocommerce_customer_secret');
        
        try {
            $response = Http::withBasicAuth($customer_key, $customer_secret) 
                ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/chronopost/trackingStatusLabel", [
                    'data' => $tracking_number
                ]); 
            return $response->json();
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    protected function getProductCode(){
        return "01";
        // If return from abroad (not France) : 3T
        // If return from France : 4T
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

    protected function removeaccents($string)
	{
		$stringToReturn = str_replace(
			array(
				'à',
				'á',
				'â',
				'ã',
				'ä',
				'ç',
				'è',
				'é',
				'ê',
				'ë',
				'ì',
				'í',
				'î',
				'ï',
				'ñ',
				'ò',
				'ó',
				'ô',
				'õ',
				'ö',
				'ù',
				'ú',
				'û',
				'ü',
				'ý',
				'ÿ',
				'À',
				'Á',
				'Â',
				'Ã',
				'Ä',
				'Ç',
				'È',
				'É',
				'Ê',
				'Ë',
				'Ì',
				'Í',
				'Î',
				'Ï',
				'Ñ',
				'Ò',
				'Ó',
				'Ô',
				'Õ',
				'Ö',
				'Ù',
				'Ú',
				'Û',
				'Ü',
				'Ý',
				'/',
				'\xa8'
			),
			array(
				'a',
				'a',
				'a',
				'a',
				'a',
				'c',
				'e',
				'e',
				'e',
				'e',
				'i',
				'i',
				'i',
				'i',
				'n',
				'o',
				'o',
				'o',
				'o',
				'o',
				'u',
				'u',
				'u',
				'u',
				'y',
				'y',
				'A',
				'A',
				'A',
				'A',
				'A',
				'C',
				'E',
				'E',
				'E',
				'E',
				'I',
				'I',
				'I',
				'I',
				'N',
				'O',
				'O',
				'O',
				'O',
				'O',
				'U',
				'U',
				'U',
				'U',
				'Y',
				' ',
				'e'
			),
			$string
		);
		// Remove all remaining other unknown characters
		$stringToReturn = preg_replace('/[^a-zA-Z0-9\-]/', ' ', $stringToReturn);
		$stringToReturn = preg_replace('/^[\-]+/', '', $stringToReturn);
		$stringToReturn = preg_replace('/[\-]+$/', '', $stringToReturn);
		$stringToReturn = preg_replace('/[\-]{2,}/', ' ', $stringToReturn);

		return $stringToReturn;
	}

    protected function getFilledValue($value)
	{
        
		if ($value) {
			return $this->removeaccents(trim($value));
		}

		return '';
	}
    
}