<?php

namespace App\Ilinya\Templates\Facebook;
use App\Ilinya\Templates\Facebook\ButtonElement;
class PersistentMenuTemplate{

  public static function toArray(){
    $menus=  array(
        array("title"=>"ROOM RATES" , "isWebview"=>false),
        array("title"=>"BANQUET PACKAGES","isWebview"=>false),
        array("title"=>"CONCERN/INQUIRY" ,"isWebview"=>true,"url" => "https://mezzohotel.com/inquiry/other")
    );
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
    $actions = [];
    foreach ($buttons as $button) {
      $actions[] = $button instanceof ButtonElement? $button->toArray(): $button;
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
    // \Storage::put("menu.json", json_encode($response));
// 
    return json_encode($response);
  }

}
