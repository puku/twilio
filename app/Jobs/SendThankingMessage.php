<?php

namespace App\Jobs;

use App\Services\Twilio;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Services_Twilio;
use Exception;
use Illuminate\Support\Facades\Log;

class SendThankingMessage extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    const DELAY = 1080; // 18 * 60


    protected $callHistory;

    /**
     * SendThankingMessage constructor.
     * @param $callHistory
     */
    public function __construct($callHistory)
    {
        $this->callHistory = $callHistory;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $callSid = $this->callHistory->call_sid;
            $client = new Services_Twilio(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));
            $twilio = new Twilio($client);
            $twilio->sendThankingMessage($callSid);
        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
