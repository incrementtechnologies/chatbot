<?php
namespace App\Ilinya\Response\Facebook;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Ilinya\Http\Curl;
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\User;
use App\Ilinya\Tracker;
use App\Ilinya\Bot;
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
use App\Ilinya\API\Company;
use App\Ilinya\API\QueueCard;
use App\Ilinya\API\QueueForm;
use App\Ilinya\API\QueueCardFields;


class QueueCardsResponse{
    private $user;
    private $messaging;
    private $curl;
    protected $tracker;
    protected $db_field = "temp_custom_fields_storage";
    protected $review;
    protected $bot;


    public function __construct(Messaging $messaging){
        $this->messaging = $messaging;
        $this->curl = new Curl();
        $this->tracker = new Tracker($messaging);
        $this->review  = new ReviewResponse($messaging);
        $this->bot     = new Bot($messaging);
    }  

    /*
      Display all of the contents and text of Review   
    */
    public function user(){
        $user = $this->curl->getUser($this->messaging->getSenderId());
        $this->user = new User($this->messaging->getSenderId(), $user['first_name'], $user['last_name']);
    }

    public function display(){
      $this->user();
      /*
        1. Get User Id via Facebok Id
        2. Get Queue Cards
        3. Display
      */
      $controller = 'App\Http\Controllers\FacebookUserController';
      $request = new Request();

      $condition [] = [
        'column'  => 'account_number',
        'clause'  => '=',
        'value'   => $this->messaging->getSenderId()
      ];

      $request['condition'] = $condition;
      $userField = Controller::retrieve($request, $controller);

      if($userField){
        /*
          1. Loop Queue Cards Heres
        */
          return $this->getQueueCardByUserId($userField[0]['id']);
      }
      else{
          return ['text' => "Hi ".$this->user->getFirstName()." :) You don't have transaction(s) yet. Kindly go to categories to make or get reservation to available establishments. Thank You :)"];
      }
    }

    public function getQueueCardByUserId($userId){
      $this->user();
      $controller = 'App\Http\Controllers\QueueCardController';
      $request = new Request();

      $condition [] = [
        'column'  => 'facebook_user_id',
        'clause'  => '=',
        'value'   => $userId
      ];

      $request['condition'] = $condition;
      $request['sort'] = ['created_at' => 'asc'];
      $qc = Controller::retrieve($request, $controller);

      if($qc){
        return $this->manageResult($qc, null);
      }
      else{
        return ['text' => "Hi ".$this->user->getFirstName()." :) You don't have Active QCard(s) yet. Kindly go to CATEGORIES to get QCard(s) to any available establishments. Thank You :)"];
      }
    }

    public function getStatus($status){
      switch ($status) {
        case 1: return "On Queue";
        case 2: return "Serving";
        case 3: return "Finished";
        default:
          # code...
          break;
      }
    }

    public function cancel($id){
      $companyId = QueueCard::retrieveById($id, 'company_id');
      $companyName = Company::retrieve(["id" => $companyId], "name");
      $result = $this->delete($id);
      if($result == 1){
        return ['text' => "You're QCard at ".$companyName." has been cancelled. Thank You :)"];
      }else if($result == 0){
        return ['text' => "QCard was not found :'( "];
      }
        return ['text' => "We're very sorry but the SERVER IS DOWN :'(  Please try again later or email us: support@ilinya.com for more information."];
    }

    public function postpone($id){
      /*
        1. Delete QCard
        2. Create new QCard
      */
      $prevQC = QueueCard::retrieveById($id, '*');
      $result = $this->delete($id);

      if($result == 1){
        //retrieve queue cards field
        //create new card
        $data = [
          "column" => "queue_card_id",
          "value"  => $id
        ];
        $fields = QueueCardFields::retrieve($data);
        echo json_encode($fields);
        return $this->createNewQueueCard($fields, $prevQC);
      }
      else if($result == 0){
        return ['text' => "QCard was not found :'( "];
      }
        return ['text' => "We're very sorry but the SERVER IS DOWN :'(  Please try again later or email us: support@ilinya.com for more information."];
    }

