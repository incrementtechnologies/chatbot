<?php

namespace App\Http\Controllers;

use App\Ilinya\Bot;
use App\Ilinya\BotTracker;
use App\Ilinya\Http\Curl;
use App\Ilinya\ImageGenerator;
use App\Ilinya\Response\Facebook\SurveyResponse;
use App\Ilinya\Webhook\Facebook\Entry;
use App\Jobs\BotHandler;
use App\Jobs\ChatbotBroadcast;
use App\Jobs\TestDatabaseQueryEffect;
use App\Logs;
use Illuminate\Http\Request;

class IlinyaController extends APIController
{
    protected $tracker;
    public function hook(Request $request)
    {
        // return response("", 200);
        $entries = Entry::getEntries($request);
        foreach ($entries as $entry) {
            $messagings = $entry->getMessagings();
            $temp_messagings = [];
            foreach ($messagings as $messaging) {
                $recepientId = $messaging->getSenderId();
                Curl::typing($recepientId, 'mark_seen');
                $this->tracker = new BotTracker($messaging);
                if (!$this->checkDuplicate($messaging)) {
                    Curl::typing($recepientId, 'typing_on');
                    dispatch(new BotHandler($messaging));
                } else {
                    return response("", 200);
                }

            }
        }
        // $this->tracker->remove();
        return response("", 200);
    }

    private function checkDuplicate($messaging)
    {
        $data = [
            "userID" => $messaging->getSenderId(),
            "recepientID" => $messaging->getRecipientId(),
            "type" => $messaging->getType(),
            "msgID" => null,
        ];

        if ($messaging->getType() == 'postback') {
            $data['message'] = $messaging->getPostback()->getTitle() . $messaging->getPostback()->getPayload();
        } else {
            if ($messaging->getMessage()->getQuickReply()) {
                $msg = $messaging->getMessage()->getQuickReply();
                $data['message'] = strtolower($msg['payload'] . $msg['parameter']);
            } else {
                $data['message'] = strtolower($messaging->getMessage()->getText());
                $data['msgID'] = $messaging->getMessage()->getId();
            }
        }
        $result = Logs::where($data)->get();
        if (sizeof($result) > 0) {
            return true;
        } else {
            Logs::updateOrCreate(
                [
                    "userID" => $messaging->getSenderId(),
                    "recepientID" => $messaging->getRecipientId(),
                    "type" => $messaging->getType(),
                ],
                $data
            );
            return false;
        }
    }

    public function broadcast($message)
    {
        $companyId = $this->getUserCompanyID();
        dispatch(new ChatbotBroadcast($companyId, $message));
    }

    public function paging($recepientId, $message, $surveyMode)
    {
        Bot::notify($recepientId, $message);
        if (intval($surveyMode) == 1 || $surveyMode == '1') {
            //Set to survey mode
            $surveyMessage = SurveyResponse::requestForSurvey($recepientId);
            Bot::survey($recepientId, $surveyMessage);
        }
    }

    public function reminder($recepientId, $message, $surveyMode)
    {
        Bot::notify($recepientId, $message);
        if (intval($surveyMode) == 1 || $surveyMode == '1') {
            //Set to survey mode
            $surveyMessage = SurveyResponse::requestForSurvey($recepientId);
            Bot::survey($recepientId, $surveyMessage);
        }
    }

    public function getStarted(Request $request)
    {
        Curl::started();
    }

    public function persistent(Request $request)
    {
        Curl::setupMenu();
    }

    public function createImage()
    {
        ImageGenerator::create();
    }

    public function test1($size)
    {
        dispatch(new TestDatabaseQueryEffect($size));
    }

    public function gettingStared(Request $request)
    {
        return "ok";
    }
}
