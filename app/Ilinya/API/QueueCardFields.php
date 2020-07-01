<?php

namespace App\Ilinya\API;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Ilinya\API\Controller;

class QueueCardFields{

  public static function retrieve($data){
     $controller = 'App\Http\Controllers\QueueCardFieldController';
     $request = new Request();
     $condition[] = [
          "column"  => $data['column'],
          "clause"  => "=",
          "value"   => $data['value']
     ];
     $request['condition'] = $condition;
     return Controller::retrieve($request, $controller);
  }

  public static function retrieveByCustom($data, $column){
     $controller = 'App\Http\Controllers\QueueCardFieldController';
     $request = new Request();
     $condition[] = [
          "column"  => $data['column'],
          "clause"  => "=",
          "value"   => $data['value']
     ];
     $request['condition'] = $condition;
     $result = Controller::retrieve($request, $controller);
     return (sizeof($result) > 0) ? $result[0][$column] : null;
  }

  public static function update($data){
     $controller = 'App\Http\Controllers\QueueCardFieldController';
     $request = new Request();
     $request['id']         = $data['id'];
     $request['value']      = $data['value'];
    return Controller::update($request, $controller);
  }

  public static function create($data){
     $controller = 'App\Http\Controllers\QueueCardFieldController';
     $request = new Request();
     $request['queue_card_id']         = $data['queue_card_id'];
     $request['queue_form_field_id']   = $data['queue_form_field_id'];
     $request['value']                 = $data['value'];
     return Controller::create($request, $controller);
  }

}