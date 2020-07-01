<?php

namespace App\Ilinya\Helper;
use Illuminate\Support\Facades\Validator;
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\Tracker;
use App\Ilinya\API\QueueFormFields;
use App\Ilinya\API\QueueCardFields;

class Validation{

    protected $tracker;

    function __construct(Messaging $messaging){
      $this->tracker = new Tracker($messaging);
    }

    public function validate($replyText){
    $editFieldId   = $this->tracker->getEditFieldId();
    $fieldId = $this->getQueueFormFieldIdByQCardFields($editFieldId);
    $type = QueueFormFields::retrieveByCustom($fieldId, 'type');
    $description = QueueFormFields::retrieveByCustom($fieldId, 'description');
    $flag = true;
    

    //Get Field Validation Settings
    //If valid, update and ask new field
    //Else, re ask
    switch ($type) {
      case 'email':
        # code..
        $text = array('email' => $replyText);
        $flag = $this->validateEmail($text);
        break;
      case 'text':
        $flag = true;
        break;
      case 'number':
        #
        $text = array('number' => $replyText);
        $flag = $this->validateNumber($text);
        break;
      default:
        # code...
        break;
    }
    $response = array(
      'status' => $flag,
      'type'   => $type,
      'description' => $description
    );
    echo json_encode($response);
    return $response;
  }

  public function getQueueFormFieldIdByQCardFields($id){
    $data = [
      "column"  => "id",
      "value"   => $id
    ];
    $field = QueueCardFields::retrieve($data);
    if(sizeof($field) > 0) return $field[0]['queue_form_field_id'];
    return null;
  }

  public function validateEmail($text){
    $validation = array('email' => 'required|email'); 
    return $this->validateReply($text, $validation);
  }
  
  public function validateNumber($text){
    $validation = array('number' => 'required|numeric');
    return $this->validateReply($text, $validation);
  }


  public function validateReply($text, $validation){
    $validator = Validator::make($text, $validation);
    if($validator->fails()){
      return false;
    }
    else
      return true;
  }
}