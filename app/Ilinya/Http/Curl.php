<?php

namespace App\Ilinya\Http;

use Symfony\Component\HttpFoundation\Response;

class Curl
{

    public function __construct()
    {
        return $this;
    }

    public function getUser($userId)
    {
        $url = "https://graph.facebook.com/v2.6/" . $userId . "?fields=first_name,last_name,profile_pic,locale,timezone,gender";
        return $this->get($url, true);
    }

    public static function send($recipientId, $message)
    {
        $parameter = [
            "recipient" => [
                "id" => $recipientId,
            ],
            "message" => $message,
        ];
        $url = 'https://graph.facebook.com/v2.6/me/messages';
        $curl = new Curl();
        $curl->post($url, $parameter);
        // return response("", 200);
    }

    public static function typing($recipientId, $action)
    {
        $parameter = [
            "recipient" => [
                "id" => $recipientId,
            ],
            "sender_action" => $action,
        ];
        $url = 'https://graph.facebook.com/v2.6/me/messages';
        $curl = new Curl();
        $curl->post($url, $parameter);
        // return response("", 200);
    }

    public static function started()
    {
        $body = [
            "get_started" => [
                "payload" => "@pStart",
            ],
        ];
        $url = "https://graph.facebook.com/v2.6/me/messenger_profile";
        $curl = new Curl();
        $curl->post($url, $body);
    }

    public static function setupMenu()
    {
        $body = [
            "persistent_menu" => [
                [
                    "locale" => "default",
                    "composer_input_disabled" => false,
                    "call_to_actions" => [
                        [
                            "type" => "postback",
                            "title" => "Rooms",
                            "payload" => "rooms@pCategorySelected",
                        ],
                        [
                            "type" => "postback",
                            "title" => "Restaurant & Events",
                            "payload" => "food_&_beverage@pCategorySelected",
                        ],
                        [
                            "type" => "postback",
                            "title" => "Inquiries",
                            "payload" => "inquiries@pCategorySelected",
                        ],
                    ],
                ],
            ],
        ];
        // $url = "https://graph.facebook.com/v8.0/me/messenger_profile";
        $url = "https://graph.facebook.com/v7.0/me/messenger_profile";
        $curl = new Curl();
        $curl->post($url, $body);
    }

    public function post($url, $parameter)
    {
        $request = $this->prepare($url, false);
        curl_setopt($request, CURLOPT_POST, count($parameter));
        curl_setopt($request, CURLOPT_POSTFIELDS, json_encode($parameter));
        return $this->execute($request);
    }

    public function get($url, $flag)
    {
        $request = $this->prepare($url, $flag);
        set_time_limit(0);
        return $this->executeBody($request);
    }

    public function prepare($url, $flag)
    {
        $request = curl_init();
        $page_access_token = "access_token=";
        $envFbStatus = env('FB_TOKEN_STATUS');
        echo $envFbStatus;
        // true = live, false = test
        if ($envFbStatus == true) {
            // $page_access_token = "access_token=EAACxnaNUyvkBADAnlNzxJ34ZCBv0QQ0VAekrsFZCziGwfOr0cM5uZAtNVQ7ZBDF8Er8hD6RUow0kgmPLufzP5HFjIFNmZASGAhZCZAE5zCpWiCIZBZCR5Bcxh5PTIBreHrC93OKPZBAlh08eYFZBsX1ZACEjbfgZAY7hh5WPZAGdO9TAWJ90spglOZBTfNG" ;
            $page_access_token = "access_token=" . env('LIVE_FB_ACCESS_TOKEN');
        } else {
            // $page_access_token = "access_token=EAACxnaNUyvkBADAnlNzxJ34ZCBv0QQ0VAekrsFZCziGwfOr0cM5uZAtNVQ7ZBDF8Er8hD6RUow0kgmPLufzP5HFjIFNmZASGAhZCZAE5zCpWiCIZBZCR5Bcxh5PTIBreHrC93OKPZBAlh08eYFZBsX1ZACEjbfgZAY7hh5WPZAGdO9TAWJ90spglOZBTfNG";
            $page_access_token = "access_token=" . env('DEV_FB_ACCESS_TOKEN');
        }
        $url .= ($flag == false) ? '?' . $page_access_token : '&' . $page_access_token;
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($request, CURLINFO_HEADER_OUT, true);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, true);
        return $request;
    }

    public function execute($request)
    {
        set_time_limit(0);
        $body = curl_exec($request);
        $info = curl_getinfo($request);
        $statusCode = $info['http_code'] === 0 ? 500 : $info['http_code'];
        curl_close($request);
        return new Response((string) $body, $statusCode, []);
    }

    public function executeBody($request)
    {
        $result = json_decode(curl_exec($request), true);
        curl_close($request);
        return $result;
    }

    public function whitelistWebView()
    {
        $url = "https://graph.facebook.com/v7.0/me/messenger_profile?access_token=";
        $envFbStatus = env('FB_TOKEN_STATUS');
        echo $envFbStatus;
        // true = live, false = test
        if ($envFbStatus == true) {
            $url .= env('LIVE_FB_ACCESS_TOKEN');
        } else {
            $url .= env('DEV_FB_ACCESS_TOKEN');
        }
        $data = array(
            'whitelisted_domains' => explode(",", env('WHITELISTED_DOMAINS')),
        );
        $payload = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload))
        );
        // Submit the POST request
        set_time_limit(0);
        $result = curl_exec($ch);
        // Close cURL session handle
        curl_close($ch);
    }

}
