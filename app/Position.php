<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends APIModel
{
  public function  company_branch(){
    return $this->belongsTo('App\CompanyBranch', 'id');
  }

  public function account_positions(){
    return $this->hasMany('App\AccountPosition');
  }

}
