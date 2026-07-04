<?php

namespace Smony\FilamentLoginAttempts\Events;

class LoginLockedOut
{
    public function __construct(
        public readonly string $key,
        public readonly ?string $email,
        public readonly ?string $ipAddress,
        public readonly int $lockedForMinutes,
    ) {}
}
