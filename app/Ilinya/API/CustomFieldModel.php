<?php

namespace App\Ilinya\API;
use Illuminate\Http\Request;
use App\Ilinya\API\Controller;
use App\Ilinya\API\Database as DB;
class CustomFieldModel{
   public static function getFieldsByTrackId($trackId){
       $db_field = "temp_custom_fields_storage";
       $condition = [
          ['track_id', '=', $trackId]
        ];
        $order = ['id', 'asc'];
        return DB::retrieve($db_field, $condition, $order);
    }

    public static function getFieldById($id){
       $db_field = "temp_custom_fields_storage";
       $condition = [
          ['id', '=', $id]
        ];
        return DB::retrieve($db_field, $condition, null);
    }
}