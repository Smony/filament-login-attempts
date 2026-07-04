<?php

namespace Smony\FilamentLoginAttempts\Listeners;

use Illuminate\Auth\Events\Failed;
use Smony\FilamentLoginAttempts\Events\LoginLockedOut;
use Smony\FilamentLoginAttempts\Models\LoginAttempt;
use Smony\FilamentLoginAttempts\Support\LoginRateLimiter;

class RecordFailedLoginAttempt
{
    public function __construct(protected LoginRateLimiter $rateLimiter) {}

    public function handle(Failed $event): void
    {
        $email = $event->credentials['email'] ?? null;
        $ip = request()->ip();

        LoginAttempt::query()->create([
            'email' => $email,
            'user_id' => $event->user?->getAuthIdentifier(),
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            'successful' => false,
        ]);

        $key = $this->rateLimiter->key($email, $ip);

        if ($this->rateLimiter->hit($key)) {
            event(new LoginLockedOut($key, $email, $ip, config('filament-login-attempts.lockout_minutes', 15)));
        }
    }
}
