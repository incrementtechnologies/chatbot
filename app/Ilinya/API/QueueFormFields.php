<?php

namespace App\Ilinya\API;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Ilinya\API\Controller;

class QueueFormFields{
  public static function retrieve($data){
     $controller = 'App\Http\Controllers\QueueFormFieldController';
     $request = new Request();
     $condition[] = [
          "column"  => $data['column'],
          "clause"  => "=",
          "value"   => $data['value']
     ];
     $request['condition'] = $condition;
     return Controller::retrieve($request, $controller);
  }
  public static function retrieveByCustom($id, $column){
     $controller = 'App\Http\Controllers\QueueFormFieldController';
     $request = new Request();
     $condition[] = [
          "column"  => "id",
          "clause"  => "=",
          "value"   => $id
     ];
     $request['condition'] = $condition;
     $field = Controller::retrieve($request, $controller);
     return (sizeof($field) > 0)?$field[0][$column]:null;
  }
}
