<?php

namespace App\Http\Controllers;

use App\CompanyLogo;
use Illuminate\Http\Request;

class CompanyLogoController extends APIController
{
  function __construct(){
    $this->model = new CompanyLogo();
  }
}
