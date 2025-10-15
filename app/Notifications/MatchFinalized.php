<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MatchFinalized extends Notification
{
    use Queueable;

    public $match;

    public function __construct($match)
    {
        $this->match = $match;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'match_finalized',
            'match_id' => $this->match->id ?? null,
            'home_score' => $this->match->home_score ?? null,
            'away_score' => $this->match->away_score ?? null,
            'message' => "Mačs pabeigts: {$this->match->home_team_name} vs {$this->match->away_team_name}",
        ];
    }
}
