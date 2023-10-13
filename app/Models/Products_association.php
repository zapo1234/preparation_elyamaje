<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products_association extends Model
{
    use HasFactory;

    protected $table = 'products_association';

    protected $fillable = [
        'id',
        'fk_product_pere',
        'fk_product_fils'
    ];

}
