<?php

namespace App\Ilinya;


use Illuminate\Support\Facades\Log;
use App\Ilinya\Http\Curl;
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\Templates\Facebook\PersistentMenuTemplate;


class Bot{
    protected $curl;
    protected $messaging;
    protected $tracker;
    function __construct(Messaging $messaging){
        $this->messaging = $messaging;
        $this->tracker = new BotTracker($messaging);
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
    
    public static function checkPartition(Array $arr){
        $max = 10;
        $len = sizeof($arr);
        $partition = 1;
        if ($len > $max) {
           $partition =  $len % $max != 0 ? floor($len/$max)+1 : floor($len/$max);
        }
        return $partition ;
    }
    
    public static function partition(Array $list) {
        $p = Bot::checkPartition($list);
        $listlen = count($list);
        $partlen = floor($listlen / $p);
        $partrem = $listlen % $p;
        $partition = array();
        $mark = 0;
        for($px = 0; $px < $p; $px ++) {
            $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
            $partition[$px] = array_slice($list, $mark, $incr);
            $mark += $incr;
        }
        return $partition;
    }

    public function setup(){
        $recipientId = $this->messaging->getSenderId();
        Curl::setupMenu($recipientId);
        return response("", 200);
    }
}