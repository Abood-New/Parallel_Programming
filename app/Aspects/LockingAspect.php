<?php

namespace App\Aspects;

class LockingAspect
{
    public static function handle($query, $callback)
    {
        $model = $query->lockForUpdate()->first();

        return $callback($model);
    }
}