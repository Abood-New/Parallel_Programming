<?php 

namespace App\Aspects;

use App\Jobs\SendOrderEmailJob;

class NotificationAspect
{
    public static function afterOrderCreated($order)
    {
        SendOrderEmailJob::dispatch($order)->afterCommit();
    }
}