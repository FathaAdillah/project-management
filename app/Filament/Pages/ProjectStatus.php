<?php

namespace App\Filament\Pages;

use App\Models\Project;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class ProjectStatus extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.pages.project-status';

    protected static ?string $title = 'Project Status';

    protected static ?string $navigationLabel = 'Project Status';

    protected static string|\UnitEnum|null $navigationGroup = 'Project Management';

    protected static ?int $navigationSort = 5;

    public function getSubheading(): ?string
    {
        return 'Track project progress with S-Curve analysis';
    }

    protected static ?string $slug = 'project-status/{project_id?}';

    public ?Project $selectedProject = null;

    public Collection $projects;

    public ?int $selectedProjectId = null;

    public string $searchProject = '';

    // Metrics
    public float $plannedProgress = 0;
    public float $actualProgress = 0;
    public float $deviation = 0;
    public string $projectStatus = 'Unknown';
    public string $statusColor = 'gray';

    public function mount($project_id = null): void
    {
        if (auth()->user()->hasRole(['super_admin'])) {
            $this->projects = Project::orderByRaw('pinned_date IS NULL')
                ->orderBy('pinned_date', 'desc')
                ->orderBy('name')
                ->get();
        } else {
            $this->projects = auth()->user()->projects()
                ->orderByRaw('pinned_date IS NULL')
                ->orderBy('pinned_date', 'desc')
                ->orderBy('name')
                ->get();
        }

        if ($project_id && $this->projects->contains('id', $project_id)) {
            $this->selectedProjectId = (int) $project_id;
            $this->selectedProject = Project::find($project_id);
            $this->calculateMetrics();
        }
    }

    public function getFilteredProjectsProperty(): Collection
    {
        if (empty($this->searchProject)) {
            return $this->projects;
        }

        return $this->projects->filter(function ($project) {
            return str_contains(strtolower($project->name), strtolower($this->searchProject)) ||
                str_contains(strtolower($project->ticket_prefix ?? ''), strtolower($this->searchProject));
        });
    }

    public function updatedSelectedProjectId($value): void
    {
        if ($value) {
            $this->selectProject($value);
        } else {
            $this->selectedProject = null;
            $this->resetMetrics();

            $url = static::getUrl();
            $this->js("Livewire.navigate('{$url}')");
        }
    }

    public function selectProject(int $projectId): void
    {
        $this->selectedProjectId = $projectId;
        $this->selectedProject = Project::find($projectId);

        if ($this->selectedProject) {
            $this->calculateMetrics();

            $url = static::getUrl(['project_id' => $projectId]);
            $this->js("Livewire.navigate('{$url}')");
        }
    }

    private function calculateMetrics(): void
    {
        if (!$this->selectedProject) {
            $this->resetMetrics();
            return;
        }

        // Calculate Planned Progress (based on time elapsed)
        if ($this->selectedProject->start_date && $this->selectedProject->end_date) {
            $startDate = Carbon::parse($this->selectedProject->start_date);
            $endDate = Carbon::parse($this->selectedProject->end_date);
            $today = Carbon::today();

            $totalDuration = $startDate->diffInDays($endDate);

            if ($totalDuration > 0) {
                if ($today->lt($startDate)) {
                    $this->plannedProgress = 0;
                } elseif ($today->gt($endDate)) {
                    $this->plannedProgress = 100;
                } else {
                    $elapsedDays = $startDate->diffInDays($today);
                    $this->plannedProgress = round(($elapsedDays / $totalDuration) * 100, 2);
                }
            } else {
                $this->plannedProgress = 0;
            }
        } else {
            $this->plannedProgress = 0;
        }

        // Calculate Actual Progress (based on completed tickets)
        $totalTickets = $this->selectedProject->tickets()->count();

        if ($totalTickets > 0) {
            $completedTickets = $this->selectedProject->tickets()
                ->whereHas('status', function ($query) {
                    $query->where('is_completed', true);
                })
                ->count();

            $this->actualProgress = round(($completedTickets / $totalTickets) * 100, 2);
        } else {
            $this->actualProgress = 0;
        }

        // Calculate Deviation
        $this->deviation = round($this->actualProgress - $this->plannedProgress, 2);

        // Determine Project Status
        $this->determineProjectStatus();
    }

    private function determineProjectStatus(): void
    {
        // If deviation is positive or within -5%, project is on time
        if ($this->deviation >= -5) {
            $this->projectStatus = 'On Time';
            $this->statusColor = 'success';
        }
        // If deviation is between -5% and -15%, project is at risk
        elseif ($this->deviation >= -15) {
            $this->projectStatus = 'At Risk';
            $this->statusColor = 'warning';
        }
        // If deviation is less than -15%, project is delayed
        else {
            $this->projectStatus = 'Delayed';
            $this->statusColor = 'danger';
        }

        // Additional check: if project is past end date and not complete
        if ($this->selectedProject->end_date) {
            $endDate = Carbon::parse($this->selectedProject->end_date);
            if (Carbon::today()->gt($endDate) && $this->actualProgress < 100) {
                $this->projectStatus = 'Delayed';
                $this->statusColor = 'danger';
            }
        }
    }

    private function resetMetrics(): void
    {
        $this->plannedProgress = 0;
        $this->actualProgress = 0;
        $this->deviation = 0;
        $this->projectStatus = 'Unknown';
        $this->statusColor = 'gray';
    }

    public function getChartDataProperty(): array
    {
        if (!$this->selectedProject || !$this->selectedProject->start_date || !$this->selectedProject->end_date) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        $startDate = Carbon::parse($this->selectedProject->start_date);
        $endDate = Carbon::parse($this->selectedProject->end_date);
        $totalDays = $startDate->diffInDays($endDate);

        if ($totalDays <= 0) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        // Generate timeline points (every few days for readability)
        $points = min(30, $totalDays); // Max 30 data points
        $interval = max(1, floor($totalDays / $points));

        $labels = [];
        $plannedData = [];
        $actualData = [];
        $deviationData = [];

        $currentDate = $startDate->copy();
        $pointIndex = 0;

        while ($currentDate->lte($endDate) && $pointIndex <= $points) {
            // Label: format tanggal (dd MMM)
            $labels[] = $currentDate->format('d M');

            // Planned Progress: berdasarkan waktu yang berlalu
            $daysPassed = $startDate->diffInDays($currentDate);
            $plannedProgress = ($daysPassed / $totalDays) * 100;
            $plannedData[] = round($plannedProgress, 2);

            // Actual Progress: berdasarkan tickets completed sampai tanggal ini
            $completedCount = $this->selectedProject->tickets()
                ->whereHas('status', function ($query) {
                    $query->where('is_completed', true);
                })
                ->where(function ($query) use ($currentDate) {
                    $query->where('updated_at', '<=', $currentDate->endOfDay())
                        ->orWhereHas('histories', function ($historyQuery) use ($currentDate) {
                            $historyQuery->whereHas('status', function ($statusQuery) {
                                $statusQuery->where('is_completed', true);
                            })
                                ->where('created_at', '<=', $currentDate->endOfDay());
                        });
                })
                ->count();

            $totalTickets = $this->selectedProject->tickets()->count();
            $actualProgress = $totalTickets > 0 ? ($completedCount / $totalTickets) * 100 : 0;
            $actualData[] = round($actualProgress, 2);

            // Deviation: Actual - Planned
            $deviation = $actualProgress - $plannedProgress;
            $deviationData[] = round($deviation, 2);

            // Move to next point
            $currentDate->addDays($interval);
            $pointIndex++;
        }

        // Ensure end date is included
        if (!$currentDate->isSameDay($endDate)) {
            $labels[] = $endDate->format('d M');

            $plannedData[] = 100;

            $completedCount = $this->selectedProject->tickets()
                ->whereHas('status', function ($query) {
                    $query->where('is_completed', true);
                })
                ->count();
            $totalTickets = $this->selectedProject->tickets()->count();
            $actualProgress = $totalTickets > 0 ? ($completedCount / $totalTickets) * 100 : 0;
            $actualData[] = round($actualProgress, 2);

            $deviationData[] = round($actualProgress - 100, 2);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Actual Progress',
                    'data' => $actualData,
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Planned Progress',
                    'data' => $plannedData,
                    'borderColor' => '#9CA3AF',
                    'backgroundColor' => 'rgba(156, 163, 175, 0.1)',
                    'borderWidth' => 2,
                    'borderDash' => [5, 5],
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Deviation',
                    'data' => $deviationData,
                    'borderColor' => '#EF4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
        ];
    }
}
