<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use App\Repository\Label\LabelRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Label extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $label;
   
    public function __construct(LabelRepository $label){
        $this->label = $label;
    }

    public function getlabels(){
        $labels = $this->label->getLabels();
        return view('labels.label', ['labels' => $labels]);
      
    }

    public function labelPDF(Request $request){
 
        $blob = $this->label->getLabelById($request->post('label_id'));
        $headers = [
            'Content-Type' => 'application/pdf',
        ];
    
        // Renvoyer le contenu blob en tant que réponse
        return Response::make($blob[0]->label, 200, $headers);
    }
    
   
}
