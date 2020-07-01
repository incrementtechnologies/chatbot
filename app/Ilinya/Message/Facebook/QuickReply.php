<?php

namespace App\Ilinya\Message\Facebook;

use App\Ilinya\Bot;
use App\Ilinya\Tracker;
use App\Ilinya\Message\Facebook\Codes;
use App\Ilinya\Message\Facebook\Form;
use App\Ilinya\Message\Facebook\Error;
use App\Ilinya\Message\Facebook\Postback;
use App\Ilinya\Response\Facebook\PostbackResponse;
use App\Ilinya\Response\Facebook\CategoryResponse;
use App\Ilinya\Response\Facebook\SendResponse;
use App\Ilinya\Response\Facebook\SearchResponse;
use App\Ilinya\Response\Facebook\SurveyResponse;
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\Response\Facebook\QueueCardsResponse;


class QuickReply{
    protected $form;
    protected $post;
    protected $search;
    protected $code;
    protected $tracker;
    protected $send;
    protected $error;
    protected $postback;
    protected $survey;
    protected $qc;

  function __construct(Messaging $messaging){
        $this->bot    = new Bot($messaging);
        $this->post   = new PostbackResponse($messaging);
        $this->category = new CategoryResponse($messaging);
        $this->form   = new Form($messaging);
        $this->tracker= new Tracker($messaging);
        $this->code   = new Codes(); 
        $this->send   = new SendResponse($messaging);
        $this->error  = new Error($messaging);
        $this->postback = new Postback($messaging);
        $this->search = new SearchResponse($messaging);
        $this->survey = new SurveyResponse($messaging);
        $this->qc     = new QueueCardsResponse($messaging);
  }
  public function manage($custom){
      $parameter = $custom['quick_reply']['parameter'];
      $parameter2 = null;
        if(strpos($parameter, ',')){
          list($parameter, $parameter2) = explode(',', $parameter);
        }
        else{}
      switch ($this->code->getCode($custom)) {
        case $this->code->qrSearch:
          $this->bot->reply($this->search->question($parameter),true);
          $data = [
            "search_option" => $parameter,
            "reply" => $this->code->replyStageSearch
          ];
          $this->tracker->update($data);
          return null;
          break;
        case $this->code->qrPriorityYes:
          break;
        case $this->code->qrPriorityNo:
          break;
        case $this->code->qrFormCancel:
          $this->bot->reply($this->post->categories(), false);
          return null;
          break;
        case $this->code->qrFormContinue:
          $this->form->retrieve($parameter, null);
          return $data = [
            "form_id" => $parameter,
            "stage"   => $this->code->stageForm
          ];
          break;
        case $this->code->qrDisregard:
          if($parameter == '1' || intval($parameter) == 1){
            //Disregard
            $this->tracker->delete();
            $this->bot->reply('Transaction has been successfully disregarded!', true);
          }
          else{
            //Cancel go to review or send directly
            $this->bot->reply($this->send->submit(), false);
          }
          return null;
          break;
        case $this->code->qrStageError:
          if($parameter == '1' || intval($parameter) == 1){
              //Cancel Current Transaction
             $this->error->clearCustomField();
             $data = [
                "stage"      => $this->code->stageStart,
                "company_id" => null,
                "form_id"    => null,
                "form_sequence" => null,
                "reply"     => null
             ];
             $this->tracker->update($data);
             //Perform previous status
             $data = [
                "payload" => $this->tracker->getPrevStatusError(),
                "type"    => 'postback'
             ];
             $this->postback->manage($data);
          }
          else{
              //Continue Current Transaction
             $this->form->retrieve(null, true);
          }
          break;
        case $this->code->qrSurvey:
            if(intval($parameter) == 1 || $parameter == '1'){
             $formdId = 6;  
             $this->form->retrieve($formdId, null);
            }
            else{
              $this->bot->reply($this->survey->appreciate(), false);
            }
          break;
        case $this->code->qrQueueCardCancel:
            if(intval($parameter) == 1 || $parameter == '1'){
             $this->bot->reply($this->qc->cancel($parameter2), false);
            }
            else{
              $this->bot->reply($this->qc->noInform(), false);
            }
          break;
        case $this->code->qrQueueCardPostpone:
          if(intval($parameter) == 1 || $parameter == '1'){
             $this->bot->reply($this->qc->postpone($parameter2), false);
          }
          else{
            $this->bot->reply($this->qc->noInform(), false);
          }
          break;
        default:
          //Statement Here
          break;
      }
  }
}