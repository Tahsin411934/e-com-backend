<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Identity\Models\User;

class VerifyStoreOwnerMail extends Mailable implements ShouldQueue
{
    use SerializesModels;

    public bool $afterCommit = true;

    public function __construct(public User $user, public string $verificationUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verify your AFT SOFT account');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.verify-store-owner', with: [
            'logoUrl' => (string) env('MAIL_LOGO_URL', 'https://aftsoftandlimited.com/aft-navbar-logo-clean.png'),
        ]);
    }
}
