<?php

namespace App\Filament\Resources\TicketPriorities\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\TicketPriorities\TicketPriorityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicketPriority extends EditRecord
{
    protected static string $resource = TicketPriorityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
