<?php


namespace App\Ilinya\Response\Facebook;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

/*
    @Providers
*/
use App\Ilinya\Http\Curl;
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\User;

/*
    @Template
*/
use App\Ilinya\Templates\Facebook\QuickReplyTemplate;
use App\Ilinya\Templates\Facebook\ButtonTemplate;
use App\Ilinya\Templates\Facebook\GenericTemplate;
use App\Ilinya\Templates\Facebook\LocationTemplate;
use App\Ilinya\Templates\Facebook\ListTemplate;
use App\Ilinya\Templates\Facebook\PersistentMenuTemplate;

/*
    @Elements
*/

use App\Ilinya\Templates\Facebook\ButtonElement;
use App\Ilinya\Templates\Facebook\GenericElement;
use App\Ilinya\Templates\Facebook\QuickReplyElement;
use Storage;

/*
    @API
*/
use App\Ilinya\API\Controller;


class PostbackResponse{
    public  $ERROR            = "I'm sorry but I can't do what you want me to do :'(";
    private $user;
    private $messaging;
    private $curl;

    public function __construct(Messaging $messaging){
        $this->messaging = $messaging;
        $this->curl = new Curl();
    }   


    public function user(){
        $user = $this->curl->getUser($this->messaging->getSenderId());
        $this->user = new User($this->messaging->getSenderId(), $user['first_name'], $user['last_name']);
    }

    public function testMessage(){
        $this->user();
        // $message = "Hi ".$this->user->getFirstName()." :) I'm ILinya, I can help you to get ticket or make reservation to any establishment(s) or event(s) you want. I'm currently on a TEST MODE right now, so all of the data are just sample and not really connected to the establishment that will be mentioned in our conversation later. ";
        $message = "Hi ".$this->user->getFirstName().", how could I help you?";
        return ["text" => $message];
    }

    public function persistentMenu(){
        $response = PersistentMenuTemplate::toArray();
        return $response;
    }
    public function banner(){
        $title = "Mezzo Hotel";
        $subtitle = "A new diversion of luxury";
        $elements[] = GenericElement::title($title)
                            ->imageUrl('https://mezzohotel.com/img/logo.png')
                            ->subtitle($subtitle)
                            ->buttons(null)
                            ->toArray();
        $response =  GenericTemplate::toArray($elements);
        return $response;
    }
    public function start(){
        $this->user();
        $title =  "Greetings ".$this->user->getFirstName().",thank you for your interest in Mezzo Hotel.For us to help you with your inquiry please select the following options below.";
        $subtitle = "Kindly click the buttons to navigate.";
        $imageUrl = "http://ilinya.com/wp-content/uploads/2017/08/cropped-logo-copy-copy.png";
        $menus= array(
            array("title"=>"ROOM RATES" , "isWebview"=>false),
            array("title"=>"BANQUET PACKAGES","isWebview"=>false),
            array("title"=>"CONCERN/INQUIRY" ,"isWebview"=>true,
            "url" => "https://mezzohotel.com/inquiry/other"));
        $buttons =[];
        foreach ($menus as $menu) {
            $payload = preg_replace('/\s+/', '_', $menu["title"]);
            if ($menu["isWebview"]) {
                $buttons[] = ButtonElement::title($menu['title'])
                    ->type('web_url')
                    ->url($menu["url"])
                    ->ratio("full")
                    ->messengerExtensions()
                    ->fallbackUrl($menu["url"])
                    ->toArray();
            } else {
                $buttons[] = ButtonElement::title($menu["title"])
                            ->type('postback')
                            ->payload(strtolower($payload).'@pCategorySelected')
                            ->toArray();
            }
        }
        $response = ButtonTemplate::toArray($title,$buttons);

        return $response;
    }

    public function priorityError(){
        $quickReplies[] = QuickReplyElement::title('Yes')->contentType('text')->payload('priority@yes');
        $quickReplies[] = QuickReplyElement::title('No')->contentType('text')->payload('priority@no');
        return QuickReplyTemplate::toArray('Are you sure you want cancel your current conversation?', $quickReplies);
    }

    public function packageInquiryContactInfo(){
        $title =  "If you need further assistance immediately please don't hesitate to contact the following numbers.";
        $buttons=[];
        $contacts= array( 
            array("payload"=> "+639177001599", "title"=>"Sales") ,
            array("payload"=> "+639226590829", "title"=>"Reservations"),
            array("payload"=> "+63322310777", "title"=>"Hotel Trunkline")
          );
          foreach ($contacts as $contact) {
              # code...
              $buttons[] = ButtonElement::title($contact["title"])
                         ->type('phone_number')
                         ->payload($contact['payload'])
                         ->toArray();
          }
        $response = ButtonTemplate::toArray($title,$buttons);
        return $response;
    }
}