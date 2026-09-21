<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;
use Modules\Store\Models\StoreDomain;
use Modules\Store\Support\StoreDomainResolver;

class StoreRegisteredMail extends Mailable implements ShouldQueue
{
    use SerializesModels;

    public bool $afterCommit = true;

    public function __construct(
        public User $user,
        public Store $store,
        public StoreDomain $storeDomain,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your AFT SOFT store is ready');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.store-registered',
            with: [
                'storeUrl' => StoreDomainResolver::urlForDomain($this->storeDomain->domain),
                'loginUrl' => rtrim((string) config('app.admin_url', config('app.url')), '/').'/login',
                'logoUrl' => (string) env('MAIL_LOGO_URL', 'https://aftsoftandlimited.com/aft-navbar-logo-clean.png'),
            ],
        );
    }
}
