<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketHistory;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class ProjectStatus extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected string $view = 'filament.pages.project-status';
    protected static ?string $title = 'Project Status';
    protected static ?string $navigationLabel = 'Project Status';
    protected static string | \UnitEnum | null $navigationGroup = 'Project Management';
    protected static ?int $navigationSort = 5;

    public ?int $selectedProjectId = null;
    public ?Project $selectedProject = null;

    // Stats
    public string $projectStatus = '-';
    public string $projectStatusColor = 'gray';
    public float $plannedProgress = 0;
    public float $actualProgress = 0;
    public float $deviation = 0;

    // Chart Data
    public array $chartData = [];

    public function mount(): void
    {
        // No auto-selection; the user must choose a project.
        $this->selectedProjectId = null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('selectedProjectId')
                    ->label('Select Project')
                    ->placeholder('Pilih Project')
                    ->options(function () {
                        if (auth()->user()->hasRole(['super_admin'])) {
                            return Project::all()->pluck('name', 'id');
                        }
                        return auth()->user()->projects()->select('projects.id', 'projects.name')->pluck('name', 'id');
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->updatedSelectedProjectId($state))
            ]);
    }

    public function updatedSelectedProjectId($value): void
    {
        if (!$value) {
            $this->resetStats();
            return;
        }

        $this->selectedProject = Project::find($value);
        if ($this->selectedProject) {
            $this->calculateStats();
            $this->generateChartData();
        }
    }

    protected function resetStats(): void
    {
        $this->selectedProject = null;
        $this->projectStatus = '-';
        $this->projectStatusColor = 'gray';
        $this->plannedProgress = 0;
        $this->actualProgress = 0;
        $this->deviation = 0;
        $this->chartData = [];
    }

    protected function calculateStats(): void
    {
        if (!$this->selectedProject || !$this->selectedProject->start_date || !$this->selectedProject->end_date) {
            $this->plannedProgress = 0;
            $this->actualProgress = $this->selectedProject ? $this->selectedProject->progress_percentage : 0;
            $this->deviation = 0;
            $this->projectStatus = 'Missing Dates';
            $this->projectStatusColor = 'gray';
            return;
        }

        $start = $this->selectedProject->start_date;
        $end = $this->selectedProject->end_date;
        $today = Carbon::today();

        // Calculate Planned Progress (Linear)
        $totalDuration = $start->diffInDays($end);
        $elapsed = $start->diffInDays($today);

        if ($today->lt($start)) {
            $this->plannedProgress = 0;
        } elseif ($today->gt($end)) {
            $this->plannedProgress = 100;
        } else {
            $this->plannedProgress = $totalDuration > 0 ? round(($elapsed / $totalDuration) * 100, 1) : 100;
        }

        // Calculate Actual Progress
        $this->actualProgress = $this->selectedProject->progress_percentage;

        // Calculate Deviation
        $this->deviation = round($this->actualProgress - $this->plannedProgress, 1);

        // Determine Status
        if ($this->deviation >= -10) {
            $this->projectStatus = 'On Track';
            $this->projectStatusColor = 'success';
        } elseif ($this->deviation >= -20) {
            $this->projectStatus = 'At Risk';
            $this->projectStatusColor = 'warning';
        } else {
            $this->projectStatus = 'Delayed';
            $this->projectStatusColor = 'danger';
        }
    }

    protected function generateChartData(): void
    {
        if (!$this->selectedProject || !$this->selectedProject->start_date || !$this->selectedProject->end_date) {
            $this->chartData = [];
            return;
        }

        $start = $this->selectedProject->start_date;
        $end = $this->selectedProject->end_date;
        $today = Carbon::today();
        
        // If project hasn't started, show empty chart
        if ($start->gt($today)) {
             $this->chartData = [];
             return;
        }

        $labels = [];
        $plannedData = [];
        $actualData = [];
        $deviationData = [];

        $totalTickets = $this->selectedProject->tickets()->count();
        if ($totalTickets === 0) $totalTickets = 1; // Avoid division by zero

        // Get completion history
        // We look for tickets that are currently completed
        $completedTicketIds = $this->selectedProject->tickets()
            ->whereHas('status', fn($q) => $q->where('is_completed', true))
            ->pluck('id');

        // Ideally we use TicketHistory to find WHEN they were completed.
        // For now, let's use a simplified approach:
        // Get all history entries where status became 'completed' for these tickets
        $completions = TicketHistory::whereIn('ticket_id', $completedTicketIds)
            ->whereHas('status', fn($q) => $q->where('is_completed', true))
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        // Generate daily data points
        $currentDate = $start->copy();
        $endDateForChart = $today->gt($end) ? $end : $today;
        
        // If project is old, we might have too many points. Limit to ~30-50 points?
        // For now, let's do daily.
        
        $cumulativeCompleted = 0;
        $totalDuration = $start->diffInDays($end);

        while ($currentDate->lte($endDateForChart)) {
            $dateStr = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('d M');

            // Planned
            $elapsed = $start->diffInDays($currentDate);
            $planned = $totalDuration > 0 ? ($elapsed / $totalDuration) * 100 : 100;
            if ($planned > 100) $planned = 100;
            $plannedData[] = round($planned, 1);

            // Actual
            if (isset($completions[$dateStr])) {
                $cumulativeCompleted += $completions[$dateStr];
            }
            // Also check if any tickets were completed on this day but don't have history (fallback to updated_at if needed, but let's stick to history for now)
            
            // Note: This logic assumes tickets are NOT un-completed. 
            // A more robust logic would track net change.
            
            $actual = ($cumulativeCompleted / $totalTickets) * 100;
            $actualData[] = round($actual, 1);

            // Deviation
            $deviationData[] = round($actual - $planned, 1);

            $currentDate->addDay();
        }

        $this->chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Actual Progress',
                    'data' => $actualData,
                    'borderColor' => '#3b82f6', // Blue
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Planned Progress',
                    'data' => $plannedData,
                    'borderColor' => '#9ca3af', // Gray
                    'borderDash' => [5, 5],
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Deviation',
                    'data' => $deviationData,
                    'borderColor' => '#ef4444', // Red
                    'hidden' => false, // Show deviation line
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
        ];

        $this->dispatch('chart-updated', $this->chartData);
    }
}
