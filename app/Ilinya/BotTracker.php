<?php

namespace App\Ilinya;

use App\Logs;
use App\Ilinya\API\Controller;
use App\Ilinya\API\Database as DB;
use Illuminate\Http\Request;
use App\Ilinya\Message\Facebook\Codes;
use App\Ilinya\Webhook\Facebook\Messaging;

class BotTracker{

  protected $id;
  
  protected $status;

  protected $input;

  protected $stage;

  protected $db_tracker = "dialog_flows";

  protected $messaging;

  protected $code;


  /**Tracker vars */

  function __construct(Messaging $messaging){
    $this->messaging = $messaging;
    $this->code = new Codes();
    $this->retrieve();
  }

  public function getId(){
    return $this->id;
  }

  public function getStage(){
    return $this->stage;
  }

  public function getInput(){
    return $this->input;;
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

  public function insert($stage  , $msg = null){
        if ($msg == null) {
          if ($this->messaging->getMessage()!=null) {
            if ($this->messaging->getMessage()->getText()!=null) {
              $msg = $this->messaging->getMessage()->getText();
            } else {
              $msg = $this->messaging->getMessage()->getQuickReply();
            }
          }    
        }
        $data = [
          "userID" => $this->messaging->getSenderId(),
          "input"  => $msg,
          "stage"  => $stage,
        ];
        $condition = ['userID'=>$this->messaging->getSenderId()];
        $flag = DB::retrieve($this->db_tracker, $condition, null);
        if(!$flag){
          DB::insert($this->db_tracker, $data);
        }else{
          DB::update($this->db_tracker, $condition, $data);
        }
  }

  public function update($data){
        $condition = ['userID'=>$this->messaging->getSenderId()];
        DB::update($this->db_tracker, $condition, $data);
  }

  public function retrieve(){
    $condition = ['userID'=>$this->messaging->getSenderId()];
    $result = DB::retrieve($this->db_tracker, $condition);
      if($result){
          foreach ($result as $key) {
              $this->id           = $key['id'];
              $this->stage        = $key['stage'];     
              $this->input        = $key['input'];       
          }
      }
  }

  public function delete(){
    $condition = [
          'userID'=>$this->messaging->getSenderId()
    ];
    DB::delete($this->db_tracker, $condition);
  }

  public function remove(){
      $data =[
          "userID"=>$this->messaging->getSenderId(),
          "recepientID"=>$this->messaging->getRecipientId(),
          "type"=> $this->messaging->getType(),
      ];

      if ($this->messaging->getType() =='postback') {
          $data['message'] = $this->messaging->getPostback()->getPayload();
      }else{
          if ($this->messaging->getMessage()->getQuickReply()) {
              # code...
              $msg =$this->messaging->getMessage()->getQuickReply();
              $data['message'] =strtolower( $msg['payload'].$msg['parameter']);
          } else {
              $data['message'] = strtolower($this->messaging->getMessage()->getText());
          }
      }
      $delete = Logs::where($data)->delete();
  }
}