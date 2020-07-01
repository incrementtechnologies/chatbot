<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessType extends APIModel
{
    protected $primaryKey = 'id';
    protected $table = 'business_types';

    public function  company(){
      return $this->hasMany('App\Company');
    }
}
