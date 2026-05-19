<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Jobs\ProcessDailySalesJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;   
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;

Route::prefix('v1/auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api');

    Route::middleware(['auth:sanctum','throttle:api'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});
Route::prefix('v1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::middleware(['auth:sanctum','throttle:api'])->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    });
});
Route::middleware(['auth:sanctum'])->prefix('v1/cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/add', [CartController::class, 'add']);
    Route::put('/update/{id}', [CartController::class, 'update']);
    Route::delete('/remove/{id}', [CartController::class, 'remove']);
    Route::delete('/clear', [CartController::class, 'clear']);
});
Route::middleware(['auth:sanctum'])->post(
    '/v1/orders/checkout',
    [OrderController::class, 'checkout']
);


Route::get('/redis',function(){
    Redis::Set('name','mohee');
    return Redis::get('name');
});
Route::get('/server-check', function () {
    return [
        'pid' => getmypid(),
        'port' => request()->server('SERVER_PORT'),
    ];
});
