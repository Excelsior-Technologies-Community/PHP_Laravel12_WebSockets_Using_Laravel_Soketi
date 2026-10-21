<?php

namespace Database\Seeders;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WebSocketDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Demo Users for Presence & Private Channels
        $users = [
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'avatar' => '👩‍💻'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com', 'avatar' => '👨‍💼'],
            ['name' => 'Charlie Davis', 'email' => 'charlie@example.com', 'avatar' => '🧑‍🎨'],
            ['name' => 'Diana Prince', 'email' => 'diana@example.com', 'avatar' => '🦸‍♀️'],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(
                ['email' => $u['email']],
                ['name' => $u['name'], 'password' => Hash::make('password')]
            );
        }

        // 2. Create Default Live Poll
        $poll = Poll::firstOrCreate(
            ['question' => 'What is your preferred Real-Time WebSocket stack for Laravel 12?'],
            ['is_active' => true]
        );

        if ($poll->options()->count() === 0) {
            $options = [
                ['option_text' => 'Soketi (Fast, open-source Pusher drop-in in uWebSockets)', 'votes_count' => 12],
                ['option_text' => 'Laravel Reverb (First-party official PHP WebSocket server)', 'votes_count' => 15],
                ['option_text' => 'Pusher Cloud (Managed third-party SaaS service)', 'votes_count' => 4],
                ['option_text' => 'Self-Hosted Redis + Node.js Socket.IO', 'votes_count' => 6],
            ];

            foreach ($options as $opt) {
                $poll->options()->create($opt);
            }
        }
    }
}
