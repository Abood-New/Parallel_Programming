<?php

namespace App\Jobs;

use App\Aspects\LoggingAspect;
use App\Models\Order;
use App\Models\SalesSummary;
use DB;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDailySalesJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;
    protected $date;

    /**
     * Create a new job instance.
     */
    public function __construct($date = null)
    {
        $this->date = $date ?? now()->subDay()->toDateString();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        LoggingAspect::jobLog("Starting daily sales processing for date: {$this->date}");
        $totalRevenue = 0;
        $totalOrders = 0;
        Order::where("created_at", $this->date)->
            where('status', 'paid')
            ->chunkById(200, function ($orders) use (&$totalRevenue, &$totalOrders) {
                DB::transaction(function () use ($orders, &$totalRevenue, &$totalOrders) {
                    foreach ($orders as $order) {
                        $totalRevenue += $order->total;
                        $totalOrders++;
                    }
                });
                LoggingAspect::jobLog('Processed chunk:{$orders->count()} orders');
            });

        SalesSummary::updateOrCreate(
            ['date' => $this->date],
            ['total_revenue' => $totalRevenue, 'total_orders' => $totalOrders]
        );
        LoggingAspect::jobLog("Completed daily sales processing for date: {$this->date}. Total Revenue: {$totalRevenue}, Total Orders: {$totalOrders}");

    }
    public function failed(Exception $exception): void
    {
        LoggingAspect::jobLog("Daily sales processing failed for date: {$this->date}. Error: {$exception->getMessage()}");
    }
}