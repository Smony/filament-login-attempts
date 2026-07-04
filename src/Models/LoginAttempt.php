<?php

namespace Smony\FilamentLoginAttempts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAttempt extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'email',
        'user_id',
        'ip_address',
        'user_agent',
        'successful',
    ];

    protected $casts = [
        'successful' => 'boolean',
    ];

    public function getTable(): string
    {
        return config('filament-login-attempts.table', 'login_attempts');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }
}
