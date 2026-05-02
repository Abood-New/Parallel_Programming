<?php

namespace App\Jobs;

use App\Mail\OrderPlacedEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Mail;

class SendOrderEmailJob implements ShouldQueue
{
    use Queueable;
    public $order;
    /**
     * Create a new job instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Illuminate\Support\Facades\Mail::to($this->order->user->email)
        ->send(new OrderPlacedEmail($this->order));
    }
}
