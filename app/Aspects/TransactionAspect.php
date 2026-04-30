<?php

namespace App\Aspects;

use Illuminate\Support\Facades\DB;

class TransactionAspect
{
    public static function handle($callback)
    {
        return DB::transaction(function () use ($callback) {
            return $callback();
        });
    }
}