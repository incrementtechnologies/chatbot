<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QueueForm extends APIModel
{
  public function queue_form_fields(){
    return $this->hasMany('App\QueueFormField');
  }
}
