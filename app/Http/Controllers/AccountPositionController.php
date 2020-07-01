<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AccountPosition;

class AccountPositionController extends APIController
{
    function __construct(){
    $this->model = new AccountPosition();
  }
}
