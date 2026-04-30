<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'abood',
            'email' => 'abd@gmail.com',
            'password' => bcrypt('password'),
        ]);
        User::factory()->create([
            'name' => 'abood2',
            'email' => 'abd2@gmail.com',
            'password' => bcrypt('password'),
        ]);
        User::factory()->create([
            'name' => 'abood3',
            'email' => 'abd3@gmail.com',
            'password' => bcrypt('password'),
        ]);
        User::factory()->create([
            'name' => 'abood4',
            'email' => 'abd4@gmail.com',
            'password' => bcrypt('password'),
        ]);
        User::factory()->create([
            'name' => 'abood5',
            'email' => 'abd5@gmail.com',
            'password' => bcrypt('password'),
        ]);
        Product::create([
            'name' => 'Product 1',
            'description' => "Description for Product 1",
            'price' => 19.99,
            'stock' => 1
        ]);
    }
}
