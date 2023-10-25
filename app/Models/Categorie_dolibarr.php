<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie_dolibarr extends Model
{
    use HasFactory;

    protected $table = 'categories_dolibarr';

    protected $fillable = [
        'id',
        'fk_parent',
        'label'
    ];

}
