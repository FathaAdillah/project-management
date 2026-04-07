<?php

namespace App\Filament\Resources\Projects\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}