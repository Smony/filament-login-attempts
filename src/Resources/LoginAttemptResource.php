<?php

namespace Smony\FilamentLoginAttempts\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Agent\Agent;
use Smony\FilamentLoginAttempts\Models\LoginAttempt;
use Smony\FilamentLoginAttempts\Resources\LoginAttemptResource\Pages\ListLoginAttempts;
use Smony\FilamentLoginAttempts\Support\LoginRateLimiter;
use UnitEnum;

class LoginAttemptResource extends Resource
{
    protected static ?string $model = LoginAttempt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?string $navigationLabel = 'Login Attempts';

    protected static string|UnitEnum|null $navigationGroup = 'Security';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->default('Guest'),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP address')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user_agent')
                    ->label('Device')
                    ->formatStateUsing(function (?string $state): string {
                        if (blank($state)) {
                            return 'Unknown';
                        }

                        $agent = new Agent;
                        $agent->setUserAgent($state);

                        return sprintf(
                            '%s on %s',
                            $agent->browser() ?: 'Unknown browser',
                            $agent->platform() ?: 'Unknown platform',
                        );
                    })
                    ->wrap(),

                Tables\Columns\IconColumn::make('successful')
                    ->label('Success')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->formatStateUsing(fn ($state) => $state?->diffForHumans())
                    ->sortable(),

                Tables\Columns\TextColumn::make('locked_until')
                    ->label('Unlocks in')
                    ->state(function (LoginAttempt $record): ?string {
                        $minutes = app(LoginRateLimiter::class)->remainingMinutes($record->email, $record->ip_address);

                        return $minutes === null ? null : trans_choice(':count minute|:count minutes', $minutes, ['count' => $minutes]);
                    })
                    ->placeholder('—')
                    ->badge()
                    ->color('warning'),
            ])
            ->filters([
                Tables\Filters\Filter::make('only_failed')
                    ->label('Only failed')
                    ->query(fn (Builder $query) => $query->where('successful', false)),

                Tables\Filters\Filter::make('locked_out')
                    ->label('Currently locked out')
                    ->query(function (Builder $query): Builder {
                        $lockedPairs = app(LoginRateLimiter::class)->currentlyLockedOutPairs();

                        if ($lockedPairs->isEmpty()) {
                            return $query->whereRaw('1 = 0');
                        }

                        return $query->where(function (Builder $query) use ($lockedPairs) {
                            foreach ($lockedPairs as $pair) {
                                $query->orWhere(function (Builder $query) use ($pair) {
                                    $query->where('email', $pair->email)
                                        ->where('ip_address', $pair->ip_address);
                                });
                            }
                        });
                    }),
            ])
            ->recordActions([
                Action::make('unlock')
                    ->label('Unlock now')
                    ->icon('heroicon-o-lock-open')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (LoginAttempt $record): bool => app(LoginRateLimiter::class)
                        ->isLockedOut($record->email, $record->ip_address))
                    ->action(function (LoginAttempt $record): void {
                        $rateLimiter = app(LoginRateLimiter::class);

                        $rateLimiter->clear($rateLimiter->key($record->email, $record->ip_address));
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoginAttempts::route('/'),
        ];
    }
}
