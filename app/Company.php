<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends APIModel
{

  protected $fillable = ['business_type','account_id', 'name','address'];
  public function business_type(){
    return $this->belongsTo('App\BusinessType', 'id');
  }

  public function company_logo(){
    return $this->hasOne('App\CompanyLogo');
  }

  public function account(){
    return $this->belongsTo('App\Account', 'id');
  }

}