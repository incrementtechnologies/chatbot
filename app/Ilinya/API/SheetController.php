<?php

namespace App\Ilinya\API;
use App\Ilinya\Http\Curl;
use Illuminate\Http\Request;
class SheetController
{

    public static function getSheetContent($arr) {
         $page = $arr[0];
         $num = (int)$arr[1];
         $url = "https://spreadsheets.google.com/feeds/cells/1gOP1KqUS_uh0L18np4XQml387E8fuhn-oErTDhoaBgc/{$page}/public/values?alt=json";
         $curl = new Curl();
         $sheetData = $curl->get($url ,true);
         $entries   = $sheetData["feed"]["entry"];
         $categories = array();
         $headers = array();
         for ($i=0; $i < $num; ++$i) { 
             array_push($headers ,$entries[$i]['content']['$t']);
            }
        \Storage::put("sheet.json", json_encode($headers));
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
