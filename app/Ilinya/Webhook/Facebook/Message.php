<?php

namespace App\Ilinya\Webhook\Facebook;


class Message{

    private $mId;
    private $text;
    private $attachments;
    private $quickReply;

    public function __construct(array $data){
     $this->mId = $data["mid"];
     $this->text = isset($data["text"]) ? $data["text"] : null;
     $this->attachments = isset($data["attachments"]) ? $data["attachments"] : null;
     $this->quickReply = isset($data["quick_reply"]) ? $data["quick_reply"] : null;
    }

    public function getId(){
        return $this->mId;
    }

    public function getText(){
        return $this->text;
    }

    public function getAttachments(){
        return $this->attachments;
    }

    public function getQuickReply(){
        if($this->quickReply){
            $response =[];
            if ($this->quickReply['payload'][0] =='@') {
                $response['payload']    = $this->quickReply['payload'];
                $response['parameter']   = $this->getText();
            } else {
                $array = explode('@', $this->quickReply['payload']);
                $response['payload']    = '@'.$array[1];
                $response['parameter']   = $array[0];
            }
            return $response;
        }
        return null;
    }
}