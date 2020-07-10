<?php

namespace App\Ilinya\Templates\Facebook;

class PersistentMenuElement{

  /*
    @Enum
    @R
  */
  protected $type;

  /*
    @String
    @R
  */
  protected $title;
  
  /*
    @String
    @R  
  */
  protected $url;
 
  /*
    @String
    @R
  */
  protected $payload;


  /*
    @Enum
    @NR
    @Default = Full
  */
  protected $webviewHeightRatio;

  /*
    @String
    @NR
  */


  function __construct($title){
    $this->title = $title;
  }

  public function type($type){
    $this->type = $type;
    return $this;
  }
  
  public static function title($title){
    return new static($title);
  }

  public function url($url){
    $this->url = $url;
    return $this;
  }

  public function payload($payload){
    $this->payload = $payload;
    return $this;
  }

  public function ratio($webviewHeightRatio){
    $this->webviewHeightRatio = $webviewHeightRatio;
    return $this;
  }

  public function toArray(){
    $response["type"] = $this->type;
    if($this->type == "web_url"){
      $response["url"] = $this->url;
      $response["webview_height_ratio"] = $this->webviewHeightRatio;
    }
    else{
      $response["payload"] = $this->payload;
    }
    if($this->title != null){
      $response['title'] = $this->title;
    }
    return $response;
  }

}