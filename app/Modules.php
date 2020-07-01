<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Modules extends APIModel
{
  protected $primaryKey = 'id';
  protected $table = 'modules';
}
