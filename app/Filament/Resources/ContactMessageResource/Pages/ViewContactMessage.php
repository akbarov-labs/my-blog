<?php

namespace App\Filament\Resources\ContactMessageResource\Pages;

use App\Filament\Resources\ContactMessageResource;
use Filament\Resources\Pages\ViewRecord;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected function afterFill(): void
    {
        if (blank($this->record->read_at)) {
            $this->record->update(['read_at' => now()]);
        }
    }
}
