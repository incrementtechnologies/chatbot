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


class CategoryResponse{

  protected $messaging;
  protected $tracker;
  protected $bot; 
  private $curl;
  private $user;
  public function __construct(Messaging $messaging){
      $this->messaging = $messaging;
      $this->tracker   = new Tracker($messaging);
      $this->bot       = new Bot($messaging);
      $this->curl = new Curl();
  }
  
  public function user(){
    $user = $this->curl->getUser($this->messaging->getSenderId());
    $this->user = new User($this->messaging->getSenderId(), $user['first_name'], $user['last_name']);
  }

  public function roomMenuStart()
  {
    $this->user();;
    $title =  "Greetings from Mezzo!\n\nYou can also call our Reservations at 0906 423 1579 for booking inquiries and room availability.\n\n#anewdimensionofluxury";    
    return ["text" => $title];
  }
  public function roomMenu(){
    $title ="How can we help you with your room inquiry?";
    $menus= array( 
      array("payload"=> "@pRoomMenuSelected", "title"=>"ROOM RATES" ,"web"=>false),
      array("url"=> "https://mezzohotel.com", "title"=>"HOTEL FACILITIES" ,"web"=>true),
      array("payload"=> "@pRoomMenuSelected", "title"=>"ROOM RESERVATIONS","web"=>false)
    );
    // 
    foreach ($menus as $menu) {
      if (!$menu['web']) {
        $buttons[] = ButtonElement::title($menu["title"])
                  ->type('postback')
                  ->payload($menu["payload"])
                  ->toArray();
      } else {
        $buttons[] = ButtonElement::title($menu["title"])
                  ->type('web_url')
                  ->url($menu["url"])
                  ->toArray();
      }
    }
    $response = ButtonTemplate::toArray($title,$buttons);
    return $response;
  }
  public function rooms($isRreserve){
    $credentials = array("5","6");
    $categories = SheetController::getSheetContent($credentials); 
    $buttons = [];
    $elements = [];
    if(sizeof($categories)>0){
        $prev = $categories[0]['title'];
        $i = 0; 
        foreach ($categories as $category) {
             $subtitle = $category['price'];
             $payload= preg_replace('/\s+/', '_', strtolower($category['title']));
             $imgArray= explode(',' , $category['images = array']);
             $imageUrl = "https://mezzohotel.com/img/".$imgArray[0];
             if ($isRreserve!=true) {
              $buttons[] = ButtonElement::title('BOOK NOW')
              ->type('web_url')
              ->url("https://mezzohotel.com/inquiry/room")
              ->toArray();
             } else {
              $buttons[] = ButtonElement::title('RESERVE')
              ->type('postback')
              ->payload($payload."@pRoomInquiry")
              ->toArray();
             } 
            if($i < sizeof($categories) - 1){
                if($prev != $categories[$i + 1]['title']){
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
        }
    }
    $response =  GenericTemplate::toArray($elements);
    return $response;
}

  public function packageMenu()
  {
    $this->user();
    $title =  "Hi ".$this->user->getFirstName().", thank you for your interest in our Banquet Packages.Please choose the following options to get the information you need.";
    $menus= array( 
      array("payload"=> "@pPackageInquiry", "title"=>"BANQUET INQUIRY") ,
      array("payload"=> "@pPackageSelected", "title"=>"BANQUET")
    );
    $buttons =[];
    foreach ($menus as $menu) {
        $buttons[] = ButtonElement::title($menu["title"])
                    ->type('postback')
                    ->payload($menu["payload"])
                    ->toArray();
    }
    $response = ButtonTemplate::toArray($title,$buttons);
    return $response;
  }
  public function packages(){
    $credentials = array("4","3");
    $packages = SheetController::getSheetContent($credentials); 
    $buttons = [];
    $elements = [];
    if(sizeof($packages)>0){
        $prev = $packages[0]['title'];
        $i = 0; 
        foreach ($packages as $package) {
             $imageUrl = $package['image'];
             $payload= preg_replace('/\s+/', '_', strtolower($package['title']));
             $buttons[] = ButtonElement::title(strtoupper('Inquire now'))
                ->type('web_url')
                ->url('https://mezzohotel.com/inquiry/room')
                ->toArray();
            if($i < sizeof($packages) - 1){
                if($prev != $packages[$i + 1]['title']){
                    $title = $package['title'];
                    $elements[] = GenericElement::title($title)
                        ->imageUrl($imageUrl)
                        ->subtitle(null)
                        ->buttons($buttons)
                        ->toArray();
                    $prev = $package['title'];
                    $buttons = null;
                    echo $imageUrl.'<br />';
                }
            }
            else{
                $title = $package['title'];
                $elements[] = GenericElement::title($title)
                    ->imageUrl($imageUrl)
                    ->subtitle(null)
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

  public function concerns($parameter){
    $title = $parameter;
    $elements[] = GenericElement::title($title)
                        ->imageUrl(null)
                        ->subtitle("test")
                        ->buttons(null)
                        ->toArray();
    $response =  GenericTemplate::toArray($elements);
    return $response;
  }
  public function companies($businessTypeId){
     $request = new Request();
     $condition[] = [
      "column"  => "business_type_id",
      "clause"  => "=",
      "value"   => $businessTypeId
    ];
     $request['condition'] = $condition;
     $request['sort'] = ["name" => "asc"];
     return $this->retrieve($request);
  }

  public function search($value, $category = null){
    $request = new Request();
    $condition = [];

    switch ($this->tracker->getSearchOption()) {
      case 2:
        $condition[] = [
          "column"  => "name",
          "clause"  => "like",
          "value"   => "%".$value.'%'
        ];
        break;
      case 3:
        $condition[] = [
          "column"  => "address",
          "clause"  => "like",
          "value"   => "%".$value.'%'
        ];
        break;
      default:
        break;
    }

    if($category){
        $condition[] = [
          "column"  => "business_type_id",
          "clause"  => "=",
          "value"   => $category
        ];
    }
   
    
    $request['condition'] = $condition;
    $request['limit']     = 10;
    return $this->retrieve($request);
  }

  public function retrieve(Request $request){
    $data = Controller::retrieve($request, "App\Http\Controllers\CompanyController");
    if(sizeof($data) > 0){
      $trackerData = [
        "search_option" => null,
        "reply" => null
      ];
      $this->tracker->update($trackerData);
    }else{}
    return $this->manageResult($data);
  }

  public function manageResult($datas){
    $size = sizeof($datas);
    $imgUrl = "http://www.gocentralph.com/gcssc/wp-content/uploads/2017/04/Services.png";
    if($size < 9 && $datas){
      $elements = [];
      $this->bot->reply($this->informAboutQCard(), false);
      foreach ($datas as $data) {
        $buttons = [];
        if($data['id'] != '6' || intval($data['id']) != 6){
          $availability = $this->availability($data['id']);
          if(intval($availability['result']) == 1){
            $buttons[] = ($availability['response'] == true)?ButtonElement::title("Get QCard")
                      ->type('postback')
                      ->payload($data['id'].'@pGetQueueCard')
                      ->toArray():ButtonElement::title("View Location")
                      ->type('web_url')
                      ->url('https://www.instantstreetview.com/@'.$data['lat'].','.$data['lng'].',11z,1t')
                      ->ratio('full')
                      ->toArray();  
          }
          else if(intval($availability['result']) > 1){
            $buttons[] = ($availability['response'] == true)?ButtonElement::title("View Forms")
                      ->type('postback')
                      ->payload($data['id'].'@pGetQueueCard')
                      ->toArray():ButtonElement::title("View Location")
                      ->type('web_url')
                      ->url('https://www.instantstreetview.com/@'.$data['lat'].','.$data['lng'].',11z,1t')
                      ->ratio('full')
                      ->toArray();  
          }else{}
          
          $buttons[] = ($availability['response'] == true)?ButtonElement::title("View Location")
                      ->type('web_url')
                      ->url('https://www.instantstreetview.com/@'.$data['lat'].','.$data['lng'].',11z,1t')
                      ->ratio('full')
                      ->toArray():ButtonElement::title("Back to Categories")
                      ->type('postback')
                      ->payload('@pCategories')
                      ->toArray();
         
          $availabilityText = ($availability['response'] == true)? ' is Available':' is not Available';
          $availabilityText .= ' for Transaction!';
          $elements[] = GenericElement::title($data['name'].$availabilityText)
                              ->imageUrl($imgUrl)
                              ->subtitle('Address: '.$data['address'])
                              ->buttons($buttons)
                              ->toArray();
        }
      }
      $response =  GenericTemplate::toArray($elements);
      return $response;
    }
    else if($size > 10 && $datas){
       $this->bot->reply($this->informAboutQCard(), false);
        $buttons = [];
        $buttons[] = ButtonElement::title("Next")
            ->type('postback')
            ->payload('@pNext')
            ->toArray();
        $buttons[] = ButtonElement::title("Search")
            ->type('postback')
            ->payload('@pSearch')
            ->toArray();
        $buttons[] = ButtonElement::title("Back to Categories")
            ->type('postback')
            ->payload('@pCategories')
            ->toArray();
        $elements[] = GenericElement::title("There's more on this Category!")
                            ->imageUrl($imgUrl)
                            ->subtitle("Click Next or take a search:")
                            ->buttons($buttons)
                            ->toArray();
      $response =  GenericTemplate::toArray($elements);
      return $response;
    }
    else{
      return ["text" => "Search not found :'( Enter again: "];
    }
  }
  
  public function availability($companyId){
    $response     = true;
    $controller   = "App\Http\Controllers\QueueFormController";
    $request      = new Request();

    $condition[] = [
          "column"  => "company_id",
          "clause"  => "=",
          "value"   => $companyId
       ];

    $request['condition'] = $condition;

    $result = Controller::retrieve($request, $controller);
    if($result != null){
      $notAvailable = 0;
      foreach ($result as $row) {
        if(intval($row['availability']) >= 2){
          $notAvailable++;
        }
      }
      if(sizeof($result) == $notAvailable){
        $response = false;
      }else if(sizeof($result) > $notAvailable){
        $response = true;
      }
    }
    else{
       $response = false;
    }
    
    $data = [
      "response" => $response,
      "result"   => sizeof($result)
    ];

    return $data;
  }

   public function informAboutQCard(){
        $this->user();
        return ['text' => "Hi ".$this->user->getFirstName()." :) To get Reservation, Ticket or Priority Number kindly click the Get QCard Button. Thank You :)"];
    }

}

