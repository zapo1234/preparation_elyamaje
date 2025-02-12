<?php

namespace App\Http\Service\Woocommerce;

class WoocommerceService
{
    // $order => details orders, $specific_product => orders with only some products, not all products of orders, $withProducts => if show list products of order or not
    public function transformArrayOrder($order, $specific_product = [], $withProducts = true){
        $order_new_array = [];
        $products = [];
        $total_product = 0;

        $order[0]['order_id'] = $order[0]['order_woocommerce_id'];
        $order[0]['id'] = $order[0]['order_woocommerce_id'];

        $billing = [
          "first_name" => $order[0]['billing_customer_first_name'],
          "last_name" => $order[0]['billing_customer_last_name'],
          "company" => $order[0]['billing_customer_company'],
          "address_1" => $order[0]['billing_customer_address_1'],
          "address_2" => $order[0]['billing_customer_address_2'],
          "city" => $order[0]['billing_customer_city'],
          "state" => $order[0]['billing_customer_state'],
          "postcode" => $order[0]['billing_customer_postcode'],
          "country" => $order[0]['billing_customer_country'],
          "email" => $order[0]['billing_customer_email'],
          "phone" =>  $order[0]['billing_customer_phone'],
        ];

        $shipping = [
          "first_name" => $order[0]['shipping_customer_first_name'],
          "last_name" => $order[0]['shipping_customer_last_name'],
          "company" => $order[0]['shipping_customer_company'],
          "address_1" => $order[0]['shipping_customer_address_1'],
          "address_2" => $order[0]['shipping_customer_address_2'],
          "city" => $order[0]['shipping_customer_city'],
          "state" => $order[0]['shipping_customer_state'],
          "postcode" => $order[0]['shipping_customer_postcode'],
          "country" => $order[0]['shipping_customer_country'],
          "phone" =>  $order[0]['shipping_customer_phone'],
        ];

        // Construis le tableau de la même manière que woocommerce
        foreach($order as $key => $or){
          if(in_array(100, explode(',', $or['discount_amount'])) && str_contains($or['coupons'], 'fem')){
            $order[$key]['coupons'] = $or['coupons'] ?? '';
            $order[$key]['discount'] = 0;
            $order[$key]['discount_amount'] = 0;
          }

          if($withProducts){
            $total_product = $total_product + intval($or['quantity']);

            // Récupère que des produits spécifique de la commande
            if(count($specific_product) > 0){
                if(in_array($or['product_woocommerce_id'], $specific_product)){
  
                    $products['line_items'][] = ['name' => $or['name'], 'product_id' => $or['product_woocommerce_id'], 'variation_id' => $or['variation'] == 1 ? $or['product_woocommerce_id'] : 0, 
                    'quantity' => $or['quantity'], 'subtotal' => $or['cost'], 'total' => $or['total_price'],  'subtotal_tax' => $or['subtotal_tax'],  'total_tax' => $or['total_tax'],
                    'weight' =>  $or['weight'], 'ref' => $or['ref'], 'category' => $or['category'] ?? '', 'category_id' => $or['category_id'] ?? 0, 'meta_data' => [['key' => 'barcode', "value" => $or['barcode']]]];
  
                    // for amb gift
                    if($or['total_price'] == 0){
                      $key_line_item = count($products['line_items']) - 1;
                      $products['line_items'][$key_line_item]['real_price'] = $or['price'];
                    }
  
  
                
                    // for fem gift
                    if(($or['total_price'] - ($or['cost'] * $or['quantity']) > 0.10) && in_array(100, explode(',', $or['discount_amount'])) && $or['total_price'] != 0.0){
                      $key_line_item = count($products['line_items']) - 1;
                      $products['line_items'][$key_line_item]['quantity'] = $products['line_items'][$key_line_item]['quantity'] > 1 ? $products['line_items'][$key_line_item]['quantity'] - 1 : 1;
                      $products['line_items'][$key_line_item]['subtotal_tax'] = $products['line_items'][$key_line_item]['total_tax'] * $products['line_items'][$key_line_item]['quantity'];
                      $products['line_items'][] = ['name' => $or['name'], 'product_id' => $or['product_woocommerce_id'], 'variation_id' => $or['variation'] == 1 ? $or['product_woocommerce_id'] : 0, 
                      'quantity' => 1, 'subtotal' => 0.0, 'total' => 0.0,  'subtotal_tax' => 0.0,  'total_tax' => 0.0,
                      'weight' =>  $or['weight'], 'meta_data' => [['key' => 'barcode', "value" => $or['barcode']]], 'real_price' => $or['price']];
                    }
                }
            } else {
                $products['line_items'][] = ['name' => $or['name'], 'product_id' => $or['product_woocommerce_id'], 'variation_id' => $or['variation'] == 1 ? $or['product_woocommerce_id'] : 0, 
                'quantity' => $or['quantity'], 'subtotal' => $or['cost'], 'total' => $or['total_price'],  'subtotal_tax' => $or['subtotal_tax'],  'total_tax' => $or['total_tax'],
                'weight' =>  $or['weight'], 'ref' => $or['ref'], 'category' => $or['category'] ?? '', 'category_id' => $or['category_id'] ?? 0, 'meta_data' => [['key' => 'barcode', "value" => $or['barcode']]]];
  
                // for amb gift or fem
                if($or['total_price'] == 0){
                  $key_line_item = count($products['line_items']) - 1;
                  $products['line_items'][$key_line_item]['real_price'] = $or['price'];
                }
  
                // for fem gift
                if(($or['total_price'] - ($or['cost'] * $or['quantity']) > 0.10) && in_array(100, explode(',', $or['discount_amount'])) && $or['total_price'] != 0.0){
                  $key_line_item = count($products['line_items']) - 1;
                  $products['line_items'][$key_line_item]['quantity'] = $products['line_items'][$key_line_item]['quantity'] > 1 ? $products['line_items'][$key_line_item]['quantity'] - 1 : 1;
                  $products['line_items'][$key_line_item]['subtotal_tax'] = $products['line_items'][$key_line_item]['total_tax'] * $products['line_items'][$key_line_item]['quantity'];
                  $products['line_items'][] = ['name' => $or['name'], 'product_id' => $or['product_woocommerce_id'], 'variation_id' => $or['variation'] == 1 ? $or['product_woocommerce_id'] : 0, 
                    'quantity' => 1, 'subtotal' => 0.0, 'total' => 0.0,  'subtotal_tax' => 0.0,  'total_tax' => 0.0,
                    'weight' =>  $or['weight'], 'meta_data' => [['key' => 'barcode', "value" => $or['barcode']]], 'real_price' => $or['price']];
                }
            }
          }
         

          foreach($or as $key2 => $or2){
            if (str_contains($key2, 'billing_customer')) {
              unset($order[$key][$key2]);
            }

            if (str_contains($key2, 'shipping_customer')) {
              unset($order[$key][$key2]);
            }
          }
        }

        $order_new_array =  $order[0];

        if($withProducts){
          $order_new_array['total_product'] = $total_product;
          $order_new_array['line_items'] = $products['line_items'];
        }
        
        $order_new_array['billing'] = $billing;
        $order_new_array['shipping'] = $shipping;
        $order_new_array['from_dolibarr'] = false;

        if(isset($order[0]['is_distributor'])){
          $distributor = $order[0]['is_distributor'] ? true : false;
        } else {
          $distributor = false;
        }

        // Frais d'expédition et détails expéditeurs
        $order_new_array['shipping_method_name'] = $this->getShippingMethod($order[0]['shipping_method']);

        $order_new_array['is_distributor'] = $distributor;
        $orders[] = $order_new_array;
        return $orders;
  }

