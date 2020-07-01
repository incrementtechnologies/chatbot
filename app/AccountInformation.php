<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountInformation extends APIModel
{

  protected $fillable = ['account_id', 'account_type_id', 'first_name', 'last_name'];
  public function  account_type(){
    return $this->belongsTo('App\AccountType','account_type_id');
  }

  public function account(){
    return $this->belongsTo('App\Account','account_id');
  }
}
