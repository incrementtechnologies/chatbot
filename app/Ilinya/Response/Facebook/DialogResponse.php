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


class DialogResponse    {

  protected $messaging;
  protected $tracker;
  protected $bot; 
  private $curl;
  private $user;
  public function __construct(Messaging $messaging){
      $this->messaging = $messaging;
      $this->tracker   = new BotTracker($messaging);
      $this->bot       = new Bot($messaging);
      $this->curl = new Curl();
  }
  
  public function user(){
    $user = $this->curl->getUser($this->messaging->getSenderId());
    $this->user = new User($this->messaging->getSenderId(), $user['first_name'], $user['last_name']);
  }
    public function start()
    {
        $this->tracker->insert(1);
        $this->bot->reply(["text"=>"stage1"],false);
    }
  public function manage($stage)
  {
      switch ($stage) {
          case 1:
              # code...
              $this->stage2();
              break;
          
          case 2 :
                $this->stage3();
              # code...
              break;
          
          case 3:
              # code...
              break;
          
          default:
              # code...
              break;
      }
  }
    public function stage2(){
        $this->tracker->insert(2);
        $this->bot->reply(["text"=>"stage2"],false);
    }
    public function stage3(){
        $this->tracker->insert(2);
        $this->bot->reply(["text"=>"stage3"],false);
    }

//END

}

