<?php

namespace App\Ilinya\Http;
use Symfony\Component\HttpFoundation\Response;


class Curl{

    function __construct(){
      return $this;
    }

    public function getUser($userId){
      $url = "https://graph.facebook.com/v2.6/".$userId."?fields=first_name,last_name,profile_pic,locale,timezone,gender";
      return $this->get($url, true);
    }

    public static function send($recipientId, $message){
      $parameter = [
          "recipient" => [
              "id" => $recipientId
          ],
          "message" => $message
      ];
      $url = 'https://graph.facebook.com/v2.6/me/messages';
      $curl = new Curl();
      $curl->post($url,$parameter);
    }


    public function post($url, $parameter){
     $request = $this->prepare($url, false);

      curl_setopt($request, CURLOPT_POST, count($parameter));
      curl_setopt($request, CURLOPT_POSTFIELDS, json_encode($parameter));
      return $this->execute($request);
    }

    public function get($url, $flag){
      $request = $this->prepare($url, $flag);
      return $this->executeBody($request);
    }

    public function prepare($url, $flag){
      $request = curl_init();
      $page_access_token = "";
      $envFbStatus = env('FB_TOKEN_STATUS');
      echo  $envFbStatus;
      // true = live, false = test
      if($envFbStatus == true){
        $page_access_token = "access_token=EAACfJZAjQCwcBAHGVRzk0BazJOn5ZBm8ZAtG6ZB3Bft8ZAdAUPEZB6z9bo4QpsdQl5vciEPiO7p2KLCTvxoyGOcHL4HHv22DiDgaroCjpVrCrbzdAWuqEPGV2uQwrtB5wiBbROEJFxWHRkRvTT1WXJjWhZBUuPL1RYcbzMltE28lhRbThpKYoUY"; 
      }
      else{
        $page_access_token = "access_token=EAAFRBiltHcQBAO1VFEe37bKHt7SA27ACcNaepjFYCRWMoE3Ke2a2SSwC8KYBZAchwlYbWlyk1nIZAVmVtq43ZBfa62ZBUIvTfhf7OO1PrlGAefZAsdue2jNpwkfeZAxe1dfZBelF0093yauIAd58M8nCZAMGqnkuy70mliBzIUUWTZCDI41noMfxe";
      }

      $url .= ($flag == false)? '?'.$page_access_token:'&'.$page_access_token;  
        
      curl_setopt($request, CURLOPT_URL, $url);
      curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($request, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
      curl_setopt($request, CURLINFO_HEADER_OUT, true);
      curl_setopt($request, CURLOPT_SSL_VERIFYPEER, true);

      return $request;
    }

    public function execute($request){  
      $body = curl_exec($request);
      $info = curl_getinfo($request);
      curl_close($request);
      $statusCode = $info['http_code'] === 0 ? 500 : $info['http_code'];
      return new Response((string) $body, $statusCode, []);
    }

    public function executeBody($request){
      return json_decode(curl_exec($request),true);
    }

}