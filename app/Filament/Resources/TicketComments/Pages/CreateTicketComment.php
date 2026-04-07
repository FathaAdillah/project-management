<?php

namespace App\Filament\Resources\TicketComments\Pages;

use App\Filament\Resources\TicketComments\TicketCommentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketComment extends CreateRecord
{
    protected static string $resource = TicketCommentResource::class;
}
