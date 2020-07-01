<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BusinessPosition as Item;

class BusinessPositionController extends APIController
{
  function __construct(){
    $this->model = new Item();
    $this->validation = [
      "description" => "required|unique:business_positions"
    ];
  }
}
