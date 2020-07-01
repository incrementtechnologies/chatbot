<?php

namespace App\Ilinya\Response\Facebook;
/*
    @Providers
*/
use App\Ilinya\Http\Curl;
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\User;
use App\Ilinya\Bot;
use App\Ilinya\Tracker;
use Illuminate\Http\Request;
/*
    @Template
*/
use App\Ilinya\Templates\Facebook\QuickReplyTemplate;
use App\Ilinya\Templates\Facebook\ButtonTemplate;
use App\Ilinya\Templates\Facebook\GenericTemplate;
use App\Ilinya\Templates\Facebook\LocationTemplate;
use App\Ilinya\Templates\Facebook\ListTemplate;

/*
    @Elements
*/

use App\Ilinya\Templates\Facebook\ButtonElement;
use App\Ilinya\Templates\Facebook\GenericElement;
use App\Ilinya\Templates\Facebook\QuickReplyElement;


/*
    @API
*/
use App\Ilinya\API\Controller;
use App\Ilinya\API\Facebook;
use App\Ilinya\API\QueueCard;
use App\Ilinya\API\Company;

/*
  @Response
*/
use App\Ilinya\Response\Facebook\QueueCardsResponse;

class FormsResponse{
  protected $messaging;
  protected $tracker;
  protected $curl;
  protected $bot;
  protected $qc;

  public function __construct(Messaging $messaging){
      $this->messaging = $messaging;
      $this->tracker   = new Tracker($messaging);
      $this->bot       = new Bot($messaging);
      $this->qc        = new QueueCardsResponse($messaging);
      $this->curl = new Curl();
  }

  public function user(){
      $user = $this->curl->getUser($this->messaging->getSenderId());
      $this->user = new User($this->messaging->getSenderId(), $user['first_name'], $user['last_name']);
  }

  public function selectForms($forms){
      /*
        1. loop form
        2. generic template
        3. return template
      */
      $elements = [];
      foreach($forms as $form) {
        $formId = $form['id'];
        $title =  $form['detail'].' is '.$this->getAvailability($form['availability']);
        $subtitle = 'Currently '.QueueCard::totalCurrentDayFinished($formId) .'/'. QueueCard::totalOnQueue($formId);
        $imageUrl = "http://ilinya.com/wp-content/uploads/2017/08/cropped-logo-copy-copy.png";
        $buttons = [];

        $buttons[] = (intval($form['availability']) == 1) ?ButtonElement::title("Get QCard")
                    ->type('postback')
                    ->payload($formId.'@pGetQueueCard')
                    ->toArray():ButtonElement::title("Back to Categories")
                    ->type('postback')
                    ->payload('@pCategories')
                    ->toArray();
        $elements[] = GenericElement::title($title)
                            ->imageUrl($imageUrl)
                            ->subtitle($subtitle)
                            ->buttons($buttons)
                            ->toArray();
      }
     return GenericTemplate::toArray($elements);
  }

  public function getAvailability($status){
    switch (intval($status)) {
      case 1:
        return "Open";
      case 2:
        return "Closed";
      case 3: 
        return "Busy";
    }
  }

 public function confirmation($form, $companyDataF = null){
  $validate = $this->validate($form); 
    if($validate == true){
      $this->user();
      $companyData = ($companyDataF) ? $companyDataF : $this->tracker->getCompanyData();
      $name = ($companyDataF) ? $companyDataF["name"] : $companyData[0]['name'];
      $title = "Hi ".$this->user->getFirstName()." :) You are about to get ".$name.' '.$form['title'].' Form. Are you sure you want to continue?';
      $quickReplies[] = QuickReplyElement::title('No')->contentType('text')->payload($form['id'].'@qrFormCancel');
      $quickReplies[] = QuickReplyElement::title('Yes')->contentType('text')->payload($form['id'].'@qrFormContinue');
      return QuickReplyTemplate::toArray($title, $quickReplies);
    }
    return $this->duplicate($form);
  }

  public function emptyForm(){
    return ['text'  => 'No Forms Available'];
  }

  public function ask($question){
    return ['text' => $question];
  }

  public function error(){
    $this->user();
    return ['text' => 'Hi '.$this->user->getFirstName().', I am very sorry but you have missed something. I will take you form the start. Kindly select the options below.'];
  }

  public function validate($form){
    $fbId = Facebook::getDynamicField($this->messaging->getSenderId(), 'id');
    $data = [
        "company_id"  => $form['company_id'],
        "queue_form_id" => $form['id'],
        "facebook_user_id"  => $fbId
    ];
    $status = QueueCard::retrieve($data, "status");
    if($status == null)return true;
    else if(intval($status) == 3)return true;
    else if(intval($status) == 1 || intval($status) == 2)return false;
  }
  public function duplicate($form){
    $this->user();
    $companyName = Company::retrieve(['id' => $form['company_id']], 'name');
    $this->bot->reply(['text' => "Hi ".$this->user->getFirstName().", You're QCard at ".$companyName." is still active."],false);
    $fbId = Facebook::getDynamicField($this->messaging->getSenderId(), 'id');
    $data = [
        "company_id"  => $form['company_id'],
        "queue_form_id" => $form['id'],
        "facebook_user_id"  => $fbId
    ];
    $result = QueueCard::retrieve($data);
    return $this->qc->manageResult($result);
  }

}