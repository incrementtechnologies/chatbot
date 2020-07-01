<?php

namespace App\Ilinya\Message\Facebook;

use App\Ilinya\Bot;
use App\Ilinya\Tracker;
use App\Ilinya\Message\Facebook\Codes;
use App\Ilinya\Message\Facebook\Form;
use App\Ilinya\Response\Facebook\PostbackResponse;
use App\Ilinya\Response\Facebook\CategoryResponse;
use App\Ilinya\Response\Facebook\SendResponse;
use App\Ilinya\Response\Facebook\EditResponse;
use App\Ilinya\Response\Facebook\QueueCardsResponse;
use App\Ilinya\Response\Facebook\DisregardResponse;
use App\Ilinya\Response\Facebook\ErrorResponse;
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\API\Database as DB;

class Error{
    protected $code;
    protected $tracker;
    protected $error;
    protected $bot;
    protected $forms;

    function __construct(Messaging $messaging){
        $this->bot    = new Bot($messaging);
        $this->forms   = new Form($messaging);
        $this->tracker= new Tracker($messaging);
        $this->code   = new Codes(); 
        $this->error  = new ErrorResponse($messaging);
    }
    public function manage($custom){
      $this->bot->reply($this->error->stage(), false);
      $data = [
        "prev_status" => $custom['payload']
      ];
      $this->tracker->update($data);
    }

    public function clearCustomField(){
      $db_field = "temp_custom_fields_storage";

      $condition = [
          ['track_id', '=', $this->tracker->getId()]
      ];
      $result = DB::delete($db_field, $condition);
    }
}