<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ConsistencyStressSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            // تنظيف الجداول
            // DB::table('cart_items')->truncate();
            // DB::table('carts')->truncate();
            // DB::table('personal_access_tokens')->truncate();
            // DB::table('orders')->truncate();
            // DB::table('order_items')->truncate();
            // DB::table('products')->truncate();
            // DB::table('users')->truncate();

            /*
            |--------------------------------------------------------------------------
            | TEST SCENARIO CONFIG
            |--------------------------------------------------------------------------
            */

            $totalUsers = 100;

            // المنتج الأساسي للاختبار
            $initialStock = 50;

            // كل مستخدم سيحاول شراء 1
            $quantityPerUser = 1;

            /*
            |--------------------------------------------------------------------------
            | CREATE STRESS TEST PRODUCT
            |--------------------------------------------------------------------------
            */

            $product = Product::create([
                'name' => 'Stress Test Product',
                'description' => 'Used for consistency testing',
                'price' => 100,
                'stock' => $initialStock,
            ]);

            /*
            |--------------------------------------------------------------------------
            | CREATE USERS + TOKENS + CARTS
            |--------------------------------------------------------------------------
            */

            $tokens = [];

            for ($i = 1; $i <= $totalUsers; $i++) {

                $user = User::factory()->create([
                    'name' => "Stress User {$i}",
                    'email' => "stress{$i}@test.com",
                    'password' => bcrypt('password'),
                ]);

                // token
                $tokens[] = $user
                    ->createToken('k6')
                    ->plainTextToken;

                // cart
                $cart = Cart::create([
                    'user_id' => $user->id,
                ]);

                // ALL USERS BUY SAME PRODUCT
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $quantityPerUser,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | SAVE TOKENS FOR K6
            |--------------------------------------------------------------------------
            */

            File::put(
                base_path('tokens.json'),
                json_encode($tokens, JSON_PRETTY_PRINT)
            );

            /*
            |--------------------------------------------------------------------------
            | EXPECTED RESULT FILE
            |--------------------------------------------------------------------------
            */

            $expectedRemainingStock =
                $initialStock - ($totalUsers * $quantityPerUser);

            File::put(
                base_path('expected-results.json'),

                json_encode([
                    'initial_stock' => $initialStock,
                    'users' => $totalUsers,
                    'quantity_per_user' => $quantityPerUser,

                    'expected_successful_orders' =>
                        min(
                            $initialStock,
                            $totalUsers
                        ),

                    'expected_failed_orders' =>
                        max(
                            0,
                            $totalUsers - $initialStock
                        ),

                    // SHOULD NEVER BE NEGATIVE
                    'expected_final_stock' =>
                        max(0, $expectedRemainingStock),
                ], JSON_PRETTY_PRINT)
            );

            DB::commit();

            $this->command->info(
                'Consistency stress-test dataset generated successfully!'
            );

        } catch (\Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }
}