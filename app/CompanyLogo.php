<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyLogo extends APIModel
{

  protected $table = 'company_logo';
  
  public function company(){
    return $this->belongsTo('App\Company', 'id');
  }
}
