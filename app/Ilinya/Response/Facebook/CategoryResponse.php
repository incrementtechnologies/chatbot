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


class CategoryResponse{

  protected $messaging;
  protected $tracker;
  protected $bot; 
  private $curl;

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

