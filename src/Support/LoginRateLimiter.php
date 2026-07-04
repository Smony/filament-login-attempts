<?php

namespace Smony\FilamentLoginAttempts\Support;

use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Collection;
use Smony\FilamentLoginAttempts\Models\LoginAttempt;

class LoginRateLimiter
{
    public function __construct(protected RateLimiter $limiter) {}

    public function key(?string $email, ?string $ip): string
    {
        return match (config('filament-login-attempts.lockout_strategy', 'ip_and_email')) {
            'email' => 'login:email:'.mb_strtolower((string) $email),
            'ip' => 'login:ip:'.(string) $ip,
            default => 'login:ip_email:'.(string) $ip.'|'.mb_strtolower((string) $email),
        };
    }

    public function tooManyAttempts(string $key): bool
    {
        return $this->limiter->tooManyAttempts($key, config('filament-login-attempts.max_attempts', 5));
    }

    /**
     * Registers a failed attempt against the key. Returns true the moment
     * this attempt is the one that crosses the threshold and locks the key.
     */
    public function hit(string $key): bool
    {
        $maxAttempts = config('filament-login-attempts.max_attempts', 5);

        $hits = $this->limiter->hit($key, config('filament-login-attempts.decay_minutes', 10) * 60);

        if ($hits < $maxAttempts) {
            return false;
        }

        // Once the threshold is crossed, re-arm the key's expiry using the
        // (typically longer) lockout duration instead of the decay window.
        $this->limiter->hit($key, config('filament-login-attempts.lockout_minutes', 15) * 60);

        return $hits === $maxAttempts;
    }

    public function clear(string $key): void
    {
        $this->limiter->clear($key);
    }

    public function availableInMinutes(string $key): int
    {
        return (int) ceil($this->limiter->availableIn($key) / 60);
    }

    public function isLockedOut(?string $email, ?string $ip): bool
    {
        return $this->tooManyAttempts($this->key($email, $ip));
    }

    /**
     * Minutes left before the key unlocks, or null if it isn't locked out.
     */
    public function remainingMinutes(?string $email, ?string $ip): ?int
    {
        $key = $this->key($email, $ip);

        return $this->tooManyAttempts($key) ? $this->availableInMinutes($key) : null;
    }

    /**
     * Distinct (email, ip_address) pairs from recent failed attempts whose
     * key is currently locked out.
     *
     * @return Collection<int, LoginAttempt>
     */
    public function currentlyLockedOutPairs(): Collection
    {
        return LoginAttempt::query()
            ->where('successful', false)
            ->where('created_at', '>=', now()->subMinutes(
                config('filament-login-attempts.decay_minutes', 10)
                + config('filament-login-attempts.lockout_minutes', 15),
            ))
            ->select('email', 'ip_address')
            ->distinct()
            ->get()
            ->filter(fn (LoginAttempt $pair) => $this->tooManyAttempts(
                $this->key($pair->email, $pair->ip_address),
            ))
            ->values();
    }
}
