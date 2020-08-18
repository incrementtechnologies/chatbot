<?php

namespace App\Ilinya\Message\Facebook;

use App\Ilinya\Bot;
use App\Ilinya\BotTracker;
use App\Ilinya\Message\Facebook\Codes;
use App\Ilinya\Message\Facebook\Form;
use App\Ilinya\Response\Facebook\CategoryResponse;
use App\Ilinya\Response\Facebook\DetailsResponse;
use App\Ilinya\Response\Facebook\DialogResponse;
use App\Ilinya\Response\Facebook\DisregardResponse;
use App\Ilinya\Response\Facebook\EditDetailsResponse;
use App\Ilinya\Response\Facebook\EditResponse;
use App\Ilinya\Response\Facebook\FoodResponse;
use App\Ilinya\Response\Facebook\PackageResponse;
use App\Ilinya\Response\Facebook\PostbackResponse;
use App\Ilinya\Response\Facebook\QueueCardsResponse;
use App\Ilinya\Response\Facebook\RoomResponse;
use App\Ilinya\Response\Facebook\SearchResponse;
use App\Ilinya\Response\Facebook\SendResponse;
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\Http\Curl;

class Postback
{
    protected $forms;
    protected $post;
    protected $search;
    protected $code;
    protected $tracker;
    protected $send;
    protected $edit;
    protected $qc;
    protected $disregard;
    protected $details;
    protected $editDetails;
    protected $package;
    protected $room;
    protected $food;
    protected $dialog;

    public function __construct(Messaging $messaging)
    {
        $this->bot = new Bot($messaging);
        $this->post = new PostbackResponse($messaging);
        $this->category = new CategoryResponse($messaging);
        $this->forms = new Form($messaging);
        $this->tracker = new BotTracker($messaging);
        $this->code = new Codes();
        $this->send = new SendResponse($messaging);
        $this->edit = new EditResponse($messaging);
        $this->qc = new QueueCardsResponse($messaging);
        $this->disregard = new DisregardResponse($messaging);
        $this->search = new SearchResponse($messaging);
        $this->details = new DetailsResponse($messaging);
        $this->editDetails = new EditDetailsResponse($messaging);
        $this->room = new RoomResponse($messaging);
        $this->package = new PackageResponse($messaging);
        $this->food = new FoodResponse($messaging);
        $this->dialog = new DialogResponse($messaging);
    }

    public function manage($custom)
    {
        $action = $this->code->getCode($custom);
        switch ($action) {
            case $this->code->pStart:
                $this->tracker->delete();
                // $this->bot->setup();
                // $this->bot->reply($this->post->banner(), false);
                $this->bot->reply($this->post->start(), false);
                // $this->bot->reply($this->post->inquiry(), false);
                break;
            case $this->code->pUserGuide:
                $this->bot->reply($this->post->userGuide(), true);
                break;
            case $this->code->pMyQueueCards:
                $this->bot->reply($this->qc->display(), false);
                break;
            case $this->code->pCategories:
                $this->bot->reply($this->post->inquiry(), false);
                break;
            case $this->code->pCategorySelected:
                $this->tracker->delete();
                $payload = trim(strtolower($custom['parameter']));
                switch ($payload) {
                    case 'rooms':
                        $this->bot->reply($this->room->roomMenuStart(), false);
                        $this->bot->reply($this->room->roomMenu(), false);
                        break;
                    case strtolower('food&beverage') :
                        $this->bot->reply($this->post->foodAndBeverageMenu(), false);
                        break;
                    case strtolower('food & beverage') :
                        $this->bot->reply($this->post->foodAndBeverageMenu(), false);
                        break;
                    case  strtolower('Restaurant & Events'):
                        $this->bot->reply($this->post->foodAndBeverageMenu(), false);
                        break;
                    case strtolower('inquiries'):
                        $this->bot->reply($this->post->inquiry(), false);
                        break;
                    case strtolower('BANQUET'):
                        $this->bot->reply($this->package->packageMenu(), false);
                        break;
                    case strtolower('cafe mezzo'):
                        $this->food->foods();
                        break;
                    case strtolower('ASK ANOTHER QUESTION'):
                        $this->tracker->delete();
                        $this->bot->reply($this->dialog->startFaq("faq"), false);
                        break;
                    case strtolower('GO BACK TO MENU'):
                        $this->tracker->delete();
                        $this->bot->reply($this->post->start(), false);
                        break;
                    case strtolower('CONCERN/INQUIRY'):
                        $this->tracker->delete();
                        $this->bot->reply($this->package->concerns($custom['parameter']), false);
                        break;
                    default:
                        return response("", 200);
                        break;
                }
                break;
            case $this->code->pPackageSelected:
                $this->tracker->delete();
                $this->bot->reply($this->package->packages(), false);

                break;
            case $this->code->pPackageInquiry:
                $this->tracker->delete();
                $this->bot->reply($this->package->packageInquiry(), false);
                //
                break;
            case $this->code->pRoomMenuSelected:
                $this->tracker->delete();
                switch (strtolower($custom['parameter'])) {
                    case 'rooms':
                        // $this->bot->reply($this->room->rooms(false), false);
                        $this->room->rooms(false);
                        //
                        break;
                    case strtolower('GROUP RESERVATIONS'):
                        // $this->bot->reply($this->room->rooms(true), false);
                        $this->room->rooms(true);
                        //
                        break;
                    default:
                        return '';
                        break;
                }
                break;
            case $this->code->pRoomInquiry:
                $this->tracker->delete();
                $this->bot->reply($this->room->roomReservation(), false);
                break;
            case $this->code->pFaq:
                $this->tracker->delete();
                $this->bot->reply($this->dialog->startFaq("faq"), false);
                break;
            case $this->code->pSearch:
                $this->bot->reply($this->search->searchOption(), false);
                break;
            case $this->code->pGetQueueCard:
                $this->forms->retrieveForms($custom['parameter']);
                break;
            case $this->code->pLocate:
                //Do Something
                break;
            case $this->code->pNext:
                //Do Something
                break;
            case $this->code->pSend:
                $this->bot->reply($this->send->submit(), false);
                break;
            case $this->code->pEdit:
                $this->bot->reply($this->edit->manage($custom), false);
                break;
            case $this->code->pDisregard:
                $this->bot->reply($this->disregard->inform(), false);
                break;
            case $this->code->pQCViewDetails:
                //View Details
                $this->bot->reply($this->details->viewDetails($custom['parameter']), false);
                break;
            case $this->code->pCancelQC:
                $this->bot->reply($this->qc->informCancel($custom['parameter']), false);
                break;
            case $this->code->pPostponeQC:
                $this->bot->reply($this->qc->informPostpone($custom['parameter']), false);
                break;
            case $this->code->pEditDetails:
                $this->bot->reply($this->editDetails->manage($custom['parameter']), true);
                break;
            default:
                //Error
                break;
        }
    }
}