  public function transformArrayOrderDolibarr($orderDolibarr, $product_to_add_label = null, $withProducts = true){

    $shipping_method_label = [
      'lpc_relay' => 'Colissimo relais',
      'lpc_sign' => 'Colissimo avec signature (Est:48h-72h)',
      'chronotoshopdirect' => 'Chronopost - Livraison en relais Pickup',
      'chrono13' => 'Livraison express avant 13h',
      'chrono18' => 'Livraison à domicile avant 18h'
    ];

    $transformOrder = [];
    $newArray = [];
    $total_product = 0;
        
    $transformOrder['discount_amount'] = $orderDolibarr[0]['remise_percent'] ?? 0;
    $transformOrder['gift_card_amount'] = "";
    $transformOrder['amountCard'] = $orderDolibarr[0]['amountCard'] ?? 0;
    $transformOrder['date'] = $orderDolibarr[0]['date'];
    $transformOrder['date_created'] = $orderDolibarr[0]['date'];
    $transformOrder['total_tax_order'] = $orderDolibarr[0]['total_tax'];
    $transformOrder['total_tax'] = $orderDolibarr[0]['total_tax'];
    $transformOrder['total_order'] = $orderDolibarr[0]['total_order_ttc'];
    $transformOrder['total'] = $orderDolibarr[0]['total_order_ttc'];
    $transformOrder['status'] = $orderDolibarr[0]['statut'];
    $transformOrder['user_id'] = $orderDolibarr[0]['user_id'];
    $transformOrder['payment_method'] = $orderDolibarr[0]['payment_methode'];
    $transformOrder['is_distributor'] = false;
    $transformOrder['customer_id'] = 0;
    $transformOrder['order_woocommerce_id'] = $orderDolibarr[0]['ref_order'];
    $transformOrder['order_id'] = $orderDolibarr[0]['ref_order'];
    $transformOrder['id'] = $orderDolibarr[0]['ref_order'];
    $transformOrder['dolibarrOrderId'] = $orderDolibarr[0]['id'];
    $transformOrder['discount_total'] = 0;
    $transformOrder['coupons'] = "";
    $transformOrder['shipping_amount'] = $orderDolibarr[0]['shipping_amount'] ?? 0;
    $transformOrder['gift_card'] = 0;
    $transformOrder['from_dolibarr'] = true;
    $transformOrder['fk_commande'] = $orderDolibarr[0]['fk_commande'];
    $transformOrder['pick_up_location_id'] = $orderDolibarr[0]['pick_up_location_id'] != 0 && $orderDolibarr[0]['pick_up_location_id'] ? $orderDolibarr[0]['pick_up_location_id'] : false;
    $transformOrder['preparateur'] = isset($orderDolibarr[0]['preparateur']) ? $orderDolibarr[0]['preparateur'] : '';
    $transformOrder['coliship'] = isset($orderDolibarr[0]['coliship']) ? $orderDolibarr[0]['coliship'] : 0;
    $transformOrder['is_professional'] = isset($orderDolibarr[0]['is_professional']) ? $orderDolibarr[0]['is_professional'] : 0;

    // On force la méthode d'expédition en livraison à domicile avec signature
    $transformOrder['shipping_method'] = $orderDolibarr[0]['shipping_method'] ?? "lpc_sign";
    $transformOrder['product_code'] = $orderDolibarr[0]['product_code'] ?? null;
    // $transformOrder['shipping_method_detail'] = str_contains($orderDolibarr[0]['ref_order'], "BP") ? "Chronopost" 
    // : ($orderDolibarr[0]['total_order_ttc'] > 100 ? "Colissimo avec signature gratuit au dela de 100€ d'achat" : "Colissimo avec signature (Est:48h-72h)");

    $transformOrder['shipping_method_detail'] = isset($shipping_method_label[$orderDolibarr[0]['shipping_method']]) ? $shipping_method_label[$orderDolibarr[0]['shipping_method']] 
    : ($orderDolibarr[0]['total_order_ttc'] > 100 ? "Colissimo avec signature gratuit au dela de 100€ d'achat" : "Colissimo avec signature (Est:48h-72h)");


    // Si adresse trop longue, on découpe en deux
    $adress = explode("\n", $orderDolibarr[0]['adresse']);
    $adress = array_values(array_filter($adress, 'strlen'));
    $adress_1 = $adress[0];
    $adress_2 = isset($adress[1]) ? $adress[1] : '';

    $transformOrder['billing'] = [
      "first_name" => $orderDolibarr[0]['billing_name'] ?? $orderDolibarr[0]['name'],
      "last_name" => $orderDolibarr[0]['billing_pname'] != null ? $orderDolibarr[0]['billing_pname'] : 
      ($orderDolibarr[0]['name'] != $orderDolibarr[0]['pname'] ? $orderDolibarr[0]['pname'] : ''),
      "company" => $orderDolibarr[0]['billing_company'] ?? $orderDolibarr[0]['company'],
      "address_1" => $orderDolibarr[0]['billing_adresse'] ?? $adress_1,
      "address_2" => $adress_2,
      "city" => $orderDolibarr[0]['billing_city'] ?? $orderDolibarr[0]['city'],
      "state" => "",
      "postcode" => $orderDolibarr[0]['billing_code_postal'] ?? $orderDolibarr[0]['code_postal'],
      "country" => $orderDolibarr[0]['billing_country'] ?? $orderDolibarr[0]['contry'],
      "email" =>  $orderDolibarr[0]['email'],
      "phone" => $orderDolibarr[0]['phone'],
    ]; 

    $transformOrder['shipping'] = [
      "first_name" => $orderDolibarr[0]['firstname'],
      "last_name" => $orderDolibarr[0]['lastname'] != $orderDolibarr[0]['firstname'] ? $orderDolibarr[0]['lastname'] : '',
      "company" => $orderDolibarr[0]['company'],
      "address_1" => $adress_1,
      "address_2" => $adress_2,
      "city" => $orderDolibarr[0]['city'],
      "state" => "",
      "postcode" => $orderDolibarr[0]['code_postal'],
      "country" => $orderDolibarr[0]['contry'],
      "email" =>  $orderDolibarr[0]['email'],
      "phone" => $orderDolibarr[0]['phone'],
    ]; 

    // Pour les commandes GAL / BP on récupère la liste des paiements utilisés pour cette commande
    if(isset($orderDolibarr[0]['payment_list'])){
      $transformOrder['payment_method'] = [];
      $transformOrder['payment_list'] = [
        "amountCard" => 0,
        "amountSpecies" => 0,
        "amountDiscount" => 0
      ];

      if(is_array($orderDolibarr[0]['payment_list'])){
        foreach($orderDolibarr[0]['payment_list'] as $payement){
          if($payement['type'] == "CB"){
            $transformOrder['payment_method'][] = "CB";
            $transformOrder['amountCard'] = $payement['amount_payement'];
            $transformOrder['payment_list']["amountCard"] = floatval($payement['amount_payement'] + $transformOrder['payment_list']["amountCard"]);
          } else if($payement['type'] == "LIQ"){
            $transformOrder['payment_method'][] = "LIQ";
            $transformOrder['payment_list']["amountSpecies"] = floatval($payement['amount_payement'] + $transformOrder['payment_list']["amountSpecies"]);
          } else if($payement['type'] == "TICK"){
            $transformOrder['payment_list']["amountDiscount"] = floatval($payement['amount_payement'] + $transformOrder['payment_list']["amountDiscount"]);
          }
        }
        // On transforme en chaine de caractère la liste des moyens d epaiement CB / Espèces
        $transformOrder['payment_method'] = implode(',', array_unique($transformOrder['payment_method']));
      }
    }

    if($withProducts){
      foreach($orderDolibarr as $order){
        $total_product = $total_product + intval($order['quantity']);
        if($product_to_add_label){
          if(in_array($order['product_woocommerce_id'], $product_to_add_label)) {
            $transformOrder['line_items'][]= [
              'id' => $order['line_items_id_dolibarr'],
              'name' => $order['productName'],
              'product_id' => $order['product_woocommerce_id'],
              'variation_id' => $order['variation'] == 1 ? $order['product_woocommerce_id'] : 0,
              'quantity' => $order['quantity'],
              'subtotal' => $order['priceDolibarr'],
              'price' => $order['priceDolibarr'],
              'real_price' => $order['priceDolibarr'],
              'total' => $order['total_ht'],
              'subtotal_tax' => $order['total_tva'],
              'total_tax' => $order['total_tva'],
              'weight' => $order['weight'],
              'ref' => $order['ref'],
              'product_dolibarr_id' => $order['product_dolibarr_id'],
              'meta_data' => [
                ['key' => 'barcode', "value" => $order['barcode']]
              ]
            ];
          }
        } else {
          $transformOrder['line_items'][] = [
            'id' => $order['line_items_id_dolibarr'],
            'name' => $order['productName'],
            'product_id' => $order['product_woocommerce_id'],
            'variation_id' => $order['variation'] == 1 ? $order['product_woocommerce_id'] : 0,
            'quantity' => $order['quantity'],
            'subtotal' => $order['priceDolibarr'],
            'price' => $order['priceDolibarr'],
            'real_price' => $order['priceDolibarr'],
            'total' => $order['total_ht'],
            'subtotal_tax' => $order['total_tva'],
            'total_tax' => $order['total_tva'],
            'weight' => $order['weight'],
            'ref' => $order['ref'],
            'product_dolibarr_id' => $order['product_dolibarr_id'],
            'meta_data' => [
              ['key' => 'barcode', "value" => $order['barcode']]
            ]
          ];
        }
        $transformOrder['total_product'] = $total_product;
        $transformOrder['total_products'] = $total_product;
      }
    }

    // Remove products double
    if(isset($transformOrder['line_items'])){
      $product_double = [];
      foreach($transformOrder['line_items'] as $key1 => $item){
          if(in_array($item['product_dolibarr_id'], $product_double)){
            unset($transformOrder['line_items'][$key1]);
          } else {
            $product_double[] = $item['product_dolibarr_id'];
          }
      }
      $transformOrder['line_items'] = array_values($transformOrder['line_items']);
    }
   

    $transformOrder['shipping_method_name'] = $this->getShippingMethod($transformOrder['shipping_method']);
    $newArray[] = $transformOrder;
    return $newArray;
  }

  private function getShippingMethod($shippingMeyhod = false){
    if(str_contains($shippingMeyhod, 'lpc')){
      return "Colissimo";
    } else if(str_contains($shippingMeyhod, 'chrono')){
      return" Chronopost";
    } else if(str_contains($shippingMeyhod, 'local')){
      return "Retrait distributeur";
    } else {
      return "Other";
    }
  }
}







