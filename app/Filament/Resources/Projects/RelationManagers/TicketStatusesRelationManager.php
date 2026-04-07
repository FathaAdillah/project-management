<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use App\Models\TicketStatus;
use Filament\Notifications\Notification;

class TicketStatusesRelationManager extends RelationManager
{
    protected static string $relationship = 'ticketStatuses';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->ticket_statuses_count ?? $ownerRecord->ticketStatuses()->count();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                ColorPicker::make('color')
                    ->required()
                    ->default('#3490dc')
                    ->helperText('Select a color for this status'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Determines display order in the project board (lower values appear first)'),
                Toggle::make('is_completed')
                    ->label('Mark as Completed Status')
                    ->helperText('Only one status per project can be marked as completed')
                    ->default(false)
                    ->reactive()
                    ->afterStateUpdated(function ($state, $get, $set, $record) {
                        if ($state) {
                            // Check if another status in this project is already marked as completed
                            $projectId = $this->getOwnerRecord()->id;
                            $existingCompleted = TicketStatus::where('project_id', $projectId)
                                ->where('is_completed', true)
                                ->when($record, fn($query) => $query->where('id', '!=', $record->id))
                                ->first();
                            
                            if ($existingCompleted) {
                                $set('is_completed', false);
                                Notification::make()
                                    ->warning()
                                    ->title('Cannot mark as completed')
                                    ->body("Status '{$existingCompleted->name}' is already marked as completed for this project. Only one status can be marked as completed.")
                                    ->send();
                            }
                        }
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                ColorColumn::make('color'),
                TextColumn::make('sort_order'),
                IconColumn::make('is_completed')
                    ->label('Completed')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $maxOrder = $this->getRelationship()->max('sort_order') ?? -1;
                        $data['sort_order'] = $maxOrder + 1;
                        
                        // Additional validation for is_completed
                        if ($data['is_completed'] ?? false) {
                            $projectId = $this->getOwnerRecord()->id;
                            $existingCompleted = TicketStatus::where('project_id', $projectId)
                                ->where('is_completed', true)
                                ->first();
                            
                            if ($existingCompleted) {
                                $data['is_completed'] = false;
                                Notification::make()
                                    ->warning()
                                    ->title('Cannot mark as completed')
                                    ->body("Status '{$existingCompleted->name}' is already marked as completed for this project.")
                                    ->send();
                            }
                        }
                        
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(function (array $data, Model $record): array {
                        // Additional validation for is_completed on edit
                        if ($data['is_completed'] ?? false) {
                            $projectId = $this->getOwnerRecord()->id;
                            $existingCompleted = TicketStatus::where('project_id', $projectId)
                                ->where('is_completed', true)
                                ->where('id', '!=', $record->id)
                                ->first();
                            
                            if ($existingCompleted) {
                                $data['is_completed'] = false;
                                Notification::make()
                                    ->warning()
                                    ->title('Cannot mark as completed')
                                    ->body("Status '{$existingCompleted->name}' is already marked as completed for this project.")
                                    ->send();
                            }
                        }
                        
                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}