    public function createNewQueueCard($fields, $prevQC){
      if(sizeof($fields) > 0){
          $newCardId = QueueCard::create($prevQC);

          if($newCardId == false){
            return ["text" => "QCard still exist."];
          }
          else{
            $flag = true;
            foreach ($fields as $field) {
              $field['queue_card_id'] = $newCardId;
              $result = QueueCardFields::create($field);
              if($result == false)
                $flag = false;
            }

            if($flag == true){
                $this->user();
                $this->bot->reply(['text' => 'Hi '.$this->user->getFirstName()." :) Here's your new QCard."], false);
                return $this->manageResult(QueueCard::retrieveById($newCardId));
            }else{
              return ["Something was wrong during the creation of new card :'( Kindly ask the support at support@ilinya.com :'( "];
            }
          }
      }
      return ['text' => "Empty Fields :'("];
    }

    public function delete($id){
      $request = new Request();
      $controller = 'App\Http\Controllers\QueueCardController';
      $request['id'] = $id;
      return Controller::delete($request, $controller);
    }

    public function manageResult($result, $title = null){
      if(sizeof($result) > 0){
          $queueCards = '';
          $elements = [];
          foreach ($result as $card) {
            $cardId = $card['id'];
            $title =  ($title != null) ? $title : "QCard #:".$cardId."(".$this->getStatus($card['status']).")";
            $subtitle = 'Currently '.QueueCard::totalCurrentDayFinished($card['queue_form_id']) .'/'. QueueCard::totalOnQueue($card['queue_form_id']).PHP_EOL.$this->getEstimatedTime($card['queue_form_id']).PHP_EOL.$this->getFormName($card['queue_form_id']);
            $imageUrl = "http://ilinya.com/wp-content/uploads/2017/08/cropped-logo-copy-copy.png";
            $buttons = [];

            if($card['status'] == 1 || $card['status'] == 2){
              $buttons[] = ButtonElement::title("View Details")
                        ->type('postback')
                        ->payload($cardId.'@pQCViewDetails')
                        ->toArray();
              $buttons[] = ButtonElement::title("Cancel")
                        ->type('postback')
                        ->payload($cardId.'@pCancelQC')
                        ->toArray();
              $buttons[] = ButtonElement::title("Postpone")
                        ->type('postback')
                        ->payload($cardId.'@pPostponeQC')
                        ->toArray();
            }
            $elements[] = GenericElement::title($title)
                                ->imageUrl($imageUrl)
                                ->subtitle($subtitle)
                                ->buttons($buttons)
                                ->toArray();
          }
        return GenericTemplate::toArray($elements);
      }
      return ['text' => 'No Cards Found!'];
    }

    public function getEstimatedTime($formId){
      $current = QueueCard::totalCurrentDayFinished($formId);
      $last    = QueueCard::totalOnQueue($formId);
      $minimumTime = 2; // Minutes
      $totalTime = floatval($minimumTime) * (intval($last) - intval($current));
      echo $totalTime;
      $time = \Carbon\Carbon::now('Asia/Singapore')->addMinutes($totalTime);
      echo $time."<br />";
      return "Est. Time: ".$time->format('h:i A');
    }
    public function getFormName($formId){
      $data = [
        "column"  => "id",
        "value"   => $formId
      ];
      $result = QueueForm::retrieveByCustomField($data);

      if($result){
        $dataRequest = [
          "id"  => $result[0]['company_id']
        ];
        return $result[0]['detail'].PHP_EOL.'@'.Company::retrieve($dataRequest, 'name');
      }
      else{
        return null;
      }
    }

    public function informCancel($parameter){
        $this->user();
        $title = "Hi ".$this->user->getFirstName()." :) Are you sure you want to cancel this QCard?";
        $quickReplies[] = QuickReplyElement::title('No')->contentType('text')->payload('0,'.$parameter.'@qrQueueCardCancel');
        $quickReplies[] = QuickReplyElement::title('Yes')->contentType('text')->payload('1,'.$parameter.'@qrQueueCardCancel');
        return QuickReplyTemplate::toArray($title, $quickReplies);
    }

    public function informPostpone($parameter){
        $this->user();
        $title = "Hi ".$this->user->getFirstName()." :) Are you sure you want to postpone this QCard? I hope you understand that by taking this action your new QCard will be placed on the last.";
        $quickReplies[] = QuickReplyElement::title('No')->contentType('text')->payload('0,'.$parameter.'@qrQueueCardPostpone');
        $quickReplies[] = QuickReplyElement::title('Yes')->contentType('text')->payload('1,'.$parameter.'@qrQueueCardPostpone');
        return QuickReplyTemplate::toArray($title, $quickReplies);
    }

    public function noInform(){
      $this->user();
      return ['text' => "Hi ".$this->user->getFirstName()." :) I understand your action :P"];
    }


}