<?php
namespace App\Ilinya;

use App\Ilinya\MessageExtractor;
use App\Ilinya\Tracker;
use App\Ilinya\Message\Facebook\Attachments;
use App\Ilinya\Message\Facebook\Codes;
use App\Ilinya\Message\Facebook\Error;
use App\Ilinya\Message\Facebook\Postback;
use App\Ilinya\Message\Facebook\QuickReply;
use App\Ilinya\Message\Facebook\Text;
use App\Ilinya\Message\Facebook\Form;
use App\Ilinya\Webhook\Facebook\Messaging;

class MessageHandler{
  protected $types = array('postback', 'message', 'read', 'delivery');
  
  protected $currentCode;
  protected $reply;
  protected $searchOption;
  protected $stage;
  protected $prevStage;
  protected $companyId;
  protected $category;
  protected $formId;


  protected $tracker;

  protected $code;
  protected $custom;

  protected $postback;
  protected $messaging;
  protected $quickReply;
  protected $text;
  protected $form;
  protected $error;
  protected $response;
  function __construct(Messaging $messaging){
    $this->messaging  = $messaging;
    $this->tracker    = new Tracker($messaging);
    $messageExtractor = new MessageExtractor($messaging); 
    $this->custom     = $messageExtractor->extractData();
    $this->code       = new Codes();
    $this->postback   = new Postback($messaging);
    $this->quickReply = new QuickReply($messaging);
    $this->form       = new Form($messaging);
    $this->text       = new Text($messaging);
    $this->error      = new Error($messaging);
  }

  public function manage(){
    $this->response = $this->tracker->getStatus($this->custom);
    $this->currentCode = $this->code->getCode($this->custom);
    $this->trackerFlag = $this->response['tracker_flag'];
    switch ($this->response['status']) {
      case $this->code->read:
        //Read
        break;
      case $this->code->delivery:
        //Delivery
        break;
      case $this->code->pStart:
        $this->postback->manage($this->custom);
        $this->trackerHandler();
        break;
      case $this->code->postback:
        $this->getParameter();  
        $this->trackerHandler();
        $this->postback->manage($this->custom);
        break;
      case $this->code->message:
        $this->message();
        $this->trackerHandler();
        break;
      case $this->code->error:
        //Error
        $this->error->manage($this->custom);
        break;
      default:
        //Do Nothing
        $this->error->manage($this->custom);
        break;
    }
  }

  public function trackerHandler(){
    $data = [
                "status"            => $this->currentCode
            ];
    switch ($this->trackerFlag) {
      case 1: // Insert
            $this->tracker->insert($this->currentCode, $this->response['stage'], $this->category);
        break;
      case 2: // Update
            if($this->category)$data['business_type_id'] =  $this->category;
            if($this->stage)$data['stage'] = $this->stage;
            if($this->companyId)$data['company_id'] = $this->companyId;
            if($this->reply)$data['reply']  = $this->reply;
            if($this->searchOption)$data['search_option'] = $this->searchOption;
            $this->tracker->update($data);
        break;
      case 3:
            if($this->reply)$data['reply']  = $this->reply;
            if($this->formId)$data['form_id'] = $this->formId;
            if($this->stage)$data['stage'] = $this->stage;
            $this->tracker->update($data);
        break;
      case 4: // Update Error
        break;
      case 5:
        break;
      default:
        break;
    }
  }
  public function getParameter(){
    $code = $this->code->getCode($this->custom);
    switch ($code) {
      case $this->code->pCategorySelected:
        $this->category =  $this->custom['parameter'];
        break;
      case $this->code->pGetQueueCard:
        $this->companyId = $this->custom['parameter'];
        break;
      default:
        # code...
        break;
    }
  }

  public function message(){
        if($this->custom['attachments']){
            //Attachments
            $attachments = new Attachments($this->custom['attachments']);
            $response;
            //if($attachments->getType() == "location"){
            //    $response = $this->response->location($attachments);
            //}
            //else{
            //}
        }
        else if($this->custom['quick_reply']){
            $response = $this->quickReply->manage($this->custom);

            if($response){
              if($response['stage'])$this->stage = $response['stage'];
              if($response['form_id'])$this->formId = $response['form_id'];
            }
        }
        else if($this->custom['text']){
            $this->text->manage($this->custom['text']);
        } 
  }
}