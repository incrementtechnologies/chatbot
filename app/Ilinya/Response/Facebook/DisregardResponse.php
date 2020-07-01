<?php

namespace App\Ilinya\Response\Facebook;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Ilinya\Http\Curl;
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\User;
use App\Ilinya\Tracker;
use App\Ilinya\API\Database as DB;
use App\Ilinya\Templates\Facebook\QuickReplyTemplate;
use App\Ilinya\Templates\Facebook\ButtonTemplate;
use App\Ilinya\Templates\Facebook\GenericTemplate;
use App\Ilinya\Templates\Facebook\LocationTemplate;
use App\Ilinya\Templates\Facebook\ListTemplate;
use App\Ilinya\Templates\Facebook\ButtonElement;
use App\Ilinya\Templates\Facebook\GenericElement;
use App\Ilinya\Templates\Facebook\QuickReplyElement;
use App\Ilinya\API\Controller;
use App\Ilinya\API\CustomFieldModel;

class DisregardResponse{
    private $user;
    private $messaging;
    private $curl;
    protected $tracker;
    protected $db_field = "temp_custom_fields_storage";


    public function __construct(Messaging $messaging){
        $this->messaging = $messaging;
        $this->curl = new Curl();
        $this->tracker = new Tracker($messaging);
    }  

    /*
      Display all of the contents and text of Review   
    */
    public function user(){
        $user = $this->curl->getUser($this->messaging->getSenderId());
        $this->user = new User($this->messaging->getSenderId(), $user['first_name'], $user['last_name']);
    }

    public function inform(){
        $this->user();
        $companyData = $this->tracker->getCompanyData();
        //echo json_encode($companyData);
        $mode = (intval($companyData[0]['id']) == 6 || $companyData[0]['id'] == '6') ? "survey" : "transaction";
        $title = "Hi ".$this->user->getFirstName()." :) Are you sure you want to disregard your ".$mode." to ".$companyData[0]['name']."?";
        $quickReplies[] = QuickReplyElement::title('No')->contentType('text')->payload('0@qrDisregard');
        $quickReplies[] = QuickReplyElement::title('Yes')->contentType('text')->payload('1@qrDisregard');
        return QuickReplyTemplate::toArray($title, $quickReplies);
    }
}