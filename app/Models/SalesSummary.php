<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesSummary extends Model
{
    /** @use HasFactory<\Database\Factories\SalesSummaryFactory> */
    use HasFactory;
        protected $fillable = ['date', 'total_revenue', 'total_orders'];

}
