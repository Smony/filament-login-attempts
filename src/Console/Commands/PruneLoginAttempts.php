<?php

namespace Smony\FilamentLoginAttempts\Console\Commands;

use Illuminate\Console\Command;
use Smony\FilamentLoginAttempts\Models\LoginAttempt;

class PruneLoginAttempts extends Command
{
    protected $signature = 'login-attempts:prune';

    protected $description = 'Delete login attempts older than the configured retention period';

    public function handle(): int
    {
        $days = config('filament-login-attempts.retention_days', 30);

        $deleted = LoginAttempt::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Pruned {$deleted} login attempt(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
