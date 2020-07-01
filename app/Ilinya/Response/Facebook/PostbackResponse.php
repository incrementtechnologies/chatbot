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

/*
    @Elements
*/

use App\Ilinya\Templates\Facebook\ButtonElement;
use App\Ilinya\Templates\Facebook\GenericElement;
use App\Ilinya\Templates\Facebook\QuickReplyElement;

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
        $message = "Hi ".$this->user->getFirstName()." :) I'm ILinya, I can help you to get ticket or make reservation to any establishment(s) or event(s) you want. I'm currently on a TEST MODE right now, so all of the data are just sample and not really connected to the establishment that will be mentioned in our conversation later. ";
        return ["text" => $message];
    }
    public function start(){
        $this->user();

        $title =  "Hi ".$this->user->getFirstName().". I'm Ilinya your personal assistant.";
        $subtitle = "Kindly click the buttons to navigate.";
        $imageUrl = "http://ilinya.com/wp-content/uploads/2017/08/cropped-logo-copy-copy.png";
        $elements[] = GenericElement::title($title)
                            ->imageUrl($imageUrl)
                            ->subtitle($subtitle)
                            ->buttons(null)
                            ->toArray();
        $response =  GenericTemplate::toArray($elements);
        return $response;
    }
    
    public function categories(){
        $request = new Request();
        $request['sort'] = ["category" => "asc"];
        $categories = Controller::retrieve($request, "App\Http\Controllers\BusinessTypeController");
        $subtitle = "Get tickets or make reservations on category below:";
        $buttons = [];
        $elements = [];
       
        if($categories){
            $prev = $categories[0]['category'];
            $i = 0;
            foreach ($categories as $category) {
                 $imageUrl = "http://ilinya.com/wp-content/uploads/2017/09/category_".strtolower($category['category']).'.png';
                $buttons[] = ButtonElement::title($category['sub_category'])
                    ->type('postback')
                    ->payload(strtolower($category['id']).'@pCategorySelected')
                    ->toArray();
                if($i < sizeof($categories) - 1){
                    if($prev != $categories[$i + 1]['category']){
                        $title = $category['category'];
                        $elements[] = GenericElement::title($title)
                            ->imageUrl($imageUrl)
                            ->subtitle($subtitle)
                            ->buttons($buttons)
                            ->toArray();
                        $prev = $category['category'];
                        $buttons = null;
                        echo $imageUrl.'<br />';
                    }
                }
                else{
                    $title = $category['category'];
                    $elements[] = GenericElement::title($title)
                        ->imageUrl($imageUrl)
                        ->subtitle($subtitle)
                        ->buttons($buttons)
                        ->toArray();
                        echo $imageUrl.'<br />';
                }
                
                $i++;
            }
        }


        $response =  GenericTemplate::toArray($elements);
        return $response;
    }

    public function priorityError(){
        $quickReplies[] = QuickReplyElement::title('Yes')->contentType('text')->payload('priority@yes');
        $quickReplies[] = QuickReplyElement::title('No')->contentType('text')->payload('priority@no');
        return QuickReplyTemplate::toArray('Are you sure you want cancel your current conversation?', $quickReplies);
    }

   
   
}