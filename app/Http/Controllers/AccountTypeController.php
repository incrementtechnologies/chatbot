<?php


namespace App\Http\Controllers;

use App\AccountType;
use Illuminate\Http\Request;

class AccountTypeController extends APIController
{
     function __construct(){
        $this->model = new AccountType();
    }
}
