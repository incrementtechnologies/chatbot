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
use App\Ilinya\API\Facebook;
use App\Ilinya\ImageGenerator;
use Carbon\Carbon;


class SurveyResponse{
    private $user;
    private $messaging;
    private $curl;
    protected $tracker;
    protected $db_field = "temp_custom_fields_storage";
    protected $userId;
    protected $cardId;

    public function __construct(Messaging $messaging){
        $this->messaging = $messaging;
        $this->curl = new Curl();
        $this->tracker = new Tracker($messaging);
    }  

    public function user(){
        $user = $this->curl->getUser($this->messaging->getSenderId());
        $this->user = new User($this->messaging->getSenderId(), $user['first_name'], $user['last_name']);
    }


    public static function requestForSurvey($accountNumber){
        /*
            1. Get name using facebook ID
        */
        $name = Facebook::getDynamicField($accountNumber, 'full_name');
        $title = "Hi ".$name." :) We at iLinya would like to ask your help to take a survey. This can help us to improve the application and to know your side also if iLinya can really help you in the Future. We really appreciate your effort if you help us. Thank you :)";
        $quickReplies[] = QuickReplyElement::title('No Thanks!')->contentType('text')->payload('0@qrSurvey');
        $quickReplies[] = QuickReplyElement::title('Take a Survey')->contentType('text')->payload('1@qrSurvey');
        $response = QuickReplyTemplate::toArray($title, $quickReplies);
        
        $data = [
                "facebook_id" => $accountNumber,
                "status"     => 327,
                "stage"      => 2000,
                "company_id" => 6,
                "form_id"    => 6,
                "form_sequence" => null,
                "reply"     => 2
        ];
        DB::insert("bot_status_tracker", $data);

        return $response;
    }

    public function appreciate(){
        $this->user();
        return ['text' => "Hi ".$this->user->getFirstName()." :) We really appreciate your participation :) We're excited to serve you soon :) "];
    }
}