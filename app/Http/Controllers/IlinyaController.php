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
class IlinyaController extends APIController
{
    public function hook(Request $request){
        $entries = Entry::getEntries($request);
        foreach ($entries as $entry) {
            $messagings = $entry->getMessagings();
            foreach ($messagings as $messaging) {
                dispatch(new BotHandler($messaging));
            }
        }
        return response("", 200);
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


