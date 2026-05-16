<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('app:generate-tokens')]
#[Description('Command description')]
class GenerateTokens extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tokens = [];

        $users = User::take(5)->get();

        foreach ($users as $user) {
            $token = $user->createToken('k6')->plainTextToken;
            $tokens[] = $token;
        }

        File::put(
            base_path('tokens.json'),
            json_encode($tokens, JSON_PRETTY_PRINT)
        );

        $this->info('Tokens generated!');
    }
}
