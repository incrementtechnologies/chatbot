<?php
namespace App\Ilinya\Response\Facebook;

/*
    @Providers
*/
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\User;
use App\Ilinya\Bot;
use Illuminate\Http\Request;
use App\Ilinya\Tracker;
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


class RoomResponse{

  protected $messaging;
  protected $tracker;
  protected $bot; 
  private $curl;
  private $user;
  private $categories ; 
  private $credentials;
  public function __construct(Messaging $messaging){
      $this->messaging = $messaging;
      $this->tracker   = new Tracker($messaging);
      $this->bot       = new Bot($messaging);
      $this->curl = new Curl();
      $this->credentials = array(env('ROOMS_URL'),"9");
      $this->categories = SheetController::getSheetContent($this->credentials); 
  }
  
  public function user(){
    $user = $this->curl->getUser($this->messaging->getSenderId());
    $this->user = new User($this->messaging->getSenderId(), $user['first_name'], $user['last_name']);
  }
// Start Yol
  public function roomMenuStart()
  {
    $this->user();;
    $title =  "Greetings from Mezzo Hotel! \n\nFor urgent inquiries, you may call us at 032 231 0777 or 0906 423 1579.\n\n#anewdimensionofluxury";    
    return ["text" => $title];
  }


#anewdimensionofluxury
  public function roomMenu(){
    $this->user();
    $title ="Hi ".$this->user->getFirstName().", thank you for interest in our rooms. Please choose the following options to get the information you need.";
    $menus= array( 
      array("payload"=> "@pRoomMenuSelected", "title"=>"ROOMS" ,"web"=>false),
      array("url"=> "https://mezzohotel.com/#gallery", "title"=>"HOTEL FACILITIES" ,"web"=>true),
      array("url"=> "https://mezzohotel.com/inquiry/room", "title"=>"GROUP RESERVATIONS","web"=>true)
    );
    // 
    foreach ($menus as $menu) {
      if (!$menu['web']) {
        $buttons[] = ButtonElement::title(ucwords(strtolower($menu["title"])))
                  ->type('postback')
                  ->payload($menu["payload"])
                  ->toArray();
      } else {
        $buttons[] = ButtonElement::title(ucwords(strtolower($menu["title"])))
                  ->type('web_url')
                  ->url($menu["url"])
                  ->ratio("full")
                  ->messengerExtensions()
                  ->fallbackUrl($menu["url"])
                  ->toArray();
      }
    }
    $response = ButtonTemplate::toArray($title,$buttons);
    return $response;
  }

