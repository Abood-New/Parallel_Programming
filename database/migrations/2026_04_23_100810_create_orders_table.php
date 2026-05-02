<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    protected $model = Order::class;
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }

     public function pending()
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function paid()
    {
        return $this->state(fn () => ['status' => 'paid']);
    }

    public function failed()
    {
        return $this->state(fn () => ['status' => 'failed']);
    }
};
