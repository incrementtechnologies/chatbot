<?php

namespace App\Ilinya;


use Illuminate\Support\Facades\Log;
use App\Ilinya\Http\Curl;
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\Templates\Facebook\PersistentMenuTemplate;


class Bot{
    protected $curl;
    protected $messaging;
    function __construct(Messaging $messaging){
        $this->messaging = $messaging;
        //$this->curl = new Curl();
    }

    public function reply($data, $flag){
        // \Storage::put("data.json", json_encode($data));
        $message = ($flag == true)?["text" => $data] : $data;
        $recipientId = $this->messaging->getSenderId();
        Curl::send($recipientId, $message);
        return response("", 200);       
    }

    public static function notify($recipientId, $message){
        $message = ['text' => $message];
        Curl::send($recipientId, $message);
    }

    public static function survey($recipientId, $message){
        Curl::send($recipientId, $message);
    }

    public function setup(){
        $recipientId = $this->messaging->getSenderId();
        Curl::setupMenu($recipientId);
        return response("", 200);
    }
}