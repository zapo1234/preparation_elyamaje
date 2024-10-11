<?php

namespace App\Repository\DiscountCode;

use Illuminate\Support\Facades\DB;

class DiscountRepository
{
    /**
     * Obtenir les codes de réduction avec des filtres optionnels.
    */
    public function getDiscountCodes($startDate = null, $endDate = null, $code = null, $status = null, $limit = null, $status_updated = null)
    {
        // Get discount code
        $query = DB::table('discount_code')->select('*');

        // Ajouter des conditions de filtre si les paramètres sont fournis
        if ($startDate) {
            $query->where('discount_code.order_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('discount_code.order_date', '<=', $endDate);
        }

        if ($code) {
            $query->where('discount_code.code', $code);
        }

        if ($status_updated) {
            $query->where('discount_code.status_updated', '>=', $status_updated);
        }

        if ($status) {
            $statusArray = explode(',', $status);
            $query->whereIn('discount_code.status', (array) $statusArray);
        }

        if ($limit) {
            $query->limit($limit);
        }

        $discount_code = $query->get()->toArray();
        $discount_code_order = [];
        $order_id = [];

        foreach($discount_code as $code){
            $code = (array) $code;
            $order_id[] = $code['order_id'];
            $discount_code_order[$code['order_id']] = $code;
        }

      
        // Request to get orders products
        $details_order = DB::table('products_order')
        ->select('*')
        ->join('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
        ->whereIn('products_order.order_id', $order_id)
        ->get()
        ->toArray();

        foreach($details_order as $order){
            $order = (array) $order;
            $discount_code_order[$order['order_id']]['data_lines'][] = [
                "id" => $order['product_woocommerce_id'],
                "name" => $order['name'],
                "category" => $order['category'],
                "category_id" => $order['category_id'],
                "quantity" => $order['quantity'],
                "cost" => $order['cost'],
                "price" => $order['price'],
                "subtotal_tax" => $order['subtotal_tax'],
                "total_tax" => $order['total_tax'],
                "total" => $order['total_price'],
                "barcode" => $order['barcode'],
                "status" => $order['status'],
            ];
        }

        $discount_code_order = array_values($discount_code_order);
        return $discount_code_order;
    }

    public function updateOrder($order_id, $data){
        return DB::table('discount_code')->where('order_id', '=', $order_id)->update($data);
    }
}
