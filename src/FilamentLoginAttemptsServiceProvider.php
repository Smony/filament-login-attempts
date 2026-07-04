<?php

namespace Smony\FilamentLoginAttempts;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Smony\FilamentLoginAttempts\Console\Commands\PruneLoginAttempts;
use Smony\FilamentLoginAttempts\Listeners\CheckLoginRateLimit;
use Smony\FilamentLoginAttempts\Listeners\RecordFailedLoginAttempt;
use Smony\FilamentLoginAttempts\Listeners\RecordSuccessfulLoginAttempt;

class FilamentLoginAttemptsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-login-attempts.php', 'filament-login-attempts');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/filament-login-attempts.php' => config_path('filament-login-attempts.php'),
        ], 'filament-login-attempts-config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_login_attempts_table.php.stub' => database_path(
                'migrations/'.date('Y_m_d_His').'_create_login_attempts_table.php',
            ),
        ], 'filament-login-attempts-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                PruneLoginAttempts::class,
            ]);
        }

        Event::listen(Attempting::class, CheckLoginRateLimit::class);
        Event::listen(Failed::class, RecordFailedLoginAttempt::class);
        Event::listen(Login::class, RecordSuccessfulLoginAttempt::class);
    }
}
