<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketsRelationManager extends RelationManager
{
    protected static string $relationship = 'tickets';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('Ticket ID')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('name')
                    ->label('Ticket Name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => match ($record->status?->name) {
                        'To Do' => 'warning',
                        'In Progress' => 'info',
                        'Review' => 'primary',
                        'Done' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('project')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->relationship('status', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('due_date')
                    ->schema([
                        DatePicker::make('due_from'),
                        DatePicker::make('due_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([

            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.tickets.edit', $record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}
