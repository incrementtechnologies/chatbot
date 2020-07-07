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


class PackageResponse{

  protected $messaging;
  protected $tracker;
  protected $bot; 
  private $curl;
  private $user;
  private $web_url = "https://mezzohotel.com/inquiry/event";
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
// Start Yol
  public function packageMenu()
  {
    $this->user();
    $title =  "Hi ".$this->user->getFirstName().",thank you for your interest in our Banquet Packages.Please choose the following options to get the information you need.";
    $menus= array( 
      array("payload"=> "@pPackageInquiry", "title"=>"BANQUET INQUIRY" ,"web"=>true) ,
      array("payload"=> "@pPackageSelected", "title"=>"BANQUET PACKAGES" ,"web"=>false)
    );
    $buttons =[];
    foreach ($menus as $menu) {
        // if ($menu["web"]) {
        //     $buttons[] = ButtonElement::title($menu["title"])
        //     ->type('web_url')
        //     ->url($this->web_url)
        //     ->ratio("full")
        //     ->messengerExtensions()
        //     ->fallbackUrl($this->web_url)
        //     ->toArray();
        // } else {
        //     $buttons[] = ButtonElement::title($menu["title"])
        //                 ->type('postback')
        //                 ->payload($menu["payload"])
        //                 ->toArray();
        // }
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
                ->type("web_url")
                ->url($this->web_url)
                ->ratio("full")
                ->messengerExtensions()
                ->fallbackUrl($this->web_url)
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

    public function packageInquiry(){
        $credentials = array("4","3");
        $packages = SheetController::getSheetContent($credentials);
        $quickReplies =[];
        for ($i=0; $i <sizeof($packages) ; $i++) {  
            $payload = preg_replace('/\s+/', '_', $packages[$i]['title']);
            $quickReplies[] = QuickReplyElement::title($packages[$i]['title'])->contentType('text')->payload($payload.'@qInquirePackage');
        }
        $title="Please choose any of the following option of the type of banquet setup?";
        $response= QuickReplyTemplate::toArray($title,$quickReplies);
        return  $response;
    }

    public function packageInquiryStages(){
        $this->user();
        $title =  "For us to follow up your inquiry please follow the questions in order for us understand your Inquiry through Facebook.";
         $buttons[] = ButtonElement::title("Click Here")
                    ->type('web_url')
                    ->url($this->web_url)
                    ->ratio("full")
                    ->messengerExtensions()
                    ->fallbackUrl($this->web_url)
                    ->toArray();
        $response = ButtonTemplate::toArray($title,$buttons);
        return $response;
    }

    public function packageInquireAgain(){
        $title =  "Thank you for your interest in Mezzo Hotel. Please wait for our personnel to respond to confirm your banquet inquiry. If you have anything to change with your inquiry please select the option to arrange banquet.";
         $buttons[] = ButtonElement::title("Arrange Banquet")
                    ->type('postback')
                    ->payload("@pPackageInquiry")
                    ->toArray();
        $response = ButtonTemplate::toArray($title,$buttons);
        return $response;
    }




//END

}

