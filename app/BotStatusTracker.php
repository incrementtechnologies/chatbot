<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BotStatusTracker extends APIModel
{
    protected $table = "bot_status_tracker";
    protected $fillable = ['facebook_id', 'status'];
}
