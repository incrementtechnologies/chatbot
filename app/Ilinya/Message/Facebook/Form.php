<?php
namespace App\Ilinya\Message\Facebook;


use App\Ilinya\Bot;
use App\Ilinya\Tracker;
use App\Ilinya\API\Database as DB;
use Illuminate\Http\Request;
use App\Ilinya\Message\Facebook\Codes;
use App\Ilinya\Response\Facebook\FormsResponse;
use App\Ilinya\Response\Facebook\PostbackResponse;
use App\Ilinya\Response\Facebook\ReviewResponse;
use App\Ilinya\Response\Facebook\SendResponse;
use App\Ilinya\Response\Facebook\SurveyResponse;
use App\Ilinya\Webhook\Facebook\Messaging;
use Illuminate\Support\Facades\Validator;
/*
    @API
*/

use App\Ilinya\API\Controller;
use App\Ilinya\API\Company;

class Form{

  public    $question;
  public    $reply;
  protected $messaging;
  protected $bot;
  protected $response;
  protected $tracker;
  protected $code;
  protected $review;
  protected $post;
  protected $db_field = "temp_custom_fields_storage";
  protected $pageID = "133610677239344";
  protected $send;
  protected $survey;

  function __construct(Messaging $messaging){
    $this->messaging  = $messaging;
    $this->bot        = new Bot($messaging);
    $this->response   = new FormsResponse($messaging);
    $this->tracker    = new Tracker($messaging);
    $this->code       = new Codes();
    $this->post       = new PostbackResponse($messaging);
    $this->review     = new ReviewResponse($messaging);
    $this->send       = new SendResponse($messaging);
    $this->survey     = new SurveyResponse($messaging);
  }
  
 public function retrieveForms($companyId){
    $forms = $this->request('App\Http\Controllers\QueueFormController','company_id', $companyId, 10, null);
    $this->manageForms($forms);
  }

  public function manageForms($forms){
    if(sizeof($forms) > 0){
        /*
          Check if have existing reservation
        */
        if(sizeof($forms) > 1){
          //Show Forms
          $this->bot->reply($this->response->selectForms($forms),false);
        }
        else{
          //Direct Display
          $data = [
              "column"  => "id",
              "value"   => $forms[0]['company_id']
          ];
          $company = Company::retrieveAll($data);
          $this->bot->reply($this->response->confirmation($forms[0], (sizeof($company) > 0) ? $company[0] : null),false);
        }
    }
    else{
      $this->bot->reply($this->response->emptyForm(), false);
    }
  }

  public function retrieveFields($formId){
    /*
      Guide
      1. Retrieve Fields
      2. Insert to temp
      3. Ask
      4. Get Reply
      5. Update
    */

    $formId = $this->tracker->getFormId();
    $stage  = $this->tracker->getStage();
    if($formId && $stage == $this->code->stageForm){
      $this->retrieve();
    }
    else{
      $this->bot->reply($this->response->error(), false);
      $this->bot->reply($this->post->categories(), false);
    }

  }

  public function reply($replyText){
    //Before
    //Get tracker ID
    //Get Field Id
    //Check Stage is 2000
    $trackerId = $this->tracker->getId();
    $stage     = $this->tracker->getStage();
    $fieldId   = $this->field('id');

    //Validate
    //$validation = false;
    $validation = $this->validate($replyText);

    //After retrieve again
    //
    if($validation ==  true){
      //Update Field
      //Reset Reply to 0
      //Retrieve new Field
      $this->update($replyText, $fieldId);
      $this->retrieve(null, null);
    }
    else{
      //Ask again
      /*
        1. Get Field Description\
        $controller,$column, $vaue, $limit = null, $sort = null
      */
       $desc = $this->field('description');
       $this->bot->reply("I'm sorry but you replied an invalid ".$this->field('type')." :'( Again, ".$desc, true);
    }
  }

  public function validate($replyText){
    $type = $this->field('type');
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
        return true;
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
    return $flag;
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
      $request = new Request();
      $controller = 'App\Http\Controllers\QueueFormFieldController';

      $condition[] = [  
          "column"  => 'queue_form_id',
          "clause"  => "=",
          "value"   => $this->tracker->getFormId()
      ];
      $condition[] = [
          "column"  => 'sequence',
          "clause"  => "=",
          "value"   => $this->tracker->getFormSequence()
      ];
      $request['condition'] = $condition;
      $field = Controller::retrieve($request, $controller);

      return (sizeof($field) > 0)?$field[0][$column]:null;
  }

  public function retrieve($formId = null, $sequenceFlag = null){

    /*
      Check if Empty
      Check if Finish
      Get Field Name
    */

    $controller = 'App\Http\Controllers\QueueFormFieldController';
    $formSequence = $this->tracker->getFormSequence();
    $field = null;

    if($formSequence){
      $newSequence = ($sequenceFlag == true)?intval($formSequence):intval($formSequence) + 1;
      $request = new Request();
      $condition[] = [
          "column"  => 'queue_form_id',
          "clause"  => "=",
          "value"   => $this->tracker->getFormId()
      ];
      $condition[] = [
          "column"  => 'sequence',
          "clause"  => "=",
          "value"   => $newSequence
      ];
      $request['condition'] = $condition;
      $request['limit']     = 1;
      $field = Controller::retrieve($request, $controller);
    }
    else{
      $field = $this->request($controller,'queue_form_id', $formId, 1, 'sequence');
    }  

    if($field){
      $this->insert($field[0]['id'],$field[0]['sequence']);
      $this->bot->reply($this->response->ask($field[0]['description']), false);
    }
    else{
      /*
        Check if sequence is not empty
      */
        if($formSequence){
          /*
            1. Update Stage to review
            2. Set Reply to 0
            3. Review Details
          */
          $data = [
            "reply" => null,
            "form_sequence" => null,
            "stage" => $this->code->stageReview
          ];

          if($this->tracker->getCompanyId() == 6){
            $this->send->submit();
            $this->bot->reply($this->survey->appreciate(), false);
          }
          else{
            $this->bot->reply($this->review->inform(), true);
            $this->bot->reply($this->review->display(), false); 
          }
          $this->tracker->update($data);
        }
        else{ 
          $this->bot->reply("Not Available!", true);
          $this->bot->reply($this->post->categories(), false); 
        }
    }
  }

  public function insert($fieldId, $sequence){
      $data = [
        "track_id"      => $this->tracker->getId(),
        "field_id"       => $fieldId
      ];
      DB::insert($this->db_field, $data);
      $sData = [
        "form_sequence"  => $sequence,
        "reply"          => $this->code->replyStageForm
      ];
      $this->tracker->update($sData);
  }

  public function update($reply, $fieldId){
      $condition = [ 
        ['track_id', "=", $this->tracker->getId()],
        ['field_id', '=', $fieldId]
      ];
      $data = ['field_value' => $reply];
      DB::update($this->db_field, $condition, $data);
  }

  public function delete(){
    //Clear all fields and set to null
    //Transfer Fields
  }


   public function request($controller,$column, $vaue, $limit = null, $sort = null){
    $request = new Request();
    $condition[] = [
      "column"  => $column,
      "clause"  => "=",
      "value"   => $vaue
    ];
    
    $request['condition'] = $condition;
    if($limit)$request['limit'] = $limit;
    if($sort)$request['sort'] = [$sort => "asc"]; 
    return Controller::retrieve($request, $controller);
  }
  
}