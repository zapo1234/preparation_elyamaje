<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDolibarr extends Model
{
    use HasFactory;

    protected $table = 'products_dolibarr';

    protected $fillable = [
        'id',
        'product_id',
        'label',

        'price_ttc',
        'barcode',
        'poids',

        'warehouse_array_list',
        'created_at',
        'updated_at',
    ];

}
