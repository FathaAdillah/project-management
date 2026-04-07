<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Actions;
use Exception;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Radio;
use App\Models\Epic;
use App\Models\TicketStatus;
use App\Models\TicketPriority;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Imports\TicketsImport;
use App\Exports\TicketTemplateExport;
use Filament\Forms\Components\FileUpload;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class TicketsRelationManager extends RelationManager
{
    protected static string $relationship = 'tickets';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->tickets_count ?? $ownerRecord->tickets()->count();
    }

    public function form(Schema $schema): Schema
    {
        $projectId = $this->getOwnerRecord()->id;

        $defaultStatus = TicketStatus::where('project_id', $projectId)->first();
        $defaultStatusId = $defaultStatus ? $defaultStatus->id : null;

        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Ticket Name'),
                
                Select::make('ticket_status_id')
                    ->label('Status')
                    ->options(function () use ($projectId) {
                        return TicketStatus::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->default($defaultStatusId)
                    ->required()
                    ->searchable(),
                
                Select::make('epic_id')
                    ->label('Epic')
                    ->options(function () use ($projectId) {
                        return Epic::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->nullable(),
                
                // UPDATED: Multi-user assignment
                Select::make('assignees')
                    ->label('Assignees')
                    ->multiple()
                    ->relationship(
                        name: 'assignees',
                        titleAttribute: 'name',
                        modifyQueryUsing: function ($query) {
                            $projectId = $this->getOwnerRecord()->id;
                            // Only show project members
                            return $query->whereHas('projects', function ($query) use ($projectId) {
                                $query->where('projects.id', $projectId);
                            });
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->default(function ($record) {
                        if ($record && $record->exists) {
                            return $record->assignees->pluck('id')->toArray();
                        }
                        
                        // Auto-assign current user if they're a project member
                        $project = $this->getOwnerRecord();
                        $isCurrentUserMember = $project->members()->where('users.id', auth()->id())->exists();
                        
                        return $isCurrentUserMember ? [auth()->id()] : [];
                    })
                    ->helperText('Select multiple users to assign this ticket to. Only project members can be assigned.'),
                
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->nullable(),
                
                DatePicker::make('due_date')
                    ->label('Due Date')
                    ->nullable()
                    ->afterOrEqual('start_date'),
                
                RichEditor::make('description')
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('attachments')
                    ->fileAttachmentsVisibility('public')
                    ->nullable(),

                // Show created by in edit mode
                Select::make('created_by')
                    ->label('Created By')
                    ->relationship('creator', 'name')
                    ->disabled()
                    ->hiddenOn('create'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('uuid')
                    ->label('Ticket ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('status.name')
                    ->badge()
                    ->color(fn ($record) => match ($record->status?->name) {
                        'To Do' => 'warning',
                        'In Progress' => 'info',
                        'Review' => 'primary',
                        'Done' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                
                TextColumn::make('epic.name')
                    ->label('Epic')
                    ->badge()
                    ->color('warning')
                    ->placeholder('No Epic')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('assignees.name')
                    ->label('Assignees')
                    ->badge()
                    ->separator(',')
                    ->expandableLimitedList()
                    ->searchable(),
                
                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                
                TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('ticket_status_id')
                    ->label('Status')
                    ->options(function () {
                        $projectId = $this->getOwnerRecord()->id;

                        return TicketStatus::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
                
                // UPDATED: Filter by assignees
                SelectFilter::make('assignees')
                    ->label('Assignee')
                    ->relationship('assignees', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                
                // Filter by creator
                SelectFilter::make('created_by')
                    ->label('Created By')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),
                
                // Filter by epic
                SelectFilter::make('epic_id')
                    ->label('Epic')
                    ->options(function () {
                        $projectId = $this->getOwnerRecord()->id;
                        return Epic::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        // Set project_id and created_by
                        $data['project_id'] = $this->getOwnerRecord()->id;
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
                
                // NEW: Import from Excel action
                Action::make('import_tickets')
                    ->label('Import from Excel')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->color('success')
                    ->schema([
                        Section::make('Import Tickets from Excel')
                            ->description('Upload an Excel file to import tickets to this project. You can download the template below.')
                            ->schema([
                                Actions::make([
                                    Action::make('download_template')
                                        ->label('Download Import Template')
                                        ->icon('heroicon-m-arrow-down-tray')
                                        ->color('gray')
                                        ->action(function (RelationManager $livewire) {
                                            $project = $livewire->getOwnerRecord();
                                            $filename = 'ticket-import-template-' . str($project->name)->slug() . '.xlsx';
                                            
                                            return Excel::download(
                                                new TicketTemplateExport($project),
                                                $filename
                                            );
                                        })
                                ])->fullWidth(),
                                
                                FileUpload::make('excel_file')
                                    ->label('Excel File')
                                    ->helperText('Upload the Excel file with ticket data. Make sure to use the template format above.')
                                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                                    ->maxSize(5120) // 5MB
                                    ->required()
                                    ->disk('local')
                                    ->directory('temp-imports')
                                    ->visibility('private'),
                            ]),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $project = $livewire->getOwnerRecord();
                        $filePath = Storage::disk('local')->path($data['excel_file']);
                        
                        try {
                            $import = new TicketsImport($project);
                            Excel::import($import, $filePath);
                            
                            $importedCount = $import->getImportedCount();
                            $errors = $import->errors();
                            $failures = $import->failures();
                            
                            // Clean up uploaded file
                            Storage::disk('local')->delete($data['excel_file']);
                            
                            if ($importedCount > 0) {
                                $message = "Successfully imported {$importedCount} ticket(s) to project '{$project->name}'.";
                                
                                if (count($errors) > 0 || count($failures) > 0) {
                                    $message .= " Some rows had errors and were skipped.";
                                }
                                
                                Notification::make()
                                    ->title('Import Completed')
                                    ->body($message)
                                    ->success()
                                    ->send();
                            } else {
                                // Get actual errors and failures
                                $importErrors = $import->errors();
                                $importFailures = $import->failures();
                                
                                $errorMessage = "No tickets were imported.";
                                
                                // Show actual validation failures if they exist
                                if (!empty($importFailures)) {
                                    $errorMessage .= "\n\n**Validation Errors:**";
                                    foreach ($importFailures as $failure) {
                                        $row = $failure->row();
                                        $errors = implode(', ', $failure->errors());
                                        $errorMessage .= "\n• Row {$row}: {$errors}";
                                    }
                                }
                                
                                // Show actual processing errors if they exist
                                if (!empty($importErrors)) {
                                    $errorMessage .= "\n\n**Processing Errors:**";
                                    foreach ($importErrors as $error) {
                                        $errorMessage .= "\n• {$error}";
                                    }
                                }
                                
                                // Only show generic help if no specific errors are available
                                if (empty($importFailures) && empty($importErrors)) {
                                    $errorMessage .= "\n\n**Possible causes:**";
                                    $errorMessage .= "\n• File contains only headers or sample data";
                                    $errorMessage .= "\n• All rows were skipped due to validation issues";
                                    $errorMessage .= "\n• File format is incorrect";
                                    $errorMessage .= "\n\nPlease check your file and try again.";
                                }
                                
                                Notification::make()
                                    ->title('Import Failed')
                                    ->body($errorMessage)
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            }
                        } catch (Exception $e) {
                            // Clean up uploaded file on error
                            Storage::disk('local')->delete($data['excel_file']);
                            
                            Notification::make()
                                ->title('Import Error')
                                ->body('An error occurred during import: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                    BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Select::make('ticket_status_id')
                                ->label('Status')
                                ->options(function (RelationManager $livewire) {
                                    $projectId = $livewire->getOwnerRecord()->id;

                                    return TicketStatus::where('project_id', $projectId)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->required(),
                        ])
                        ->action(function (array $data, Collection $records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'ticket_status_id' => $data['ticket_status_id'],
                                ]);
                            }
                            
                            Notification::make()
                                ->success()
                                ->title('Status updated')
                                ->body(count($records) . ' tickets have been updated.')
                                ->send();
                        }),
                    
                    // NEW: Bulk assign users
                    BulkAction::make('assignUsers')
                        ->label('Assign Users')
                        ->icon('heroicon-o-user-plus')
                        ->form([
                            Select::make('assignees')
                                ->label('Assignees')
                                ->multiple()
                                ->options(function (RelationManager $livewire) {
                                    return $livewire->getOwnerRecord()
                                        ->members()
                                        ->pluck('name', 'users.id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->required(),
                            
                            Radio::make('assignment_mode')
                                ->label('Assignment Mode')
                                ->options([
                                    'replace' => 'Replace existing assignees',
                                    'add' => 'Add to existing assignees',
                                ])
                                ->default('add')
                                ->required(),
                        ])
                        ->action(function (array $data, Collection $records) {
                            foreach ($records as $record) {
                                if ($data['assignment_mode'] === 'replace') {
                                    $record->assignees()->sync($data['assignees']);
                                } else {
                                    $record->assignees()->syncWithoutDetaching($data['assignees']);
                                }
                            }
                            
                            Notification::make()
                                ->success()
                                ->title('Users assigned')
                                ->body(count($records) . ' tickets have been updated with new assignees.')
                                ->send();
                        }),
                    BulkAction::make('updatePriority')
                        ->label('Update Priority')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('priority_id')
                                ->label('Priority')
                                ->options(TicketPriority::pluck('name', 'id')->toArray())
                                ->nullable(),
                        ])
                        ->action(function (array $data, Collection $records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'priority_id' => $data['priority_id'],
                                ]);
                            }
                        }),
                    
                    BulkAction::make('assignToEpic')
                        ->label('Assign to Epic')
                        ->icon('heroicon-o-bookmark')
                        ->form([
                            Select::make('epic_id')
                                ->label('Epic')
                                ->options(function (RelationManager $livewire) {
                                    $projectId = $livewire->getOwnerRecord()->id;
                                    return Epic::where('project_id', $projectId)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->helperText('Select an epic to assign the selected tickets to. Leave empty to remove epic assignment.'),
                        ])
                        ->action(function (array $data, Collection $records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'epic_id' => $data['epic_id'],
                                ]);
                            }
                            
                            $epicName = $data['epic_id'] 
                                ? Epic::find($data['epic_id'])->name 
                                : 'No Epic';
                            
                            Notification::make()
                                ->success()
                                ->title('Epic assignment updated')
                                ->body(count($records) . ' tickets have been assigned to: ' . $epicName)
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}