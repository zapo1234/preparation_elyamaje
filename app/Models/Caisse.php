<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caisse extends Model
{
    use HasFactory;

    protected $table = 'caisse';

    protected $fillable = [
      'id',
      'name',
      'created_at'
    ];
}