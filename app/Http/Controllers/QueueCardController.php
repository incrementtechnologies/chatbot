<?php

namespace App\Http\Controllers;
use App\QueueCard as DBItem;
use Illuminate\Http\Request;

class QueueCardController extends APIController
{
  function __construct(){
    $this->model = new DBItem();
    $this->useUserCompanyID = false;
    $this->editableForeignTable = array(
      'queue_card_fields'
    );
    $this->notRequired = array(
      'facebook_user_id',
      'status',
      'datetime_finished',
      'datetime_served'
    );
    $this->requiredForeignTable = array(
      'facebook_user'
    );
    $this->defaultValue = array(
      'company_id' => $this->getUserCompanyID(),
      'status' => 1,
      'user_id' => 0
    );
  }
  public function update(Request $request){
    $reqArray = $request->toArray();
    if(isset($reqArray['status'])){
      switch($reqArray['status'] * 1){
        case 2:
          $reqArray['datetime_served'] = date('Y-m-d H:i:s', time());
          break;
        case 3:
          $reqArray['datetime_finished'] = date('Y-m-d H:i:s', time());
          break;
      }
    }
    $this->updateEntry($reqArray);
    return $this->output();
  }
  public function getAverageQueueTime(){
    $currentDate = date('Y-m-d H:i:s', time());
    $startDate = date("Y-m-d 00:00:00", strtotime("-1 week"));
    $result = $this->model
      ->where('datetime_finished', '>', $startDate)
      ->where('datetime_served', '<=', $currentDate)
      ->get()->toArray();
    $totalTimeDifference = 0;
    foreach($result as $queueCard){
      $date1 = strtotime($queueCard['datetime_finished']);
      $date2 = strtotime($queueCard['datetime_served']);
      $totalTimeDifference = $date1 - $date2;
    }
    $this->response['data'] = number_format($totalTimeDifference / count($queueCard), 2);
    return $this->output();
  }
}
