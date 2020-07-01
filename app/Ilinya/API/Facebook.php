<?php

namespace App\Ilinya\API;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Ilinya\API\Controller;

class Facebook{
   public static function getDynamicField($accountNumber, $column){
      $controller = 'App\Http\Controllers\FacebookUserController';
      $request = new Request();

      $condition [] = [
        'column'  => 'account_number',
        'clause'  => '=',
        'value'   => $accountNumber
      ];

      $request['condition'] = $condition;
      $userField = Controller::retrieve($request, $controller);
      return (sizeof($userField) > 0) ? $userField[0][$column] : null;
    }
}