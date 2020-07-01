<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use App\Ilinya\Bot;
use App\Ilinya\API\Controller;
use Illuminate\Http\Request;
class ChatbotBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $companyId;
    protected $message;
    public function __construct($companyId, $message)
    {
        $this->companyId = $companyId;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /*
            1. Get User having transactions with CompanyId
            2. Send Messages to the users
            3. Catch Exceptions
        */
            $activeUsers = $this->retrieve();

            foreach ($activeUsers as $user) {
                $facebookInfo = $this->getFacebookUserInfo($user['facebook_user_id']);
                 $newMessage = "Hi ".$facebookInfo[0]['full_name'].'!'.$this->message;
                 Bot::notify($facebookInfo[0]['account_number'], $newMessage);
            }
           
    }

    public function retrieve(){
        $request = new Request();
        $condition[] = [
          "column"  => "company_id",
          "clause"  => "=",
          "value"   => $this->companyId
        ];
        $request['condition'] = $condition;
        $data = Controller::retrieve($request, "App\Http\Controllers\QueueCardController");
        return $data;
    }

    public function getFacebookUserInfo($id){
        $request = new Request();
        $condition[] = [
          "column"  => "id",
          "clause"  => "=",
          "value"   => $id
        ];
        $request['condition'] = $condition;
        $data = Controller::retrieve($request, "App\Http\Controllers\FacebookUserController");
        return $data;
    }
}
