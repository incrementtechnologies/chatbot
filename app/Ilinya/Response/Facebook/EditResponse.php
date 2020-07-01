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
use App\Ilinya\Response\Facebook\ReviewResponse;
use App\Ilinya\API\Controller;
use App\Ilinya\API\CustomFieldModel;
use Illuminate\Support\Facades\Validator;


class EditResponse{
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


    public function manage($custom){
      /*
        1. Retreive Field Question
        2. Set Reply to Edit
        3. Validate
        4. 
      */
        $customField = CustomFieldModel::getFieldById($custom['parameter']);

        if($customField){
            /*
              1. Get Question in Queue Fields
            */
            $queueForm = $this->retrieve($customField[0]['field_id']);
            if($queueForm){
              $dataTracker = [
                'reply' => 3,
                'edit_field_id' => $custom['parameter']
              ];
              $this->tracker->update($dataTracker);
              return ['text' => $queueForm[0]['description']];
            }
            else{
              return ["text" => "Field Not Found"];
            }
        }
        else{
          return ['text' => "Not Found"];
        }
    }

    public function retrieve($id){
      $controller = 'App\Http\Controllers\QueueFormFieldController';
      $request = new Request();
      $condition[] = [
          "column"  => 'id',
          "clause"  => "=",
          "value"   => $id
      ];
      $request['condition'] = $condition;
      $request['limit']     = 1;
     return Controller::retrieve($request, $controller);
    }

    public function update($reply){
      $db_field = "temp_custom_fields_storage";

      $condition = [
          ['id', '=', $this->tracker->getEditFieldId()]
      ];
      $data = ['field_value' => $reply];
      $result = DB::update($db_field, $condition, $data);

      if($result){
        return $this->review->display();
      }
      else{
        return ['text' => 'Unable to update.'];
      }
    }

    public function inform(){
      $dataTracker = [
                'reply' => NULL,
                'edit_field_id' => NULL
              ];
      $this->tracker->update($dataTracker);
      return ['text' => 'Successfully Edited! Kindly check review again your forms.'];
    }

    public function validate($replyText){
    $id   = $this->field('field_id');
    $type = $this->fieldByController('type', $id);
    $description = $this->fieldByController('description', $id);
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

    public function field($column){
      $db_field = "temp_custom_fields_storage";

      $condition = [
          ['id', '=', $this->tracker->getEditFieldId()]
      ];
      $result = DB::retrieve($db_field, $condition, null);

      if(sizeof($result) > 0){
          return $result[0][$column];
      }
      else
        return null;
    }

    public function fieldByController($column,$id){
      $request = new Request();
      $controller = 'App\Http\Controllers\QueueFormFieldController';

      $condition[] = [  
          "column"  => 'id',
          "clause"  => "=",
          "value"   => $id
      ];
      $request['condition'] = $condition;
      $field = Controller::retrieve($request, $controller);

      return (sizeof($field) > 0)?$field[0][$column]:null;
  }
}