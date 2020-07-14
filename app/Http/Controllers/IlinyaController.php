<?php

namespace App\Http\Controllers;
use App\Logs;
use App\Ilinya\BotTracker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Ilinya\Webhook\Facebook\Entry;
use App\Jobs\BotHandler;
use App\Ilinya\Bot;
use App\Jobs\TestDatabaseQueryEffect;
use App\Jobs\ChatbotBroadcast;
use App\Ilinya\ImageGenerator;
use App\Ilinya\Response\Facebook\SurveyResponse;
use App\Ilinya\Http\Curl;
class IlinyaController extends APIController
{
    protected $tracker;   
    public function hook(Request $request){
        // return response("", 200);
        $entries = Entry::getEntries($request);
        foreach ($entries as $entry) {
            $messagings = $entry->getMessagings();
            $temp_messagings = [];
            foreach ($messagings as $messaging) {
                $this->insertLog($messaging);
                $this->tracker = new BotTracker($messaging);
                    if (! $this->checkDuplicate($messaging)) {
                        dispatch(new BotHandler($messaging));
                    } else {
                        return response("", 200);
                    }
                }
            }
        
        return response("", 200);
        $this->tracker->remove();
    }

    private function insertLog($messaging){
        \Log::info("Details:\n\tgetRecipientId:\t".
        ($messaging->getRecipientId()) . ",\n\tgetTimestamp:".
        ($messaging->getTimestamp()) . ",\n\tgetSenderId:\t".
        ($messaging->getSenderId()) .",\n\tgetRecipientId:\t".
        ($messaging->getType()) .",\n\tContent:".
        ($messaging->getPostback()!=null?$messaging->getPostback()->getPayload():"\n\t\tID : "
        .($messaging->getMessage()->getId()) .",\n\t\tgetMessageText:\t".
        ($messaging->getMessage()->getText()) .",\n\t\tgetMessageQR:\t".
        ($messaging->getMessage()->getQuickReply()["payload"]))."\n" );
    }

    private function checkDuplicate($messaging){
        $data =[
            "userID"=>$messaging->getSenderId(),
            "recepientID"=>$messaging->getRecipientId(),
            "type"=> $messaging->getType(),
        ];

        if ($messaging->getType() =='postback') {
            $data['message'] = $messaging->getPostback()->getPayload();
        }else{
            if ($messaging->getMessage()->getQuickReply()) {
                # code...
                $msg =$messaging->getMessage()->getQuickReply();
                $data['message'] = strtolower($msg['payload'].$msg['parameter']);
            } else {
                 $data['message'] = strtolower($messaging->getMessage()->getText());
            }
        }
        $result = Logs::where($data)->get();
        \Log::info(sizeof($result)."results found");
        if (sizeof($result) > 0) {
            return true;
        } else {
            // insert to DB
            Logs::updateOrCreate(
                [
                    "userID"=>$messaging->getSenderId(),
                    "recepientID"=>$messaging->getRecipientId(),
                ],
                $data
            );
            return false;
        }
    }

    public function broadcast($message){
        $companyId = $this->getUserCompanyID();
        dispatch(new ChatbotBroadcast($companyId, $message));
    }

    public function paging($recepientId, $message, $surveyMode){
        Bot::notify($recepientId, $message);
        if(intval($surveyMode) == 1 || $surveyMode == '1'){    
            //Set to survey mode
            $surveyMessage = SurveyResponse::requestForSurvey($recepientId);
            Bot::survey($recepientId, $surveyMessage);
        }       
    }

    public function reminder($recepientId, $message, $surveyMode){
        Bot::notify($recepientId, $message);
        if(intval($surveyMode) == 1 || $surveyMode == '1'){    
            //Set to survey mode
            $surveyMessage = SurveyResponse::requestForSurvey($recepientId);
            Bot::survey($recepientId, $surveyMessage);
        }   
    }

    public function createImage(){
        ImageGenerator::create();
    }

    public function test1($size){
        dispatch(new TestDatabaseQueryEffect($size));
    }
}


