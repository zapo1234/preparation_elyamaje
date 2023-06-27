<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsOrder extends Model
{
    use HasFactory;

    protected $table = 'products_order';

    protected $fillable = [
        'order_id',
        'product_woocommerce_id',
        'category',
        'category_id',
        'quantity',
        'cost',
        'subtotal_tax',
        'total_tax',
        'total_price',
        'pick',
        'line_item_id',
        'created_at',
        'updated_at',
        'pick_control'
    ];
}
