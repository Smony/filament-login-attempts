<?php

namespace Smony\FilamentLoginAttempts\Listeners;

use Illuminate\Auth\Events\Login;
use Smony\FilamentLoginAttempts\Models\LoginAttempt;
use Smony\FilamentLoginAttempts\Support\LoginRateLimiter;

class RecordSuccessfulLoginAttempt
{
    public function __construct(protected LoginRateLimiter $rateLimiter) {}

    public function handle(Login $event): void
    {
        $email = $event->user->email ?? null;

        LoginAttempt::query()->create([
            'email' => $email,
            'user_id' => $event->user->getAuthIdentifier(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'successful' => true,
        ]);

        $this->rateLimiter->clear($this->rateLimiter->key($email, request()->ip()));
    }
}
