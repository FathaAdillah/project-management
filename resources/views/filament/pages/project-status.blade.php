<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Project Selector --}}
        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>

        @if ($selectedProject)
            {{-- Status Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Planned Progress --}}
                <x-filament::card class="h-full">
                    <div class="flex flex-col h-full justify-between">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Planned Progress</span>
                        <span
                            class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $plannedProgress }}%</span>
                    </div>
                </x-filament::card>

                {{-- Actual Progress --}}
                <x-filament::card class="h-full">
                    <div class="flex flex-col h-full justify-between">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Actual Progress</span>
                        <span
                            class="mt-2 text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $actualProgress }}%</span>
                    </div>
                </x-filament::card>

                {{-- Deviation --}}
                <x-filament::card class="h-full">
                    <div class="flex flex-col h-full justify-between">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Deviation</span>
                        <div class="mt-2 flex items-center gap-2">
                            <span
                                class="text-3xl font-bold {{ $deviation >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                {{ $deviation > 0 ? '+' : '' }}{{ $deviation }}%
                            </span>
                        </div>
                    </div>
                </x-filament::card>

                {{-- Project Status --}}
                <x-filament::card class="h-full">
                    <div class="flex flex-col h-full justify-between">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Project Status</span>
                        <div class="mt-2 flex items-center gap-2">
                            @php
                                $dotClass = match ($projectStatusColor) {
                                    'success' => 'bg-green-500',
                                    'warning' => 'bg-yellow-500',
                                    'danger' => 'bg-red-500',
                                    default => 'bg-gray-500',
                                };
                                $textClass = match ($projectStatusColor) {
                                    'success' => 'text-green-600 dark:text-green-400',
                                    'warning' => 'text-yellow-600 dark:text-yellow-400',
                                    'danger' => 'text-red-600 dark:text-red-400',
                                    default => 'text-gray-600 dark:text-gray-400',
                                };
                            @endphp
                            <span class="inline-block w-3 h-3 rounded-full {{ $dotClass }}"></span>
                            <span class="text-lg font-bold {{ $textClass }}">{{ $projectStatus }}</span>
                        </div>
                    </div>
                </x-filament::card>
            </div>

            {{-- S-Curve Chart --}}
            <x-filament::section>
                <div class="mb-4">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">S-Curve Analysis</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Comparison of planned vs actual progress over
                        time</p>
                </div>

                <div class="relative h-96 w-full" wire:ignore>
                    <canvas id="sCurveChart"></canvas>
                </div>
            </x-filament::section>

            @assets
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            @endassets

            @script
                <script>
                    // Wrap everything in an IIFE to avoid Alpine.js conflicts
                    (function() {
                        // We use the CDN version which defines 'Chart' globally.
                        // We wait for it to be available.

                        let sCurveChartInstance = null;

                        const initChart = (data) => {
                            const canvas = document.getElementById('sCurveChart');
                            if (!canvas) return;

                            const ctx = canvas.getContext('2d');

                            if (sCurveChartInstance) {
                                sCurveChartInstance.destroy();
                            }

                            if (!data || !data.labels || data.labels.length === 0) {
                                return;
                            }

                            sCurveChartInstance = new Chart(ctx, {
                                type: 'line',
                                data: data,
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    interaction: {
                                        mode: 'index',
                                        intersect: false,
                                    },
                                    plugins: {
                                        legend: {
                                            position: 'bottom',
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    return context.dataset.label + ': ' + context.parsed.y + '%';
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
                                            }
                                        }
                                    }
                                }
                            });
                        };

                        // Wait for Chart to be defined
                        const waitForChart = (callback) => {
                            if (typeof Chart !== 'undefined') {
                                callback();
                            } else {
                                setTimeout(() => waitForChart(callback), 100);
                            }
                        };

                        waitForChart(() => {
                            initChart($wire.chartData);
                        });

                        $wire.on('chart-updated', (data) => {
                            waitForChart(() => {
                                const chartInstance = Chart.getChart("sCurveChart");
                                if (chartInstance) {
                                    chartInstance.data = data[0];
                                    chartInstance.update();
                                } else {
                                    initChart(data[0]);
                                }
                            });
                        });
                    })();
                </script>
            @endscript
        @else
            <div class="flex flex-col items-center justify-center h-64 text-gray-500 dark:text-gray-400">
                <x-heroicon-o-presentation-chart-line class="w-16 h-16 text-gray-300" />
                <p class="mt-4 text-lg">Select a project to view its status</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
