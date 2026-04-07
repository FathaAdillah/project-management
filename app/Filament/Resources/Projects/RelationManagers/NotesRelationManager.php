<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Filters\Filter;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Models\ProjectNote;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\StaticAction;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $title = 'Project Notes';

    protected static ?string $modelLabel = 'Note';

    protected static ?string $pluralModelLabel = 'Notes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                
                DatePicker::make('note_date')
                    ->label('Note Date')
                    ->default(now())
                    ->required(),
                
                RichEditor::make('content')
                    ->required()
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('attachments')
                    ->fileAttachmentsVisibility('public')
                    ->toolbarButtons([
                        'attachFiles',
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])
                    ->helperText('Write your meeting summary or project notes here with rich formatting.'),
                
                Hidden::make('created_by')
                    ->default(auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                
                TextColumn::make('note_date')
                    ->date('M d, Y')
                    ->sortable(),
                
                TextColumn::make('creator.name')
                    ->label('Created by')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('recent')
                    ->query(fn ($query) => $query->where('created_at', '>=', now()->subDays(30)))
                    ->label('Recent (30 days)'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Add Note')
                    ->modalWidth('2xl')
                    ->closeModalByClickingAway(false)
                    ,
            ])
            ->recordActions([
                ViewAction::make()
                    ->closeModalByClickingAway(false),
                EditAction::make()
                    ->closeModalByClickingAway(false),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('note_date', 'desc')
            ->emptyStateHeading('No project notes yet')
            ->emptyStateDescription('Start documenting your project meetings and important notes.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}