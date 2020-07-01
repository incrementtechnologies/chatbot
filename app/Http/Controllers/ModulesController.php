<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules;

class ModulesController extends APIController
{
  function __construct(){
    $this->model = new Modules();
  }
}
