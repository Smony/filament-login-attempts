<?php

namespace Smony\FilamentLoginAttempts\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Smony\FilamentLoginAttempts\Models\LoginAttempt;
use Smony\FilamentLoginAttempts\Support\LoginRateLimiter;

class FailedLoginsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $failedLast24h = LoginAttempt::query()
            ->where('successful', false)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $lockedOutCount = app(LoginRateLimiter::class)->currentlyLockedOutPairs()->count();

        return [
            Stat::make('Failed attempts (24h)', $failedLast24h)
                ->color($failedLast24h > 0 ? 'danger' : 'success'),

            Stat::make('Currently locked out', $lockedOutCount)
                ->color($lockedOutCount > 0 ? 'warning' : 'success'),
        ];
    }
}
