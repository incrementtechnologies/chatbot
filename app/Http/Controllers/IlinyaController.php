<?php

namespace App\Http\Controllers;
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


    public function hook(Request $request){
        $entries = Entry::getEntries($request);
        foreach ($entries as $entry) {
            $messagings = $entry->getMessagings();
            $temp_messagings = [];
            foreach ($messagings as $messaging) {
                if (sizeof($temp_messagings) > 0) {
                    foreach ($temp_messagings as $temp) {
                        if (!$this->checkDuplicate($messaging , $temp)) {
                            $temp_messagings[] = $messaging;
                            dispatch(new BotHandler($messaging));
                        } 
                    }
                } else {
                    $temp_messagings[] = $messaging;
                    dispatch(new BotHandler($messaging));
                }
                
            }
        }
        return response("Ok", 200);
    }

    private function checkDuplicate($messaging , $temp){
        //  ($messaging->getTimestamp() == $temp->getTimestamp()) &&
        if (
            ($messaging->getSenderId() == $temp->getSenderId()) &&
            ($messaging->getRecipientId() == $temp->getRecipientId()) &&
            ($messaging->getType() == $temp->getType()) &&
            ($messaging->getMessage()->getId() == $temp->getMessage()->getId()) &&
            ($messaging->getMessage()->getText() == $temp->getMessage()->getId()) &&
            ($messaging->getMessage()->getQuickReply() == $temp->getMessage()->getQuickReply()) &&
            ($messaging->getPostback() == $temp->getPostback())
        ) {
            return true;
        }
        return false;
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


