<?php

namespace App\Ilinya\API;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Ilinya\API\Controller;

class QueueForm{
  public static function retrieve($data){
    $controller = 'App\Http\Controllers\QueueFormController';
      $request = new Request();

       $condition[] = [
          "column"  => "id",
          "clause"  => "=",
          "value"   => $data['id']
       ];
       $condition[] = [
          "column"  => "company_id",
          "clause"  => "=",
          "value"   => $data['company_id']
        ];

      $request['condition'] = $condition;
      return Controller::retrieve($request, $controller);
  }

  public static function retrieveByCustomField($data){
      $controller = 'App\Http\Controllers\QueueFormController';
        $request = new Request();

         $condition[] = [
            "column"  => $data['column'],
            "clause"  => "=",
            "value"   => $data['value']
         ];

        $request['condition'] = $condition;
        return Controller::retrieve($request, $controller);
  }
}