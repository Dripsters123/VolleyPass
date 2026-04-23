<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RequestSubmitted extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $requestType,
        public readonly int    $requestId,
        public readonly string $submitterName,
        public readonly string $summary
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'           => 'request_submitted',
            'request_type'   => $this->requestType,
            'request_id'     => $this->requestId,
            'submitter_name' => $this->submitterName,
            'summary'        => $this->summary,
            'message'        => "Jauns pieprasījums no {$this->submitterName}: {$this->summary}",
            'link'           => route('admin.match_requests.show', $this->requestId),
        ];
    }
}
