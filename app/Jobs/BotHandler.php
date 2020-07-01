<?php

namespace App\Jobs;


use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\MessageHandler;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Cache;
class BotHandler implements ShouldQueue{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $messaging;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Messaging $messaging){
        $this->messaging = $messaging;
    }
    /**
     * Execute the job.
     *
     * @param Messaging $messaging
     */
    public function handle(){
        $messageHandler  = new MessageHandler($this->messaging);
        $messageHandler->manage();
    }
}
