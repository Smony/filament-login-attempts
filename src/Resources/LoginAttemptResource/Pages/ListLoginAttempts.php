<?php

namespace Smony\FilamentLoginAttempts\Resources\LoginAttemptResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Smony\FilamentLoginAttempts\Resources\LoginAttemptResource;

class ListLoginAttempts extends ListRecords
{
    protected static string $resource = LoginAttemptResource::class;
}
