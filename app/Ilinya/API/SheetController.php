<?php

namespace App\Ilinya\API;
use App\Ilinya\Http\Curl;
use Illuminate\Http\Request;
class SheetController
{

    public static function  getSheetContent($arr) {
         $page = $arr[0];
         $num = (int)$arr[1];
         $url = "https://spreadsheets.google.com/feeds/cells/1nGEKC19s4EqgadU3IGHC6An-OSJ69B7HYsHxsW-vTR8/{$page}/public/values?alt=json";
         $curl = new Curl();
         $sheetData = $curl->get($url ,true);
         $entries   = $sheetData["feed"]["entry"];
         $categories = array();
         $headers = array();
        
        for ($i=0; $i < $num; ++$i) { 
            array_push($headers ,$entries[$i]['content']['$t']);
        }
        for ($j=sizeof($headers); $j < sizeof($entries) ; $j+=$num) { 
            $object = array();
            for ($k=0; $k < sizeof($headers) ; $k++) { 
                $object[$headers[$k]]= $entries[$j+$k]['content']['$t'] ;
            }
            array_push($categories, $object);   
        }
        return $categories;
    }
}
