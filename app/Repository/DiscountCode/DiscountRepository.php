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
        $query = DB::table('discount_code')
        ->select('discount_code.*', 'products.name')
        ->join('products_order', 'discount_code.order_id', '=', 'products_order.order_id')
        ->join('products', 'products_order.product_woocommerce_id', '=', 'products.product_woocommerce_id');

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

        dd($query->get());
        // Récupérer les résultats et les transformer
        return $query->get()->map(function ($item) {
            return [
                'id_commande' => $item->order_id,
                'nom' => $item->last_name,
                'prenom' => $item->first_name,
                'email' => $item->email,
                'phone' => $item->phone,
                'address_1' => $item->address_1,
                'address_2' => $item->address_2,
                'city' => $item->city,
                'phone' => $item->phone,
                'code_promo' => $item->code,
                'total_ht' => $item->total_ht,
                'shipping_amount' => $item->shipping_amount,
                'payment_method' => $item->	payment_method,
                'total_ttc' => $item->total_ttc,
                'status' => $item->status,
                'date' => $item->order_date,
            ];
        });
    }
}
