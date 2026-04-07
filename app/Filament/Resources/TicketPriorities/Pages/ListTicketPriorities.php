<?php

namespace App\Filament\Resources\TicketPriorities\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\TicketPriorities\TicketPriorityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTicketPriorities extends ListRecords
{
    protected static string $resource = TicketPriorityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
