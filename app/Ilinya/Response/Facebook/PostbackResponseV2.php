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
use App\Ilinya\Templates\Facebook\PersistentMenuElement;
use Storage;

/*
    @API
*/
use App\Ilinya\API\Controller;


class PostbackResponseV2{
    public  $ERROR = "I'm sorry but I can't do what you want me to do :'(";
    private $user;
    private $messaging;
    private $curl;

    public function __construct(Messaging $messaging){
        $this->messaging = $messaging;
        $this->curl = new Curl();
    }   


    public function getUser(){
        $user = $this->curl->getUser($this->messaging->getSenderId());
        $this->user = new User($this->messaging->getSenderId(), $user['first_name'], $user['last_name']);
    }

    public function testMessage(){
        $this->getUser();
        // $message = "Hi ".$this->user->getFirstName()." :) I'm ILinya, I can help you to get ticket or make reservation to any establishment(s) or event(s) you want. I'm currently on a TEST MODE right now, so all of the data are just sample and not really connected to the establishment that will be mentioned in our conversation later. ";
        $message = "Hi ".$this->user->getFirstName().", how could I help you?";
        return ["text" => $message];
    }

    public static function persistentMenu(){
        $menus=  array(
            array(
                "title" => "Bookings",
                "isWebview" => true,
                'url' => 'https://mezzohotel.com/booking-inquiry'
            ),
            array(
                "title" => "Promos",
                "isWebview" => true,
                'url' => 'https://mezzohotel.com/promos'
            ),
            array(
                "title" =>  "General FAQ",
                "isWebview" =>  true,
                "url" => "https://mezzohotel.com/#faq"
            )
        );
        $actions =[];
        foreach ($menus as $menu) {
            $payload = preg_replace('/\s+/', '_', $menu["title"]);
            if ($menu["isWebview"]) {
                $actions[] = PersistentMenuElement::title($menu['title'])
                    ->type('web_url')
                    ->url($menu["url"])
                    ->ratio("full")
                    ->toArray();
            } else {
                $actions[] = PersistentMenuElement::title($menu["title"])
                            ->type('postback')
                            ->payload('@pCategorySelected')
                            ->toArray();
            }
        }
        $response = PersistentMenuTemplate::toArray($actions);
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
        $this->getUser();

        $title =  "Hello ".$this->user->getFirstName()."! Thank you for messaging Mezzo Hotel, Cebu’s four-star business hotel offering a new dimension of luxury to its guests.\n\nI’m Sean, the Mezzo chatbot. Please follow the steps and answer my questions precisely so I can assist you better.\n\nPlease select the category of your request or question:";
        $imageUrl = "http://ilinya.com/wp-content/uploads/2017/08/cropped-logo-copy-copy.png";
        $menus= array(
            array(
                "title"=>"Bookings",
                "isWebview" => true,
                "url" => "https://mezzohotel.com/booking-inquiry",
            ),
            array(
                "title"=>"Promos",
                "isWebview" => true,
                "url" => "https://mezzohotel.com/promos",
            ),
            array(
                "title"=>"General FAQ",
                "isWebview" => true,
                "url" => "https://mezzohotel.com/#faq",
            )
        );
        $buttons =[];
        foreach ($menus as $menu) {
            $payload = preg_replace('/\s+/', '_', $menu["title"]);
            $buttons[] = ButtonElement::title(ucwords(strtolower( $menu['title'])))
            ->type('web_url')
            ->url($menu["url"])
            ->ratio("full")
            ->messengerExtensions()
            ->fallbackUrl($menu["url"])
            ->toArray();
        }
        $response = ButtonTemplate::toArray($title,$buttons);
        return $response;
    }
}