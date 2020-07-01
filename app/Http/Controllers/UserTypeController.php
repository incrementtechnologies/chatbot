<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserType as Item;

class UserTypeController extends APIController
{
  function __construct(){
    $this->model = new Item();
  }
}
