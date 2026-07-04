<?php

namespace Smony\FilamentLoginAttempts;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Smony\FilamentLoginAttempts\Resources\LoginAttemptResource;
use Smony\FilamentLoginAttempts\Widgets\FailedLoginsWidget;

class FilamentLoginAttemptsPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-login-attempts';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                LoginAttemptResource::class,
            ])
            ->widgets([
                FailedLoginsWidget::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
