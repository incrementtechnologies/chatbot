<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountType extends APIModel
{
  public function  account_information(){
    return $this->hasManny('App\AccountInformation');
  }
}
