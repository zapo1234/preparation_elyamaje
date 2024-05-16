<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReassortMissing extends Model
{
    use HasFactory;

    protected $table = 'missing_products_reassort';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    
    protected $fillable = [
      'id',
      'identifiant_reassort',
      'missing',
    ];
}