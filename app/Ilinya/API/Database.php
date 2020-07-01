<?php

namespace App\Ilinya\API;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Database{

  public static function insert($name, $data){
    $data['created_at'] = Carbon::now();
    return DB::table($name)->insert($data);
  }

  public static function update($name, $condition, $data){
    $data['updated_at'] = Carbon::now();
    return DB::table($name)->where($condition)->whereNull("deleted_at")->update($data);
  }
  
  public static function retrieve($name, $condition = null, $order = null){
    $result = null;
    if($condition && $order){
      $result =  DB::table($name)->where($condition)->whereNull('deleted_at')->orderBy($order[0], $order[1])->get();
    }
    else if($condition && $order == null){
      $result =  DB::table($name)->where($condition)->whereNull('deleted_at')->get();
    }
    else if($condition == null && $order){
      $result =  DB::table($name)->whereNull('deleted_at')->orderBy($order[0], $order[1])->get();
    }
    else{

    }

    return json_decode($result, true);
  }

  public static function delete($name, $condition){
    return DB::table($name)->where($condition)->whereNull("deleted_at")->update(["deleted_at" => Carbon::now()]);
  }
}