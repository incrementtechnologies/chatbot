<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QueueFormField extends APIModel
{
    protected $fillable = ['queue_form_id', 'description', 'sequence'];
    protected $attributes = array(
      'type' => 'text',
      'setting' => '-'
    );

}
