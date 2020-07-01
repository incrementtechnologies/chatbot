<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Announcement as DBItem;

class AnnouncementController extends APIController
{
  function __construct(){
    $this->model = new DBItem();
  }

  public function create(Request $request){
    $this->createEntry($request->toArray());
    // $botRequest = new \Illuminate\Http\Request();
    $this->response['debug'][] = app('App\Http\Controllers\IlinyaController')->broadcast($request['message']);
    return $this->output();
  }
}