  public function roomReservation(){
    $url = "https://mezzohotel.com/inquiry/room";
    $title =  "For us to arrange a reservation please follow the questions in order for us understand your reservation through Facebook.";
     $buttons[] = ButtonElement::title("Click Here")
                ->type('web_url')
                ->url($url)
                ->ratio("full")
                ->messengerExtensions()
                ->fallbackUrl($url)
                ->toArray();
    $response = ButtonTemplate::toArray($title,$buttons);
    return $response;
  }
  public function rooms($isRreserve){
   
    $length = sizeof($this->categories);
    $partitions = $this->bot->partition($this->categories);
    $max = 10;
    if($length>0){
        foreach ($partitions as $chunck) {
          $buttons = [];
          $elements = [];
          $prev = $chunck[0]['title'];
          $i = 0; 
          foreach ($chunck as $category) {
               $subtitle = $isRreserve ? null: $category['price'];
               $payload= preg_replace('/\s+/', '_', strtolower($category['title']));
               $imgArray= explode(',' , $category['images = array']);
               $imageUrl = "https://mezzohotel.com/img/".$imgArray[0];
               if ($isRreserve!=true) {
                $buttons[] = ButtonElement::title(ucwords(strtolower("BOOK NOW")))
                  ->type('web_url')
                  ->url("https://mezzohotel.com/inquiry/room")
                  ->ratio("full")
                  ->messengerExtensions()
                  ->fallbackUrl("https://mezzohotel.com/inquiry/room")
                  ->toArray();
               } else {
                $buttons[] = ButtonElement::title(ucwords(strtolower('RESERVE')))
                ->type('postback')
                ->payload($payload."@pRoomInquiry")
                ->toArray();
               } 
              if($i < sizeof($chunck) - 1){
                  if($prev != $chunck[$i + 1]['title']){
                      $title = $category['title'];
                      $elements[] = GenericElement::title($title)
                          ->imageUrl($imageUrl)
                          ->subtitle($subtitle)
                          ->buttons($buttons)
                          ->toArray();
                      $prev = $category['title'];
                      $buttons = null;
                      echo $imageUrl.'<br />';
                  }
              }
              else{
                  $title = $category['title'];
                  $elements[] = GenericElement::title($title)
                      ->imageUrl($imageUrl)
                      ->subtitle($subtitle)
                      ->buttons($buttons)
                      ->toArray();
                      echo $imageUrl.'<br />';
              }
              $i++;
            //   if (sizeof($elements) == $max) {
            //     $response =  GenericTemplate::toArray($elements);
            //     $this->bot->reply($response , false);
            //     $elements = [];
            // }
          }
          $response =  GenericTemplate::toArray($elements);
          $this->bot->reply($response , false);
        }
       
    }else{
      $this->bot->reply(["text"=>"There are no rooms available at the moment."],false);
    }
    
    // $response =  GenericTemplate::toArray($elements);
    // return json_encode($response);
}
//   public function rooms($isRreserve){
//     $credentials = array(env('ROOMS_URL'),"9");
//     $categories = SheetController::getSheetContent($credentials); 
//     \Storage::put("request.json", json_encode($categories));

//     $buttons = [];
//     $elements = [];
//     $length = sizeof($this->categories);
//     $max = 10;
//     if($length>0){
//         $prev = $this->categories[0]['title'];
//         $i = 0; 
//         foreach ($this->categories as $category) {
//              $subtitle = $isRreserve ? null: $category['price'];
//              $payload= preg_replace('/\s+/', '_', strtolower($category['title']));
//              $imgArray= explode(',' , $category['images = array']);
//              $imageUrl = "https://mezzohotel.com/img/".$imgArray[0];
//              if ($isRreserve!=true) {
//               $buttons[] = ButtonElement::title('BOOK NOW')
//                 ->type('web_url')
//                 ->url("https://mezzohotel.com/managebooking.php")
//                 ->ratio("full")
//                 ->messengerExtensions()
//                 ->fallbackUrl("https://mezzohotel.com/managebooking.php")
//                 ->toArray();
//              } else {
//               $buttons[] = ButtonElement::title('RESERVE')
//               ->type('postback')
//               ->payload($payload."@pRoomInquiry")
//               ->toArray();
//              } 
//             if($i < sizeof($this->categories) - 1){
//                 if($prev != $this->categories[$i + 1]['title']){
//                     $title = $category['title'];
//                     $elements[] = GenericElement::title($title)
//                         ->imageUrl($imageUrl)
//                         ->subtitle(null)
//                         ->buttons($buttons)
//                         ->toArray();
//                     $prev = $category['title'];
//                     $buttons = null;
//                     echo $imageUrl.'<br />';
//                 }
//             }
//             else{
//                 $title = $category['title'];
//                 $elements[] = GenericElement::title($title)
//                     ->imageUrl($imageUrl)
//                     ->subtitle($subtitle)
//                     ->buttons($buttons)
//                     ->toArray();
//                     echo $imageUrl.'<br />';
//             }
//             $i++;
//             if (sizeof($elements) == $max) {
//               $response =  GenericTemplate::toArray($elements);
//               $this->bot->reply($response , false);
//               $elements = [];
//           }
//         }
//         $response =  GenericTemplate::toArray($elements);
//         $this->bot->reply($response , false);
//     }else{
//       $this->bot->reply(["text"=>"There are no rooms available at the moment."],false);
//     }
    
//     // $response =  GenericTemplate::toArray($elements);
//     // return json_encode($response);
// }
public function roomReserveAgain(){
  $title =  "Thank you for your interest in Mezzo Hotel. Please wait for our personnel to respond to confirm your banquet inquiry. If you have anything to change with your inquiry please select the option to arrange banquet.";
   $buttons[] = ButtonElement::title("Reserve Room Again")
              ->type('postback')
              ->payload("@pRoomInquiry")
              ->toArray();
  $response = ButtonTemplate::toArray($title,$buttons);
  return $response;
}
}