<?php
namespace App\Ilinya\Response\Facebook;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Ilinya\Http\Curl;
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\User;
use App\Ilinya\Tracker;
use App\Ilinya\API\Database as DB;
use App\Ilinya\API\QueueCardFields;
use App\Ilinya\API\QueueFormFields;
use App\Ilinya\Templates\Facebook\QuickReplyTemplate;
use App\Ilinya\Templates\Facebook\ButtonTemplate;
use App\Ilinya\Templates\Facebook\GenericTemplate;
use App\Ilinya\Templates\Facebook\LocationTemplate;
use App\Ilinya\Templates\Facebook\ListTemplate;
use App\Ilinya\Templates\Facebook\ButtonElement;
use App\Ilinya\Templates\Facebook\GenericElement;
use App\Ilinya\Templates\Facebook\QuickReplyElement;
use App\Ilinya\Response\Facebook\ReviewResponse;
use App\Ilinya\API\Controller;
use App\Ilinya\Helper\Validation;


class EditDetailsResponse{
    private $user;
    private $messaging;
    private $curl;
    protected $tracker;
    protected $db_field = "temp_custom_fields_storage";
    protected $review;


    public function __construct(Messaging $messaging){
        $this->messaging = $messaging;
        $this->curl = new Curl();
        $this->tracker = new Tracker($messaging);
        $this->review  = new ReviewResponse($messaging);
    }  

    /*
      Display all of the contents and text of Review   
    */
    public function user(){
        $user = $this->curl->getUser($this->messaging->getSenderId());
        $this->user = new User($this->messaging->getSenderId(), $user['first_name'], $user['last_name']);
    }

    public function manage($qCardFieldId){
      /*
        1. retrieve Queueu Card
        2. retrieve Form Field
      */
        $data = [
          "column"  => "id",
          "value"   => $qCardFieldId
        ];

        $feilds = QueueCardFields::retrieve($data);
        if(sizeof($feilds) > 0){

          $fData = [
            "column"  => "id",
            "value"   => $feilds[0]['queue_form_field_id']
          ];
          $formField = QueueFormFields::retrieve($fData);
          if(sizeof($formField) > 0){
            $data = [
              "reply" => 4,
              'edit_field_id' => $qCardFieldId
            ];
            $this->tracker->update($data);
          }else{}
          return ($formField)?$formField[0]['description']:null;
        }
    }

    public function update($reply){
      /*
        2. update Queue Card Field
        3. Set tracker reply and edit_field to emply
      */
        $data = [
          "id"    => $this->tracker->getEditFieldId(),
          "value" => $reply
        ];
        $result = QueueCardFields::update($data);
        
        if($result == true)
          return $this->inform();
          return ["text" => "I'm sorry, Unable to Update :'( Something was wrong with the server. Kindly, contact us at support@ilinya.com"];
    }

    public function inform(){
      $dataTracker = [
                'reply' => NULL,
                'edit_field_id' => NULL
              ];
      $this->tracker->update($dataTracker);
      return ['text' => "Successfully Edited :)"];
    }




}