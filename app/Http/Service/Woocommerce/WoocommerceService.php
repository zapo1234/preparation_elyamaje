<?php

namespace App\Http\Service\Woocommerce;

class WoocommerceService
{

    public function transformArrayOrder($order, $specific_product = []){
        $order_new_array = [];
        $products = [];

        $order[0]['order_id'] = $order[0]['order_woocommerce_id'];
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

          // Récupère que des produits spécifique de la commande
          if(count($specific_product) > 0){
              if(in_array($or['product_woocommerce_id'], $specific_product)){

                  $products['line_items'][] = ['name' => $or['name'], 'product_id' => $or['product_woocommerce_id'], 'variation_id' => $or['variation'] == 1 ? $or['product_woocommerce_id'] : 0, 
                  'quantity' => $or['quantity'], 'subtotal' => $or['cost'], 'total' => $or['total_price'],  'subtotal_tax' => $or['subtotal_tax'],  'total_tax' => $or['total_tax'],
                  'weight' =>  $or['weight'], 'ref' => $or['ref'], 'meta_data' => [['key' => 'barcode', "value" => $or['barcode']]]];

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
              'weight' =>  $or['weight'], 'ref' => $or['ref'], 'meta_data' => [['key' => 'barcode', "value" => $or['barcode']]]];

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

          foreach($or as $key2 => $or2){
            if (str_contains($key2, 'billing')) {
              unset($order[$key][$key2]);
            }

            if (str_contains($key2, 'shipping') && !str_contains($key2, 'method')) {
              unset($order[$key][$key2]);
            }
          }
        }

        $order_new_array =  $order[0];
        $order_new_array['line_items'] = $products['line_items'];
        $order_new_array['billing'] = $billing;
        $order_new_array['shipping'] = $shipping;

        if(isset($order[0]['is_distributor'])){
          $distributor = $order[0]['is_distributor'] ? true : false;
        } else {
          $distributor = false;
        }

        $order_new_array['is_distributor'] = $distributor;
        $orders[] = $order_new_array;

        return $orders;
  }

  public function transformArrayOrderDolibarr($orderDolibarr, $product_to_add_label = null){
    $transformOrder = [];
    $newArray = [];
    
    $transformOrder['discount_amount'] = $orderDolibarr[0]['remise_percent'];
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
    $transformOrder['order_woocommerce_id'] = $orderDolibarr[0]['orderDoliId'];
    $transformOrder['order_id'] = $orderDolibarr[0]['orderDoliId'];
    $transformOrder['id'] = $orderDolibarr[0]['orderDoliId'];
    $transformOrder['discount_total'] = 0;
    $transformOrder['coupons'] = "";
    $transformOrder['shipping_amount'] = 0;
    $transformOrder['gift_card'] = 0;
    $transformOrder['from_dolibarr'] = true;
    $transformOrder['fk_commande'] = $orderDolibarr[0]['fk_commande'];
    $transformOrder['preparateur'] = isset($orderDolibarr[0]['preparateur']) ? $orderDolibarr[0]['preparateur'] : '';


    // On force la méthode d'expédition en livraison à domicile avec signature
    $transformOrder['shipping_method'] = "lpc_sign";
    $transformOrder['product_code'] = null;
    $transformOrder['shipping_method_detail'] = $orderDolibarr[0]['total_order_ttc'] > 100 ? "Colissimo avec signature gratuit au dela de 100€ d'achat" : "Colissimo avec signature (Est:48h-72h)";


    $transformOrder['billing'] = [
      "first_name" => $orderDolibarr[0]['firstname'],
      "last_name" => $orderDolibarr[0]['lastname'] != $orderDolibarr[0]['firstname'] ? $orderDolibarr[0]['lastname'] : '',
      "company" => $orderDolibarr[0]['company'],
      "address_1" => $orderDolibarr[0]['adresse'],
      "address_2" => "",
      "city" => $orderDolibarr[0]['city'],
      "state" => "",
      "postcode" => $orderDolibarr[0]['code_postal'],
      "country" => $orderDolibarr[0]['contry'],
      "email" =>  $orderDolibarr[0]['email'],
      "phone" => $orderDolibarr[0]['phone'],
    ]; 
    $transformOrder['shipping'] =  $transformOrder['billing'];

    foreach($orderDolibarr as $order){
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
            'total' => $order['total_ht'],
            'subtotal_tax' => $order['total_tva'],
            'total_tax' => $order['total_tva'],
            'weight' => $order['weight'],
            'ref' => $order['ref'],
            'meta_data' => [
              ['key' => 'barcode', "value" => $order['barcode']]
            ]
          ];
        }
      } else {
        $transformOrder['line_items'][]= [
          'id' => $order['line_items_id_dolibarr'],
          'name' => $order['productName'],
          'product_id' => $order['product_woocommerce_id'],
          'variation_id' => $order['variation'] == 1 ? $order['product_woocommerce_id'] : 0,
          'quantity' => $order['quantity'],
          'subtotal' => $order['priceDolibarr'],
          'price' => $order['priceDolibarr'],
          'total' => $order['total_ht'],
          'subtotal_tax' => $order['total_tva'],
          'total_tax' => $order['total_tva'],
          'weight' => $order['weight'],
          'ref' => $order['ref'],
          'meta_data' => [
            ['key' => 'barcode', "value" => $order['barcode']]
          ]
        ];
      }
    }

    $newArray[] = $transformOrder;
    return $newArray;
  }
}







