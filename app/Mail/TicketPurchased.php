<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\VolleyballMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketPurchased extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public VolleyballMatch $match,
        public array $seats
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Jūsu VolleyPass biļete — ' . $this->match->home_team_name . ' vs ' . $this->match->away_team_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-purchased',
        );
    }
}
