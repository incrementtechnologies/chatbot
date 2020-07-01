<?php

namespace App\Ilinya\API;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Ilinya\API\Controller;

class QueueCard{
    

    public static function totalCurrentDayFinished($queueFormId){
      $controller = 'App\Http\Controllers\QueueCardController';
      $request = new Request();
      $condition[] = [
        "column"  => "queue_form_id",
        "clause"  => "=",
        "value"   => $queueFormId
      ];

      $condition[] = [
        "column"  => "status",
        "clause"  => "=",
        "value"   => 3
      ];
      $request['condition'] = $condition;
      $qc = Controller::retrieve($request, $controller);
      return sizeof($qc);
    }
    public static function totalOnQueue($queueFormId){
      $controller = 'App\Http\Controllers\QueueCardController';
      $request = new Request();
      $condition[] = [
        "column"  => "queue_form_id",
        "clause"  => "=",
        "value"   => $queueFormId
      ];
      $request['condition'] = $condition;
      $qc = Controller::retrieve($request, $controller);
      return sizeof($qc);
    }

    public static function retrieve($data, $column = null){
      $controller = 'App\Http\Controllers\QueueCardController';
      $request = new Request();
       $condition[] = [
          "column"  => "company_id",
          "clause"  => "=",
          "value"   => $data['company_id']
        ];
        $condition[] = [
          "column"  => "queue_form_id",
          "clause"  => "=",
          "value"   => $data['queue_form_id']
        ];
        $condition[] = [
          "column"  => "facebook_user_id",
          "clause"  => "=",
          "value"   => $data['facebook_user_id']
        ];
      $request['condition'] = $condition;
      $qc = Controller::retrieve($request, $controller);
      if($column)
        return (sizeof($qc) > 0) ? $qc[0][$column] : null;
        return $qc;
    } 

    public static function retrieveById($id, $column = null){
      $controller = 'App\Http\Controllers\QueueCardController';
      $request = new Request();
       $condition[] = [
          "column"  => "id",
          "clause"  => "=",
          "value"   => $id
        ];

      $request['condition'] = $condition;
      $qc = Controller::retrieve($request, $controller);
      if($column == "*")
        return (sizeof($qc) > 0) ? $qc[0] : null;
      else if($column != "*" && $column != null)
        return (sizeof($qc) > 0) ? $qc[0][$column] : null;
        return $qc;
    }

    public static function create($data){
       /*
        1. Get Fields
        2. Tracker
        3. Get New Card Number
           - Get Previous  + 1
      */
        $controller = 'App\Http\Controllers\QueueCardController';
        //@tracker
        $reCon = new Request();
        $condition[] = [
          "column"  => "company_id",
          "clause"  => "=",
          "value"   => $data['company_id']
        ];
        $condition[] = [
          "column"  => "queue_form_id",
          "clause"  => "=",
          "value"   => $data['queue_form_id']
        ];
        $condition[] = [
          "column"  => "facebook_user_id",
          "clause"  => "=",
          "value"   => $data['facebook_user_id']
        ];
        $condition[] = [
          "column"  => "status",
          "clause"  => "!=",
          "value"   => "3"
        ];

        $reCon['condition'] = $condition;
        $result = Controller::retrieve($reCon, $controller);

        if(!$result){
          $request = new Request();
          $request['company_id']        = $data['company_id'];
          $request['queue_form_id']     = $data['queue_form_id'];
          $request['facebook_user_id']  = $data['facebook_user_id'];
          $request['number']  = 0;
          $result = Controller::create($request, $controller);
          return ($result != false)? $result:null;  
        }
        else{
          return  $result[0]['id'];
        }
    }

}