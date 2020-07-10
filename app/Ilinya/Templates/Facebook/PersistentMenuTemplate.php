<?php

namespace App\Ilinya\Templates\Facebook;
use App\Ilinya\Templates\Facebook\ButtonElement;
class PersistentMenuTemplate{

  public static function toArray($buttons){
    $actions =[];
    foreach ($buttons as $button) {
        $actions[] = $button instanceof PersistentMenuElement? $button->toArray(): $button;
      }
    $response =  [
        "psid"=> "<PSID>",
        "persistent_menu"=> [
              [
                  "locale"=> "default",
                  "composer_input_disabled"=> true,
                  "call_to_actions"=> $actions
                  ]
              
          ]
    ];
    return $response;   
  }

}
