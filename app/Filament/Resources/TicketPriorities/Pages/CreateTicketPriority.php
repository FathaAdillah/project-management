<?php

namespace App\Filament\Resources\TicketPriorities\Pages;

use App\Filament\Resources\TicketPriorities\TicketPriorityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketPriority extends CreateRecord
{
    protected static string $resource = TicketPriorityResource::class;
}
