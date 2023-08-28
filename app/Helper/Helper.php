<?php

namespace App\Helper;

use Illuminate\Support\Facades\Auth;
use App\Helper\ZplPrinter;
use Illuminate\Support\Facades\Log;

class Helper
{
    //print barcode function
    public static function printBarcode(string $content)
    {
        $zpl = "";
        //get the type from barcode from front 4 caracters
        $type = substr($content, 0, 4);
        if ($type == "SLF-") {
            $zpl = "^XA
            ^CF0,100
            ^FO220,50^FD" . substr($content, 4) . "^FS
            ^BY4,3,70
            ^FO150,210^BCN,280,N^FD" . $content . "^FS
            ^XZ";
        } else {
            $zpl = "";
        }

        //send to printer
        $zpl_ip = "your printer ip";
        //trim zpl
        $zpl = trim($zpl);
        //Log::info("IP:" . $zpl_ip . " Barcode:" . $zpl);
        if (!empty($zpl_ip) and !empty($zpl)) {
            ZplPrinter::printer($zpl_ip)->send($zpl);
        }
    }
}
