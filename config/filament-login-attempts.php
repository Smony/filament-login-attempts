<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Login attempts table
    |--------------------------------------------------------------------------
    */
    'table' => 'login_attempts',

    /*
    |--------------------------------------------------------------------------
    | Brute-force protection
    |--------------------------------------------------------------------------
    |
    | After `max_attempts` failed logins within `decay_minutes`, the offending
    | key is locked out for `lockout_minutes`. Enforced via Laravel's
    | RateLimiter, the same mechanism used for the default login throttling.
    |
    */
    'max_attempts' => 2,

    'decay_minutes' => 10,

    'lockout_minutes' => 15,

    /*
    |--------------------------------------------------------------------------
    | Lockout key strategy
    |--------------------------------------------------------------------------
    |
    | What identifies a "bad actor" for rate limiting purposes: the IP
    | address, the submitted email, or both combined.
    |
    | Supported: "ip", "email", "ip_and_email"
    |
    */
    'lockout_strategy' => 'ip_and_email',

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | How many days of login attempts to keep. Older records are removed by
    | the `login-attempts:prune` command.
    |
    */
    'retention_days' => 30,
];
