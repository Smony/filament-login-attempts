<?php

namespace Smony\FilamentLoginAttempts\Listeners;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Validation\ValidationException;
use Smony\FilamentLoginAttempts\Support\LoginRateLimiter;

class CheckLoginRateLimit
{
    public function __construct(protected LoginRateLimiter $rateLimiter) {}

    public function handle(Attempting $event): void
    {
        $email = $event->credentials['email'] ?? null;
        $key = $this->rateLimiter->key($email, request()->ip());

        if (! $this->rateLimiter->tooManyAttempts($key)) {
            return;
        }

        $minutes = $this->rateLimiter->availableInMinutes($key);

        throw ValidationException::withMessages([
            'email' => trans_choice(
                'Too many attempts, try again in :count minute.|Too many attempts, try again in :count minutes.',
                $minutes,
                ['count' => $minutes],
            ),
        ]);
    }
}
