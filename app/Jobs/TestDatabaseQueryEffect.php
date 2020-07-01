<?php

namespace App\Jobs;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class TestDatabaseQueryEffect implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $tries   = 1;
    public $timeout = 5;
    public $myCounterSize;
    public function __construct($size)
    {
        $this->myCounterSize = $size;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /*
                This Job will test on how the query affects the timer of the Job
                and exceeds the timeout.
        */

        //Make a loop
        $i = 1;
        $name = "business_types";
        try{   
            while($i <= $this->myCounterSize){
                $result = DB::table($name)->whereNull('deleted_at')->get();
                $result = DB::table($name)->whereNull('deleted_at')->get();
                $result = DB::table($name)->whereNull('deleted_at')->get();
                $result = DB::table($name)->whereNull('deleted_at')->get();
                $result = DB::table($name)->whereNull('deleted_at')->get();
                $result = DB::table($name)->whereNull('deleted_at')->get();
                $result = DB::table($name)->whereNull('deleted_at')->get();
                $result = DB::table($name)->whereNull('deleted_at')->get();
                $result = DB::table($name)->whereNull('deleted_at')->get();
                $result = DB::table($name)->whereNull('deleted_at')->get();
                $result = DB::table($name)->whereNull('deleted_at')->get();
                $i++;
            }
            echo 'Test Succeeded where size = '.$this->myCounterSize. ', timeout = '.$this->timeout.' and tries = '.$this->tries;
        }catch(Exception $e){
            $this->failed($e);
        }
    }

    public function failed($exception){
        //
        //Log::info('Error at time is '.Carbon::now() .PHP_EOL.' where error is'.$exception->getMessage());
        echo 'Test Failed where size = '.$this->myCounterSize. ', timeout = '.$this->timeout.' and tries = '.$this->tries;
        echo '<br />Error is '.$exception->getMessage();
    }
}
