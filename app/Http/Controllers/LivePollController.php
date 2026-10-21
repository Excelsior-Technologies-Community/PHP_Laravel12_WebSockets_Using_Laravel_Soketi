<?php

namespace App\Http\Controllers;

use App\Events\VoteCasted;
use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Http\Request;

class LivePollController extends Controller
{
    /**
     * Display Live Interactive Poll Room
     */
    public function index()
    {
        $poll = Poll::with('options')->first();

        // Calculate statistics
        $totalVotes = $poll ? $poll->options->sum('votes_count') : 0;
        $stats = $this->computeStats($poll, $totalVotes);

        return view('websocket.polls', compact('poll', 'totalVotes', 'stats'));
    }

    /**
     * Cast vote on an option & broadcast in real-time
     */
    public function vote(Request $request)
    {
        $optionId = (int) $request->input('option_id');
        $option = PollOption::with('poll')->findOrFail($optionId);
        $option->increment('votes_count');

        $poll = $option->poll->load('options');
        $totalVotes = $poll->options->sum('votes_count');
        $stats = $this->computeStats($poll, $totalVotes);

        try {
            broadcast(new VoteCasted(
                pollId: $poll->id,
                optionId: $option->id,
                optionsStats: $stats,
                totalVotes: $totalVotes
            ));
        } catch (\Throwable $e) {
            \Log::warning('Poll broadcast failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'poll_id' => $poll->id,
            'option_id' => $option->id,
            'total_votes' => $totalVotes,
            'options' => $stats,
        ]);
    }

    /**
     * Reset vote tallies for demo
     */
    public function reset(Request $request)
    {
        $poll = Poll::first();
        if ($poll) {
            $poll->options()->update(['votes_count' => 0]);
            
            $stats = $this->computeStats($poll->load('options'), 0);
            try {
                broadcast(new VoteCasted(
                    pollId: $poll->id,
                    optionId: 0,
                    optionsStats: $stats,
                    totalVotes: 0
                ));
            } catch (\Throwable $e) {}
        }

        return back()->with('success', 'Poll vote tallies reset to zero.');
    }

    private function computeStats(Poll $poll, int $totalVotes): array
    {
        return $poll->options->map(function ($opt) use ($totalVotes) {
            $percentage = $totalVotes > 0 ? round(($opt->votes_count / $totalVotes) * 100, 1) : 0;
            return [
                'id' => $opt->id,
                'option_text' => $opt->option_text,
                'votes_count' => $opt->votes_count,
                'percentage' => $percentage,
            ];
        })->toArray();
    }
}
