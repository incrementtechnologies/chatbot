<?php
namespace App\Ilinya\Response\Facebook;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Ilinya\Http\Curl;
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\User;
use App\Ilinya\Bot;
use App\Ilinya\Tracker;
use App\Ilinya\API\Database as DB;
use App\Ilinya\API\QueueCardFields;
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




class DetailsResponse{
    private $user;
    private $messaging;
    private $curl;
    protected $bot;
    protected $tracker;
    protected $db_field = "temp_custom_fields_storage";


    public function __construct(Messaging $messaging){
        $this->messaging    = $messaging;
        $this->curl         = new Curl();
        $this->tracker      = new Tracker($messaging);
        $this->bot          = new Bot($messaging);
    } 

      /*
      Display all of the contents and text of Review   
    */
    public function user(){
        $user = $this->curl->getUser($this->messaging->getSenderId());
        $this->user = new User($this->messaging->getSenderId(), $user['first_name'], $user['last_name']);
    }

    public function viewDetails($qCard){
        /*
            1. Get Queue Card Fields
            2. Display using Generic Templates
        */
        $data = [
            "column"    => "queue_card_id",
            "value"     => $qCard
        ];
        $fields = QueueCardFields::retrieve($data);

        if(sizeof($fields) > 0){
            $elements = [];
            $i = 1;
            foreach ($fields as $field) {
              $imgUrl = "http://ilinya.com/wp-content/uploads/2017/09/step-";
              $question = $this->getQuestion($field['queue_form_field_id']);
              $buttons[] = ButtonElement::title("Edit")
                  ->type('postback')
                  ->payload($field['id'].'@pEditDetails')
                  ->toArray();

              $imgUrl .= $i.'.png';
              $elements[] = GenericElement::title($question)
                                  ->imageUrl($imgUrl)
                                  ->subtitle($field['value'])
                                  ->buttons($buttons)
                                  ->toArray();
              $buttons = null;
              $i++;
            }
        return GenericTemplate::toArray($elements);
        }else{
            return ["text" => "No Fields on this Queue Card"];
        }

    }
    public function getQuestion($fieldId){
       $controller = 'App\Http\Controllers\QueueFormFieldController';
       $request = new Request();
       $condition[] = [
          "column"  => "id",
          "clause"  => "=",
          "value"   => $fieldId
        ];
       $request['condition'] = $condition;
       $data = Controller::retrieve($request, $controller);
       return ($data)?$data[0]['description']:null;
    }

}