<?php

namespace App\Http\Controllers;
use App\BusinessType;
use DB;

use Illuminate\Http\Request;

class BusinessTypeController extends APIController
{
    function __construct(){
      $this->model = new BusinessType();
    }
}
