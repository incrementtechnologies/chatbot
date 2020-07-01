<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\FacebookUser as Item;

class FacebookUserController extends APIController
{
  function __construct(){
    $this->model = new Item();
  }
}
