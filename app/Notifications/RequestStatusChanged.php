<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RequestStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int    $requestId,
        public readonly string $newStatus,
        public readonly string $summary,
        public readonly ?string $reason = null
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $statusLabels = [
            'accepted'  => 'apstiprināts',
            'rejected'  => 'noraidīts',
            'reviewing' => 'tiek izskatīts',
        ];

        $label   = $statusLabels[$this->newStatus] ?? $this->newStatus;
        $message = "Jūsu pieprasījums \"{$this->summary}\" ir {$label}.";
        if ($this->reason) {
            $message .= " Iemesls: {$this->reason}";
        }

        return [
            'type'       => 'request_status_changed',
            'request_id' => $this->requestId,
            'new_status' => $this->newStatus,
            'summary'    => $this->summary,
            'reason'     => $this->reason,
            'message'    => $message,
            'link'       => route('match_requests.view', $this->requestId),
        ];
    }
}
