<?php

namespace App\Ilinya\Message\Facebook;

use App\Ilinya\Bot;
use App\Ilinya\Tracker;
use App\Ilinya\Message\Facebook\Codes;
use App\Ilinya\Message\Facebook\Form;
use App\Ilinya\Response\Facebook\PostbackResponse;
use App\Ilinya\Response\Facebook\CategoryResponse;
use App\Ilinya\Response\Facebook\EditResponse;
use App\Ilinya\Response\Facebook\SearchResponse;
use App\Ilinya\Response\Facebook\EditDetailsResponse;
use App\Ilinya\Response\Facebook\DetailsResponse;
use App\Ilinya\Response\Facebook\AiResponse;
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\Helper\Validation;
use App\Ilinya\API\QueueCardFields;

class Ai{
    protected $form;
    protected $post;
    protected $search;
    protected $code;
    protected $tracker;
    protected $edit;
    protected $editDetails;
    protected $validation;
    protected $details;
    protected $aiResponse;
  function __construct(Messaging $messaging){
      $this->bot    = new Bot($messaging);
      $this->post   = new PostbackResponse($messaging);
      $this->category = new CategoryResponse($messaging);
      $this->form   = new Form($messaging);
      $this->tracker= new Tracker($messaging);
      $this->code   = new Codes(); 
      $this->edit   = new EditResponse($messaging);
      $this->search = new SearchResponse($messaging);
      $this->editDetails = new EditDetailsResponse($messaging);
      $this->validation = new Validation($messaging);
      $this->details = new DetailsResponse($messaging);
      $this->aiResponse   = new AiResponse($messaging);
  }

  public function manage($reply){
    $reply = strtolower($reply);

    if(strpos($reply, 'hi') !== false || strpos($reply, 'hello') !== false ||strpos($reply, 'help') !== false || strpos($reply, 'hola') !== false){
        $this->bot->reply($this->post->testMessage(), false);
        $this->bot->reply($this->post->start(), false);
        $this->bot->reply($this->post->categories(), false);
    }
    else if(strpos($reply, 'thank you') !== false){
        $this->bot->reply($this->aiResponse->thankYou(),  false);
    }
    else if(strpos($reply, 'baha ba karun') !== false){
        $this->bot->reply("Dli baya", false);
    }
    else{
        $this->bot->reply($this->aiResponse->error(), false);
        $this->bot->reply($this->post->start(), false);
        $this->bot->reply($this->post->categories(), false);
    }
  }

}
