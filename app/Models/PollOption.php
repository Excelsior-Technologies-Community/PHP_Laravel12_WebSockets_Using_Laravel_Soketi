<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollOption extends Model
{
    protected $fillable = ['poll_id', 'option_text', 'votes_count'];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }
}
