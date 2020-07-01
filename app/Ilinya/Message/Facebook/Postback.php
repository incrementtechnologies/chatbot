<?php

namespace App\Ilinya\Message\Facebook;

use App\Ilinya\Bot;
use App\Ilinya\Tracker;
use App\Ilinya\Message\Facebook\Codes;
use App\Ilinya\Message\Facebook\Form;
use App\Ilinya\Response\Facebook\PostbackResponse;
use App\Ilinya\Response\Facebook\CategoryResponse;
use App\Ilinya\Response\Facebook\SendResponse;
use App\Ilinya\Response\Facebook\SearchResponse;
use App\Ilinya\Response\Facebook\EditResponse;
use App\Ilinya\Response\Facebook\QueueCardsResponse;
use App\Ilinya\Response\Facebook\DisregardResponse;
use App\Ilinya\Response\Facebook\DetailsResponse;
use App\Ilinya\Response\Facebook\EditDetailsResponse;
use App\Ilinya\Webhook\Facebook\Messaging;

class Postback{
    protected $forms;
    protected $post;
    protected $search;
    protected $code;
    protected $tracker;
    protected $send;
    protected $edit;
    protected $qc;
    protected $disregard;
    protected $details;
    protected $editDetails;

    function __construct(Messaging $messaging){
        $this->bot    = new Bot($messaging);
        $this->post   = new PostbackResponse($messaging);
        $this->category = new CategoryResponse($messaging);
        $this->forms   = new Form($messaging);
        $this->tracker= new Tracker($messaging);
        $this->code   = new Codes(); 
        $this->send   = new SendResponse($messaging);
        $this->edit   = new EditResponse($messaging);
        $this->qc     = new QueueCardsResponse($messaging);
        $this->disregard = new DisregardResponse($messaging);
        $this->search = new SearchResponse($messaging);
        $this->details = new DetailsResponse($messaging);
        $this->editDetails = new EditDetailsResponse($messaging);
    }

    public function manage($custom){
        $action = $this->code->getCode($custom);
        switch ($action) {
          case $this->code->pStart:
            $this->bot->reply($this->post->testMessage(), false);
            $this->bot->reply($this->post->start(), false);
            $this->bot->reply($this->post->categories(), false);
            break;
          case $this->code->pUserGuide:
            $this->bot->reply($this->post->userGuide(), true);
            break;
          case $this->code->pMyQueueCards:
            $this->bot->reply($this->qc->display(), false);
            break;
          case $this->code->pCategories:
            $this->bot->reply($this->post->categories(), false);
            break;
          case $this->code->pCategorySelected:
            $this->bot->reply($this->category->companies($custom['parameter']), false);
            break;
          case $this->code->pSearch:
            $this->bot->reply($this->search->searchOption(), false);
            break;
          case $this->code->pGetQueueCard:
            $this->forms->retrieveForms($custom['parameter']);
            break;
          case $this->code->pLocate:
            //Do Something
            break;
          case $this->code->pNext:
            //Do Something
            break;
          case $this->code->pSend:
            $this->bot->reply($this->send->submit(), false);
            break;
          case $this->code->pEdit:
            $this->bot->reply($this->edit->manage($custom), false);
            break;
          case $this->code->pDisregard:
            $this->bot->reply($this->disregard->inform(), false);
            break;
          case $this->code->pQCViewDetails:
            //View Details
            $this->bot->reply($this->details->viewDetails($custom['parameter']), false);
            break;
          case $this->code->pCancelQC:
            $this->bot->reply($this->qc->informCancel($custom['parameter']), false);
            break;
          case $this->code->pPostponeQC:
            $this->bot->reply($this->qc->informPostpone($custom['parameter']), false);
            break;
          case $this->code->pEditDetails:
            $this->bot->reply($this->editDetails->manage($custom['parameter']), true);
            break;
          default:
            //Error
            break;
        }   
    }
}