<?php

namespace App\Ilinya\API;

use Illuminate\Http\Request;

//use App\Http\Controllers\BusinessTypeController;

class Controller{
  public static function retrieve(Request $request, $controller){
    $result = app($controller)->retrieve($request);
    $result = json_decode($result->getContent(), true);
    $data   = $result['data'];
    return $data;
  }

  public static function create(Request $request, $controller){
    $result = app($controller)->create($request);
    $result = json_decode($result->getContent(), true);
    echo json_encode($result);
    return $result['data'];
  }
  public static function insert(Request $request, $controller){
    $result = app($controller)->create($request);
    $result = json_decode($result->getContent(), true);
    return $result['data'];
  }

  public static function delete(Request $request, $controller){
    $result = app($controller)->delete($request);
    $result = json_decode($result->getContent(), true);
    return $result['data'];
  }

  public static function update(Request $request, $controller){
    $result = app($controller)->update($request);
    $result = json_decode($result->getContent(), true);
    return $result['data'];
  }
}
