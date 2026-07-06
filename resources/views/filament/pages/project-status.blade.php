<x-filament-panels::page>
    {{-- Load Chart.js Library --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>

    {{-- Project Selector --}}
    @if(!$selectedProject)
        <div class="mb-6">
            <x-filament::section>
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Select Project
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Choose a project to view its status and S-Curve analysis
                    </p>
                </div>

                {{-- Search Bar --}}
                <div class="mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="searchProject"
                            placeholder="Search projects by name or prefix..."
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        @if($searchProject)
                            <button wire:click="$set('searchProject', '')"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                @if($projects->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-gray-500 dark:text-gray-400">
                        <h3 class="text-base font-medium text-gray-900 dark:text-white mb-1">No Projects Available</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">You don't have access to any projects yet.</p>
                    </div>
                @elseif($this->filteredProjects->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mb-3 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="text-base font-medium text-gray-900 dark:text-white mb-1">No Projects Found</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Try adjusting your search terms</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                        @foreach($this->filteredProjects as $project)
                            <button wire:click="selectProject({{ $project->id }})"
                                class="relative p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:shadow-md transition-all text-left overflow-hidden"
                                style="border-left: 4px solid {{ $project->color ?? '#6B7280' }};">
                                {{-- Pin Icon Badge --}}
                                @if($project->is_pinned)
                                    <div class="absolute top-2 right-2">
                                        <div class="flex items-center justify-center w-6 h-6 rounded-full shadow-sm"
                                            style="background-color: {{ $project->color ?? '#6B7280' }};" title="Pinned Project">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24"
                                                fill="currentColor">
                                                <path
                                                    d="M16 9V4h1c.55 0 1-.45 1-1s-.45-1-1-1H7c-.55 0-1 .45-1 1s.45 1 1 1h1v5c0 1.66-1.34 3-3 3v2h5.97v7l1 1 1-1v-7H19v-2c-1.66 0-3-1.34-3-3z" />
                                            </svg>
                                        </div>
                                    </div>
                                @endif

                                {{-- Project Prefix Badge --}}
                                @if($project->ticket_prefix)
                                    @php
                                        $color = $project->color ?? '#6B7280';
                                        $r = hexdec(substr($color, 1, 2));
                                        $g = hexdec(substr($color, 3, 2));
                                        $b = hexdec(substr($color, 5, 2));
                                        $bgColor = "rgba($r, $g, $b, 0.1)";
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium mb-2"
                                        style="background-color: {{ $bgColor }}; color: {{ $project->color }};">
                                        {{ $project->ticket_prefix }}
                                    </span>
                                @endif

                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ $project->name }}
                                </h3>

                                @if($project->description)
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-3 prose prose-sm max-w-none dark:prose-invert">
                                        {!! $project->description !!}
                                    </div>
                                @endif

                                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <span>
                                        {{ $project->tickets()->count() }} tickets
                                    </span>
                                    @if($project->start_date && $project->end_date)
                                        <span>
                                            {{ \Carbon\Carbon::parse($project->start_date)->format('M d') }} -
                                            {{ \Carbon\Carbon::parse($project->end_date)->format('M d, Y') }}
                                        </span>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        </div>
    @else
        {{-- Project Status Dashboard --}}
        <div class="mb-4">
            <x-filament::section>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button wire:click="$set('selectedProjectId', null)"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="Back to project list">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </button>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $selectedProject->name }}
                            </h2>
                            @if($selectedProject->description)
                                <div
                                    class="text-sm text-gray-500 dark:text-gray-400 prose prose-sm max-w-none dark:prose-invert">
                                    {!! $selectedProject->description !!}
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($selectedProject->ticket_prefix)
                        @php
                            $color = $selectedProject->color ?? '#6B7280';
                            $r = hexdec(substr($color, 1, 2));
                            $g = hexdec(substr($color, 3, 2));
                            $b = hexdec(substr($color, 5, 2));
                            $bgColor = "rgba($r, $g, $b, 0.1)";
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium"
                            style="background-color: {{ $bgColor }}; color: {{ $selectedProject->color }};">
                            {{ $selectedProject->ticket_prefix }}
                        </span>
                    @endif
                </div>
            </x-filament::section>
        </div>

        {{-- Status Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            {{-- Planned Progress Card --}}
            <div
                class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Planned Progress</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ number_format($plannedProgress, 1) }}%
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Based on timeline
                        </p>
                    </div>
                    <div
                        class="w-10 h-10 bg-blue-200 dark:bg-blue-700 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Actual Progress Card --}}
            <div
                class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Actual Progress</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ number_format($actualProgress, 1) }}%
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Completed tickets
                        </p>
                    </div>
                    <div
                        class="w-10 h-10 bg-green-200 dark:bg-green-700 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Deviation Card --}}
            <div
                class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Deviation</p>
                        <p
                            class="text-2xl font-bold {{ $deviation >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $deviation >= 0 ? '+' : '' }}{{ number_format($deviation, 1) }}%
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ abs($deviation) < 5 ? 'Minimal variance' : ($deviation >= 0 ? 'Ahead of schedule' : 'Behind schedule') }}
                        </p>
                    </div>
                    <div
                        class="w-10 h-10 bg-purple-200 dark:bg-purple-700 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Project Status Card --}}
            <div
                class="bg-gradient-to-br rounded-lg p-4 border border-gray-200 dark:border-gray-700
                                {{ $statusColor === 'success' ? 'from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-800/20' : '' }}
                                {{ $statusColor === 'warning' ? 'from-yellow-50 to-orange-100 dark:from-yellow-900/20 dark:to-orange-800/20' : '' }}
                                {{ $statusColor === 'danger' ? 'from-red-50 to-rose-100 dark:from-red-900/20 dark:to-rose-800/20' : '' }}
                                {{ $statusColor === 'gray' ? 'from-gray-50 to-gray-100 dark:from-gray-900/20 dark:to-gray-800/20' : '' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Project Status</p>
                        <p class="text-2xl font-bold
                                            {{ $statusColor === 'success' ? 'text-green-600 dark:text-green-400' : '' }}
                                            {{ $statusColor === 'warning' ? 'text-orange-600 dark:text-orange-400' : '' }}
                                            {{ $statusColor === 'danger' ? 'text-red-600 dark:text-red-400' : '' }}
                                            {{ $statusColor === 'gray' ? 'text-gray-600 dark:text-gray-400' : '' }}">
                            {{ $projectStatus }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            @if($selectedProject->end_date)
                                {{ $selectedProject->remaining_days }} days remaining
                            @else
                                No deadline set
                            @endif
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                                        {{ $statusColor === 'success' ? 'bg-green-200 dark:bg-green-700' : '' }}
                                        {{ $statusColor === 'warning' ? 'bg-orange-200 dark:bg-orange-700' : '' }}
                                        {{ $statusColor === 'danger' ? 'bg-red-200 dark:bg-red-700' : '' }}
                                        {{ $statusColor === 'gray' ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
                        @if($statusColor === 'success')
                            <svg class="w-5 h-5 text-green-600 dark:text-green-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @elseif($statusColor === 'warning')
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        @elseif($statusColor === 'danger')
                            <svg class="w-5 h-5 text-red-600 dark:text-red-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                                </path>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- S-Curve Analysis Chart --}}
        <x-filament::section>
            <div class="mb-4">
                <h3 class="text-lg font-semibold">
                    S-Curve Analysis
                </h3>
                <p class="text-sm text-gray-500">
                    Comparison of planned vs actual progress over time
                </p>
            </div>

            <div id="sCurveChartWrapper" style="height:450px; width:100%;" wire:ignore>
                <canvas id="sCurveChart" style="width:100%; height:100%; display:block;"></canvas>
            </div>

            <script type="application/json" id="chart-data">
                        {!! json_encode($this->chartData) !!}
                    </script>
        </x-filament::section>

        @push('scripts')
            <script>
                let sCurveChart = null;
                let chartInitialized = false;

                window.initSCurveChart = function (chartData) {
                    try {
                        // Wait for Chart.js to be available
                        if (typeof Chart === 'undefined') {
                            console.error('Chart.js library not loaded');
                            setTimeout(() => window.initSCurveChart(chartData), 200);
                            return;
                        }

                        const canvas = document.getElementById('sCurveChart');
                        const wrapper = document.getElementById('sCurveChartWrapper');
                        if (!canvas) {
                            console.error('Canvas element #sCurveChart not found');
                            return;
                        }
                        // Ensure canvas fills wrapper and has proper display styles
                        canvas.style.width = '100%';
                        canvas.style.height = '100%';
                        const ctx = canvas.getContext('2d');

                        // Validate chart data
                        if (!chartData || !chartData.labels || !chartData.datasets) {
                            console.warn('Invalid chart data structure:', chartData);
                            ctx.fillStyle = '#ccc';
                            ctx.fillRect(0, 0, canvas.width, canvas.height);
                            return;
                        }

                        // Check if data is empty
                        if (chartData.labels.length === 0 || chartData.datasets.length === 0) {
                            console.warn('Chart data is empty');
                            ctx.fillStyle = '#e5e7eb';
                            ctx.font = '14px sans-serif';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillText('No data available for this project', canvas.width / 2, canvas.height / 2);
                            return;
                        }

                        console.log('Initializing S-Curve chart with data:', chartData);

                        // Destroy existing chart and resize observer
                        if (sCurveChart) {
                            try {
                                sCurveChart.destroy();
                                sCurveChart = null;
                            } catch (e) {
                                console.warn('Error destroying previous chart:', e);
                            }
                        }
                        if (window.sCurveResizeObserver) {
                            try { window.sCurveResizeObserver.disconnect(); } catch (e) { }
                            window.sCurveResizeObserver = null;
                        }

                        // Create new chart
                        sCurveChart = new Chart(ctx, {
                            type: 'line',
                            data: chartData,
                            options: {
                                responsive: true,
                                // Let the wrapper control height so chart fills the div
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom',
                                        labels: {
                                            usePointStyle: true,
                                            padding: 15,
                                            font: {
                                                size: 12
                                            }
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                        titleFont: { size: 13 },
                                        bodyFont: { size: 12 },
                                        padding: 10,
                                        displayColors: true,
                                        callbacks: {
                                            label: function (context) {
                                                return context.dataset.label + ': ' + parseFloat(context.parsed.y).toFixed(2) + '%';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        title: {
                                            display: true,
                                            text: 'Progress (%)'
                                        },
                                        ticks: {
                                            callback: function (value) {
                                                return value + '%';
                                            }
                                        },
                                        grid: {
                                            drawBorder: false,
                                            color: function (context) {
                                                if (context.tick.value === 0 || context.tick.value === 100) {
                                                    return 'rgba(0, 0, 0, 0.2)';
                                                }
                                                return 'rgba(0, 0, 0, 0.05)';
                                            }
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Timeline'
                                        },
                                        grid: {
                                            drawBorder: false,
                                            display: false
                                        },
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 0
                                        }
                                    }
                                }
                            }
                        });

                        chartInitialized = true;
                        // Resize observer to handle container resizes
                        if (wrapper && window.ResizeObserver) {
                            try {
                                window.sCurveResizeObserver = new ResizeObserver(() => {
                                    if (sCurveChart && typeof sCurveChart.resize === 'function') {
                                        sCurveChart.resize();
                                    }
                                });
                                window.sCurveResizeObserver.observe(wrapper);
                            } catch (e) {
                                console.warn('ResizeObserver not available or failed:', e);
                            }
                        }
                        console.log('S-Curve chart initialized successfully');

                    } catch (error) {
                        console.error('Error initializing S-Curve chart:', error);
                        console.error('Chart data was:', chartData);
                    }
                };

                // Wait for Chart.js to load
                const waitForChartJS = (maxAttempts = 20) => {
                    if (typeof Chart !== 'undefined') {
                        console.log('Chart.js loaded, ready to initialize');
                        // Chart.js is loaded, data will be passed via Livewire
                    } else if (maxAttempts > 0) {
                        setTimeout(() => waitForChartJS(maxAttempts - 1), 100);
                    } else {
                        console.error('Chart.js failed to load after timeout');
                    }
                };

                // Initialize Chart.js loading check
                waitForChartJS();

                // Parse chart JSON from script tag and initialize when ready
                const tryInitFromScriptTag = () => {
                    const dataEl = document.getElementById('chart-data');
                    if (!dataEl) {
                        console.warn('chart-data element not found');
                        return;
                    }
                    try {
                        const raw = dataEl.textContent || dataEl.innerText || '';
                        const parsed = raw.trim();
                        if (!parsed) {
                            console.warn('chart-data is empty');
                            return;
                        }
                        const chartData = JSON.parse(parsed);
                        if (!chartData || !chartData.labels || !chartData.datasets) {
                            console.warn('Invalid chartData from server', chartData);
                            return;
                        }
                        if (typeof Chart === 'undefined') {
                            // wait for Chart.js then retry
                            setTimeout(tryInitFromScriptTag, 100);
                            return;
                        }
                        window.initSCurveChart(chartData);
                    } catch (e) {
                        console.error('Failed to parse chart-data JSON', e);
                    }
                };

                // Try on DOM ready
                document.addEventListener('DOMContentLoaded', tryInitFromScriptTag);
                // Also try after Livewire updates (if Livewire is present)
                if (window.Livewire && window.Livewire.hook) {
                    window.Livewire.hook('message.processed', () => tryInitFromScriptTag());
                }
            </script>
        @endpush


    @endif

</x-filament-panels::page>