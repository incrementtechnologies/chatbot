<?php

namespace App\Ilinya;


use App\Ilinya\API\Controller;
use App\Ilinya\API\Database as DB;
use Illuminate\Http\Request;
use App\Ilinya\Message\Facebook\Codes;
use App\Ilinya\Webhook\Facebook\Messaging;

class Tracker{

  protected $id;
  
  protected $status;

  protected $prevStatusError;

  protected $stage;
  
  protected $category;

  protected $companyId;

  protected $formId;

  protected $formSequence;

  protected $searchOption;

  protected $reply;

  protected $db_tracker = "bot_status_tracker";

  protected $messaging;

  protected $code;

  protected $pageID;

  protected $companyData;

  protected $editFieldId;


  function __construct(Messaging $messaging){
    $this->pageID = (env("FB_TOKEN_STATUS") == true) ? "133610677239344" : "136273293774342";
    $this->messaging = $messaging;
    $this->code = new Codes();
    $this->retrieve();
  }

  public function getPrevStatusError(){
    return $this->prevStatusError;
  }
  public function getEditFieldId(){
    return $this->editFieldId;
  }

  public function getId(){
    return $this->id;
  }

  public function getCompanyData(){
    return $this->companyData;
  }

  public function getFormId(){
    return $this->formId;
  }

  public function getFormSequence(){
    return $this->formSequence;
  }

  public function getReplyStage(){
    return $this->reply;
  }

  public function getSearchOption(){
    return $this->searchOption;
  }

  public function getPrevStatus(){
    return $this->status;
  }

  public function getCategory(){
    return $this->category;
  }

  public function getCompanyId(){
    return $this->companyId;
  }

  public function getStage(){
    return $this->stage;
  }

  public function getStatus($custom){
    $prev = $this->status;
    $current = $this->code->getCode($custom);
    $response = array();

    if($current == $this->code->read){
      $response = [
        "status"  => $this->code->read,
        "stage"   => null,
        "tracker_flag"  => 0
      ];
    }
    else if($current == $this->code->delivery){
      $response = [
        "status"  => $this->code->delivery,
        "stage"   => null,
        "tracker_flag"  => 0
      ];
    }
    else if(!$prev){
      $response = [
        "status"  => $this->code->pStart,
        "stage"   => $this->code->stageStart,
        "tracker_flag"  => 1
      ];
    }
    else if($current <= $this->code->pCategories && $this->stage >= $this->code->stageForm){
      $response = [
        "status"  => $this->code->error,
        "stage"   => $this->code->stageForm,
        "tracker_flag"  => 4
      ];
      echo json_encode($response);
    }
    else if($current < $this->code->message && $current >= $this->code->postback){
      $response = [
        "status"  => $this->code->postback,
        "stage"   => null,
        "tracker_flag"  => 2
      ];  
    }
    else if($current < $this->code->error && $current >= $this->code->message){
      $response = [
        "status"  => $this->code->message,
        "stage"   => null,
        "tracker_flag"  => 3
      ];
    }
    else{
      $response = [
        "status"  => $this->code->error,
        "stage"   => null,
        "tracker_flag"  => 0
      ];
    }
    return $response;
  }

  public function insert($status, $stage, $category = null){
       if($this->messaging->getSenderId() != $this->pageID){
        $data = [
          "facebook_id" => $this->messaging->getSenderId(),
          "status"      => $status,
          "stage"       => $stage
        ];
        if($category)$data['category'] = $category;
        
        $condition = [
            ['facebook_id','=',$this->messaging->getSenderId()]
        ];
        $flag = DB::retrieve($this->db_tracker, $condition, null);
        if(!$flag)
        DB::insert($this->db_tracker, $data);
    }
  }

  public function insertSurvey($data){
     if($this->messaging->getSenderId() != $this->pageID){
        $condition = [
            ['facebook_id','=',$this->messaging->getSenderId()]
        ];
        $flag = DB::retrieve($this->db_tracker, $condition, null);
        if(!$flag){
          $data['facebook_id'] = $this->messaging->getSenderId();
          return DB::insert($this->db_tracker, $data); 
        }
      }
  }

  public function update($data){
        $condition = [
            ['facebook_id','=',$this->messaging->getSenderId()]
        ];
        DB::update($this->db_tracker, $condition, $data);
  }

  public function retrieve(){

    if($this->messaging->getSenderId() != $this->pageID){
      $condition = [
          ['facebook_id','=',$this->messaging->getSenderId()]
      ];
      $result = DB::retrieve($this->db_tracker, $condition);

      if($result){
          foreach ($result as $key) {
              $this->id           = $key['id'];
              $this->status       = $key['status'];
              $this->stage        = $key['stage'];     
              $this->category     = $key['business_type_id'];       
              $this->companyId    = $key['company_id'];
              $this->formId       = $key['form_id'];
              $this->formSequence = $key['form_sequence'];
              $this->searchOption = $key['search_option'];
              $this->reply        = $key['reply'];  
              $this->editFieldId  = $key['edit_field_id'];
              $this->prevStatusError = $key['prev_status'];
          }
          $this->retrieveCompanyData($this->companyId);
      }
    }
    else{
      $this->status = null;
    }
  }

  public function delete(){
    $condition = [
          ['facebook_id','=',$this->messaging->getSenderId()]
    ];

    DB::delete($this->db_tracker, $condition);
  }

  public function retrieveCompanyData($id){
     $request = new Request();
     $condition[] = [
      "column"  => "id",
      "clause"  => "=",
      "value"   => $id
    ];
     $request['condition'] = $condition;
     $result = Controller::retrieve($request, "App\Http\Controllers\CompanyController");
     $this->companyData = $result;
  }

}