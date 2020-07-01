<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use Mpociot\BotMan\BotMan;
use Mpociot\BotMan\Messages\Message;
use Mpociot\BotMan\Facebook\ElementButton;
use Mpociot\BotMan\Facebook\ButtonTemplate;

use Mpociot\BotMan\Conversations\Ilinya;
/*
  @Model
*/
  use App\BusinessType;

class BotManController extends APIController
{
  public function handler(){
    $botman = app('botman');
    $botman->verifyServices('GoCentral123456789ekennCKdashIlinya2017143143leadUsLord');

    /*

                Actions Here
    */

    // Persistent Menus
    $botman->hears('@start', function (BotMan $ilinya) {
      $this->start($ilinya);
    });
    $botman->hears('@categories', function (BotMan $ilinya) {
      $this->categories($ilinya);
    });
    $botman->hears('@my_queue_cards', function (BotMan $ilinya) {
      $this->queueCards($ilinya);
    });
    $botman->hears('@user_guide', function (BotMan $ilinya) {
      $this->guide($ilinya);
    });

    //Categories
    $botman->hears('@ktv', function (BotMan $ilinya) {
      $ilinya->startConversation(new Ilinya);
    });

    // start listening
    $botman->listen();
  }

  public function start(BotMan $ilinya){
     $ilinya->typesAndWaits(2);
     $ilinya->reply('Hi '.$ilinya->getUser()->getFirstName().'! My Name is Ilinya, I can help to get your reservations or tickets easily. Just follow my instructions and you will be good to go!');
     $this->categories($ilinya);
  }

  public function categories(BotMan $ilinya){
    $buttons = [];
    $categories = BusinessType::get();
    if($categories){
      $i = 0;
      foreach ($categories as $category) {
        $buttons[] = ElementButton::create($category['title'])->type('postback')->payload('@'.strtolower($category['title']));
      }
    }
    $ilinya->reply(ButtonTemplate::create('Select Categories:')->addButtons($buttons));
  }

  public function queueCards(BotMan $ilinya){
    $id = $ilinya->getUser()->getId();
  }

  public function guide(BotMan $ilinya){

  }

}
