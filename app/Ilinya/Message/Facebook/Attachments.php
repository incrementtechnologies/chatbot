<?php


namespace App\Ilinya\Message\Facebook;

class Attachments{
    
    protected $type;

    protected $LOCATION = 1;

    function __construct($type){
      $this->type = $type;
    }

    public static function manage(){
      switch ($this->type) {
        case $this->LOCATION:
          $this->location();
          break;
        default:
          # code...
          break;
      }
    }

    public function location(){
      //Do Something
    }

    public function media(){
      //Do Something
    }
}

