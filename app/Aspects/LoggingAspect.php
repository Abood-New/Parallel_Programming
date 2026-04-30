<?php

namespace App\Aspects;

class LoggingAspect {
    public static function unsafeLog($message)
        {
            file_put_contents(
                storage_path('logs/unsafe_code.log'),
                $message . PHP_EOL,
                FILE_APPEND
            );
        }
    public static function safeLog($message)
        {
            file_put_contents(
                storage_path('logs/safe_code.log'),
                $message . PHP_EOL,
                FILE_APPEND
            );
        }
}