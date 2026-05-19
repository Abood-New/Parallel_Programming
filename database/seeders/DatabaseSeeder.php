<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // // User::factory(10)->create();

        // // User::factory()->create([
        // //     'name' => 'abood',
        // //     'email' => 'abd@gmail.com',
        // //     'password' => bcrypt('password'),
        // // ]);
        // // User::factory()->create([
        // //     'name' => 'abood2',
        // //     'email' => 'abd2@gmail.com',
        // //     'password' => bcrypt('password'),
        // // ]);
        // // User::factory()->create([
        // //     'name' => 'abood3',
        // //     'email' => 'abd3@gmail.com',
        // //     'password' => bcrypt('password'),
        // // ]);
        // // User::factory()->create([
        // //     'name' => 'abood4',
        // //     'email' => 'abd4@gmail.com',
        // //     'password' => bcrypt('password'),
        // // ]);
        // // User::factory()->create([
        // //     'name' => 'abood5',
        // //     'email' => 'abd5@gmail.com',
        // //     'password' => bcrypt('password'),
        // // ]);
        // Product::create([
        //     'name' => 'Product 1',
        //     'description' => "Description for Product 1",
        //     'price' => 19.99,
        //     'stock' => 1
        // ]);

        // // Product::factory()->count(200)->create([
        // //     'stock' => 1000,
        // // ]);

        // User::factory()->count(5)->create([
        //     'password' => bcrypt('password'),
        // ])->each(function ($user) {

        //     $cart = Cart::create([
        //         'user_id' => $user->id
        //     ]);


        //     CartItem::create([
        //         'cart_id' => $cart->id,
        //         'product_id' => 1,
        //         'quantity' => 1,
        //     ]);
        // });
        DB::beginTransaction();
        try {

            $products = Product::factory()
                ->count(300)
                ->create([
                    'stock' => rand(5000, 10000),
                ]);

            $users = collect();

            for ($i = 1; $i <= 600; $i++) {

                $users->push(
                    User::factory()->create([
                        'name' => "Load Test User {$i}",
                        'email' => "loadtest_{$i}@test.com",
                        'password' => bcrypt('password'),
                    ])
                );
            }

            $tokens = [];

            foreach ($users as $user) {
                $tokens[] = $user
                    ->createToken('k6')
                    ->plainTextToken;
            }

            File::put(
                base_path('tokens.json'),
                json_encode($tokens, JSON_PRETTY_PRINT)
            );

            foreach ($users as $user) {
                $cart = Cart::create([
                    'user_id' => $user->id,
                ]);

                // each user gets 2–5 products
                $randomProducts = $products
                    ->random(rand(2, 5));

                foreach ($randomProducts as $product) {

                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'quantity' => rand(1, 2),
                    ]);
                }
            }

            DB::commit();

            $this->command->info('Performance dataset generated successfully!');
        } catch (\Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
