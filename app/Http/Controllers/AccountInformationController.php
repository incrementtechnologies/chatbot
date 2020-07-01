<?php

namespace App\Http\Controllers;

use App\AccountInformation;
use Illuminate\Http\Request;

class AccountInformationController extends APIController
{
     function __construct(){  
        $this->model = new AccountInformation();

        $this->foreignTable = array(
          'account'
        );
    }
}
