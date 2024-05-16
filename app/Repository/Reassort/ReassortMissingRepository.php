<?php

namespace App\Repository\Reassort;

use App\Models\ReassortMissing;
use App\Repository\Reassort\ReassortMissingInterface;

class ReassortMissingRepository implements ReassortMissingInterface 
{   
    private $model;
    public function __construct(ReassortMissing $model){
        $this->model = $model;
    }

    public function insert($data){
        return $this->model::insert([
            'identifiant_reassort' => $data['identifiant_reassort'],
            'missing' => $data['missing'],
        ]);
    }

    public function getById($id){
        return $this->model::where('identifiant_reassort', $id)->orderBy('created_at', 'desc')->get();
    }
}























