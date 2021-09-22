<?php

namespace App\Ilinya\Message\Facebook;

use App\Ilinya\Bot;
use App\Ilinya\BotTracker;
use App\Ilinya\Message\Facebook\Codes;
use App\Ilinya\Message\Facebook\Form;
use App\Ilinya\Response\Facebook\PostbackResponse;
use App\Ilinya\Response\Facebook\CategoryResponse;
use App\Ilinya\Response\Facebook\EditResponse;
use App\Ilinya\Response\Facebook\SearchResponse;
use App\Ilinya\Response\Facebook\EditDetailsResponse;
use App\Ilinya\Response\Facebook\DetailsResponse;
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\Helper\Validation;
use App\Ilinya\API\QueueCardFields;
use App\Ilinya\Message\Facebook\Ai;

class Text{
    protected $form;
    protected $post;
    protected $search;
    protected $code;
    protected $tracker;
    protected $edit;
    protected $editDetails;
    protected $validation;
    protected $details;
    protected $ai;
  function __construct(Messaging $messaging){
      $this->bot    = new Bot($messaging);
      $this->post   = new PostbackResponse($messaging);
      $this->category = new CategoryResponse($messaging);
      $this->form   = new Form($messaging);
      $this->tracker= new BotTracker($messaging);
      $this->code   = new Codes(); 
      $this->edit   = new EditResponse($messaging);
      $this->search = new SearchResponse($messaging);
      $this->editDetails = new EditDetailsResponse($messaging);
      $this->validation = new Validation($messaging);
      $this->details = new DetailsResponse($messaging);
      $this->ai     = new Ai($messaging);
  }

  public function manage($reply){
    $this->ai->manage($reply);
   }
  public function checkShortCodes($text){
    if($text[0] == '@'){
      return true;
    }
    return false;
  }
}