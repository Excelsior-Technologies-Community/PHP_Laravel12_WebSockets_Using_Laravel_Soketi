<?php

namespace App\Events;

use App\Models\Poll;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteCasted implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $pollId,
        public int $optionId,
        public array $optionsStats,
        public int $totalVotes
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('live-polls')];
    }

    public function broadcastAs(): string
    {
        return 'poll.vote.casted';
    }

    public function broadcastWith(): array
    {
        return [
            'poll_id' => $this->pollId,
            'option_id' => $this->optionId,
            'options' => $this->optionsStats,
            'total_votes' => $this->totalVotes,
            'voted_at' => now()->toIso8601String(),
        ];
    }
}
