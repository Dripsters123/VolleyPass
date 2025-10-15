<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ScoreUpdateRequested extends Notification
{
    use Queueable;

    public $verification; // e.g. a MatchScoreVerification model or array with data

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $verification
     * @return void
     */
    public function __construct($verification)
    {
        $this->verification = $verification;
    }

    /**
     * Channels for delivery. 'database' stores notifications in DB.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Array representation stored in the notifications table.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        // adapt keys to whatever your controller provides
        return [
            'type' => 'score_update_requested',
            'verification_id' => $this->verification->id ?? null,
            'match_id' => $this->verification->match_id ?? ($this->verification['match_id'] ?? null),
            'home_score' => $this->verification->home_score ?? ($this->verification['home_score'] ?? null),
            'away_score' => $this->verification->away_score ?? ($this->verification['away_score'] ?? null),
            'submitted_by' => $this->verification->user_id ?? ($this->verification['user_id'] ?? null),
            'message' => $this->verification->message ?? ($this->verification['message'] ?? 'Jauns rezultāta pieprasījums'),
        ];
    }
}
