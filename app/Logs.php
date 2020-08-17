<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    //
    protected $table = 'logs';
    protected $fillable = ['userID', 'recepientID', 'message', 'type' ,'msgID'];
}
