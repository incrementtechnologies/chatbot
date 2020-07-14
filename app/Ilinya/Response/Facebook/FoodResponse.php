<?php
namespace App\Ilinya\Response\Facebook;

/*
    @Providers
*/
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\User;
use App\Ilinya\Bot;
use Illuminate\Http\Request;
use App\Ilinya\BotTracker;
use App\Ilinya\Http\Curl;
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
use App\Ilinya\API\SheetController;

/**
 * @STORAGE 
 */
use Storage;


class FoodResponse{

  protected $messaging;
  protected $tracker;
  protected $foods;
  protected $bot; 
  private $curl;
  private $user;
  private $credentials;

  public function __construct(Messaging $messaging){
      $this->messaging = $messaging;
      $this->tracker   = new BotTracker($messaging);
      $this->bot       = new Bot($messaging);
      $this->curl = new Curl();
      $this->credentials = array(env('FOOD_URL'),"4");
      $this->foods = SheetController::getSheetContent($this->credentials); 

  }
  
  public function user(){
    $user = $this->curl->getUser($this->messaging->getSenderId());
    $this->user = new User($this->messaging->getSenderId(), $user['first_name'], $user['last_name']);
  }
  public function foods(){
    $buttons = [];
    $elements = [];
    $max=10;
    if(sizeof($this->foods)>0){
        $prev = $this->foods[0]['caption'];
        $i = 0; 
        foreach ($this->foods as $food) {
             $data = explode(":",$food['caption']);
             $subtitle = $data[0];
             $imageUrl = "https://mezzohotel.com/img/".$food["image"];
             $btnText = str_replace("_" ," " , $food['type']);
             $buttons[] = ButtonElement::title(strtoupper($btnText))
                        ->type('web_url')
                        ->url($food["link"])
                        ->ratio("full")
                        ->messengerExtensions()
                        ->fallbackUrl($food["link"])
                        ->toArray();
            if($i < sizeof($this->foods) - 1){
                if($prev != $this->foods[$i + 1]['caption']){
                    $title = $data[1];
                    $elements[] = GenericElement::title($title)
                        ->imageUrl($imageUrl)
                        ->subtitle($subtitle)
                        ->buttons($buttons)
                        ->toArray();
                    $prev = $food['caption'];
                    $buttons = null;
                    echo $imageUrl.'<br />';
                }
            }
            else{
                $title = $data[1];
                $elements[] = GenericElement::title($title)
                    ->imageUrl($imageUrl)
                    ->subtitle($subtitle)
                    ->buttons($buttons)
                    ->toArray();
                    echo $imageUrl.'<br />';
            }
            $i++;
            if (sizeof($elements) == $max) {
                $response =  GenericTemplate::toArray($elements);
                $this->bot->reply(json_encode($response) , false);
                $elements = [];
            }
            
        }
        $response =  GenericTemplate::toArray($elements);
        $this->bot->reply(json_encode($response) , false);
        
    }else{
        $this->bot->reply(["text"=>"There are no foods available at the moment."],false);
        
      }
   
    // return json_encode($response);
}
  //END

}

