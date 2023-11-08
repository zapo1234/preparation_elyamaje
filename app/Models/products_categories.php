<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class products_categories extends Model
{
    use HasFactory;

    protected $table = 'products_categories';

    protected $fillable = [
        'fk_categorie',
        'fk_product',
        'import_key'
    ];
    public $timestamps = true;
}
