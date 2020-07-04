<?php

namespace App\Ilinya;

use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\Webhook\Facebook\Message;

class MessageExtractor{
   protected $messaging;

   function __construct(Messaging $messaging){
      $this->messaging = $messaging;
   }

   public function extractData(){
        $type = $this->messaging->getType();
        if($type == "message") {
            return $this->extractDataFromMessage();
        } 
        else if ($type == "postback") {
            return $this->extractDataFromPostback();
        }
        else if($type == "read"){
            return $this->extractDataFromRead();
        }
        else if($type == "delivery"){
            return $this->extractDataFromDelivery();
        }
        return [];
    }

    public function extractDataFromMessage(){
        $message = new Message($this->messaging->getMessageArray());
        $response =  [
            "type"          => "message",
            "text"          => $message->getText(),
            "attachments"   => $message->getAttachments(),
            "quick_reply"   => $message->getQuickReply()
        ];
        return $response;
    }

    public function extractDataFromPostback(){
        $payload = $this->messaging->getPostback()->getPayload();
        $parameter = $this->messaging->getPostback()->getTitle();
        $response = [
            "type"  => "postback"
        ];
        if ($payload[0] =='@') {
            $response['payload']    = $payload;
            $response['parameter']   = $parameter;
        } else {
            $array = explode('@', $payload);
            $response['payload']    = '@'.$array[1];
            $response['parameter']   = $parameter;
        }
        return  $response;
    }

    public function extractDataFromRead(){
        return [
            "type"  => "read"
        ];
    }

    public function extractDataFromDelivery(){
        return [
            "type"  => "delivery"
        ];
    }
}