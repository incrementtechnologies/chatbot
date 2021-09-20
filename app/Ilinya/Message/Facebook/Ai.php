<?php

namespace App\Ilinya\Message\Facebook;

use App\Ilinya\Bot;
use App\Ilinya\BotTracker;
use App\Ilinya\Helper\Validation;
use App\Ilinya\Http\Curl;
use App\Ilinya\Message\Facebook\Codes;
use App\Ilinya\Message\Facebook\Form;
use App\Ilinya\Response\Facebook\AiResponse;
use App\Ilinya\Response\Facebook\CategoryResponse;
use App\Ilinya\Response\Facebook\DetailsResponse;
use App\Ilinya\Response\Facebook\DialogResponse;
use App\Ilinya\Response\Facebook\EditDetailsResponse;
use App\Ilinya\Response\Facebook\EditResponse;
use App\Ilinya\Response\Facebook\FoodResponse;
use App\Ilinya\Response\Facebook\PackageResponse;
use App\Ilinya\Response\Facebook\PostbackResponse;
use App\Ilinya\Response\Facebook\RoomResponse;
use App\Ilinya\Response\Facebook\SearchResponse;
use App\Ilinya\Webhook\Facebook\Messaging;


use App\Ilinya\Templates\Facebook\ButtonTemplate;
use App\Ilinya\Templates\Facebook\ButtonElement;

class Ai
{
    protected $form;
    protected $post;
    protected $search;
    protected $code;
    protected $tracker;
    protected $edit;
    protected $editDetails;
    protected $validation;
    protected $details;
    protected $curl;
    protected $aiResponse;
    protected $food;
    protected $package;
    protected $room;
    protected $dialog;
    public function __construct(Messaging $messaging)
    {
        $this->bot = new Bot($messaging);
        $this->post = new PostbackResponse($messaging);
        $this->category = new CategoryResponse($messaging);
        $this->form = new Form($messaging);
        $this->tracker = new BotTracker($messaging);
        $this->code = new Codes();
        $this->edit = new EditResponse($messaging);
        $this->search = new SearchResponse($messaging);
        $this->editDetails = new EditDetailsResponse($messaging);
        $this->validation = new Validation($messaging);
        $this->details = new DetailsResponse($messaging);
        $this->aiResponse = new AiResponse($messaging);
        $this->dialog = new DialogResponse($messaging);
        $this->curl = new Curl();
        $this->room = new RoomResponse($messaging);
        $this->package = new PackageResponse($messaging);
        $this->food = new FoodResponse($messaging);

    }

    public function manage($reply)
    {
        $reply = strtolower($reply);
        $this->curl->whitelistWebView();
        $track_flag = $this->tracker->getStage();
        
        $title = "Oops! Please be reminded that I’m just a chatbot. Please follow the steps and answer my questions precisely so I can assist you better. \n\nIf you’d rather speak to a hotel representative, please contact us at (032) 231-0777 Alternatively, you can fill-in the form below and a hotel representative will contact you within 24 hours.";
        
        $imageUrl = "http://ilinya.com/wp-content/uploads/2017/08/cropped-logo-copy-copy.png";

        $menus = array(
            array(
                "title"=>"Take me to the form." ,
                "isWebview"=>true,
                "url" => "https://mezzohotel.com/inquiry/other"
            ),
        );

        $buttons =[];
        foreach ($menus as $menu) {
            $payload = preg_replace('/\s+/', '_', $menu["title"]);
            $buttons[] = ButtonElement::title(ucwords(strtolower( $menu['title'])))
                ->type('web_url')
                ->url($menu["url"])
                ->ratio("full")
                ->messengerExtensions()
                ->fallbackUrl($menu["url"])
                ->toArray();
        }
        $response = ButtonTemplate::toArray($title,$buttons);
        $this->bot->reply($response, false);
    }

    public function manageOld($reply)
    {
        $reply = strtolower($reply);
        $this->curl->whitelistWebView();
        $track_flag = $this->tracker->getStage();
        if ($track_flag != null) {
            $this->bot->reply($this->dialog->manage($reply), false);
        } else {
            $this->tracker->delete();
            if (strpos($reply, 'hi') !== false || strpos($reply, 'hello') !== false || strpos($reply, 'help') !== false || strpos($reply, 'hola') !== false || $reply =="?") {
                $this->bot->reply($this->post->start(), false);
            } else if (strpos($reply, 'thank you') !== false) {
                $this->bot->reply($this->aiResponse->thankYou(), false);
            } else if ($reply == "food" || $reply == "foods") {
                $this->bot->reply($this->food->foods(), false);
            } else if ($reply == "rooms") {
                $this->bot->reply($this->room->roomMenuStart(), false);
                $this->bot->reply($this->room->roomMenu(), false);
            } else if ($reply == "packages") {
                $this->bot->reply($this->package->packageMenu(), false);
            } else {
                // $this->bot->reply($this->post->banner(), false);
                // $this->bot->reply($this->post->start(), false);
                // $this->bot->reply($this->post->inquiry(), false);
                // $this->tracker->delete();
                if (trim($reply[-1]) == "?") {
                    $reply = substr_replace(trim($reply), "", -1);
                }
                $this->tracker->insert(1, $reply);
                $this->dialog->paginateQuestion(1, $reply);
                $this->tracker->delete();
                // $this->bot->reply($this->dialog->manage($reply) , false);

            }
            return response('', 200);
        }
    }

}
