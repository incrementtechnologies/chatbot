<?php
namespace App\Ilinya\Response\Facebook;

/*
@Providers
 */
use App\Ilinya\API\SheetController;
use App\Ilinya\Bot;
use App\Ilinya\BotTracker;
use App\Ilinya\Http\Curl;
use App\Ilinya\Response\Facebook\AiResponse;
/*
@Template
 */
use App\Ilinya\Templates\Facebook\ButtonElement;
use App\Ilinya\Templates\Facebook\ButtonTemplate;
use App\Ilinya\Templates\Facebook\ListTemplate;

/*
@Elements
 */

use App\Ilinya\Templates\Facebook\QuickReplyElement;
use App\Ilinya\Templates\Facebook\QuickReplyTemplate;

/*
@API
 */
use App\Ilinya\User;
use App\Ilinya\Webhook\Facebook\Messaging;
use Illuminate\Http\Request;

/**
 * @STORAGE
 */
use Storage;

class DialogResponse
{

    protected $messaging;
    protected $tracker;
    protected $bot;
    private $curl;
    private $aiResponse;
    private $user;
    private $questions;
    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
        $this->tracker = new BotTracker($messaging);
        $this->bot = new Bot($messaging);
        $this->aiResponse = new AiResponse($messaging);
        $this->curl = new Curl();
        $this->questions = SheetController::getSheetContent(array(env("FAQ_URL"), "3"));
    }

    public function user()
    {
        $user = $this->curl->getUser($this->messaging->getSenderId());
        $this->user = new User($this->messaging->getSenderId(), $user['first_name'], $user['last_name']);
    }
    public function startFaq($msg = null)
    {
        $this->tracker->insert(1, $msg);
        $this->user();
        $message = "Hi " . $this->user->getFirstName() . ", please send us your question.";
        return ["text" => $message];
    }
    public function manage($msg)
    {
        $page = $this->tracker->getStage();
        $data = [];
        if (strtolower($msg) == "more") {
            $data["stage"] = ++$page;
        } else {
            $data["input"] = $msg;
            $data["stage"] = 1;
        }
        $this->tracker->update($data);
        $this->tracker->retrieve();
        $reply = $this->tracker->getInput();
        $this->paginateQuestion($page, $reply);
        $this->tracker->delete();
    }

    public function paginateQuestion($page, $reply)
    {
        $result = [];
        $msg_array = explode(" ", $reply);
        foreach ($this->questions as $question) {
            $tags = explode(",", $question['tags']);
            if (strpos(strtolower($question['question']), $reply) !== false) {
                $result[] = $question;
            } else {
                foreach ($msg_array as $word) {
                    if (in_array($word, $tags)) {
                        $result[] = $question;
                        break;
                    }
                }
            }
        }
        if (sizeof($result) == 0) {
            $this->bot->reply($this->aiResponse->error(), false);
            $this->bot->reply($this->startFaq('faq'), false);
            $this->tracker->delete();

        } else {
            $offset = sizeof($result) >= $page * 3 ? $page * 3 : sizeof($result);
            $index = $offset - 3 < 0 ? 0 : $offset - 3;
            if ($page == 1) {
                $message = "Hi  these are the FAQ's related to your question.\n\n";
                $this->bot->reply(["text" => $message], false);
            }
            for ($i = $index; $i < $offset; $i++) {
                $message = ($i + 1) . ". " . $result[$i]['question'] . "\n\n" . $result[$i]['answer'];
                $this->bot->reply(["text" => $message], false);
            }
            if ($offset < sizeof($result)) {
                $quickReplies = [];
                $options = [
                    ["title" => "more", "payload" => "@qLoadMoreResult"],
                    ["title" => "ask again", "payload" => "@qFaq"],
                    ["title" => "Go back to menu", "payload" => "@qMainMenu"],
                ];
                foreach ($options as $option) {
                    $quickReplies[] = QuickReplyElement::title(strtoupper($option['title']))->contentType('text')->payload($option['payload']);
                }
                $title = "Reply 'more' to view more results.";
                $response = QuickReplyTemplate::toArray($title, $quickReplies);
                $this->bot->reply($response, false);
                // $this->FaqList();
            } else {
                \Log::debug("end of result should display faqs");
                // $title = "You have reached the bottom of the results. What do you want to do next?";
                // $menus = array(
                //     array("title" => "Ask another question"),
                //     array("title" => "Go back to menu"),
                // );
                // $buttons = [];
                // foreach ($menus as $menu) {
                //     # code...
                //     $buttons[] = ButtonElement::title(ucwords(strtolower($menu["title"])))
                //         ->type('postback')
                //         ->payload("@pCategorySelected")
                //         ->toArray();
                // }
                // $response = ButtonTemplate::toArray($title, $buttons);
                // $this->bot->reply($response, false);
                $this->FaqList();
            }
        }
    }

    public function FaqList()
    {
        \Log::debug('sending faq list');
        $buttons = [];
        $buttons[] = ButtonElement::title("FAQ List")
            ->type('web_url')
            ->url("https://mezzohotel.com/#faq")
            ->ratio("full")
            ->messengerExtensions()
            ->fallbackUrl("https://mezzohotel.com/#faq")
            ->toArray();
        $response = ButtonTemplate::toArray("To see the complete FAQ list , click here", $buttons);
        $this->bot->reply($response, false);
    }

//END

}
