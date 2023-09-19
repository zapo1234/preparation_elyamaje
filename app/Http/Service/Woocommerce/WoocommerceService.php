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
            $order[$key]['coupons'] = "";
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
                    if($or['cost'] * $or['quantity'] != $or['total_price'] && in_array(100, explode(',', $or['discount_amount'])) && $or['total_price'] != 0.0){
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
        $orders[] = $order_new_array;

        return $orders;
    }
}







