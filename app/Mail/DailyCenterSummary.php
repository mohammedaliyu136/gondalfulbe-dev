<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyCenterSummary extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $centerName,
        public float  $todayLitres,
        public int    $pendingCosts,
        public int    $lowStockCount,
        public string $managerName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Daily Center Summary — ' . $this->centerName . ' — ' . now()->format('d M Y'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.daily_center_summary');
    }
}
