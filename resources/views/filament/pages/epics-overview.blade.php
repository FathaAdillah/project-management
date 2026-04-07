<x-filament-panels::page>
    {{-- Project Selector --}}
    @php
        $selectedProject = $selectedProjectId ? $availableProjects->firstWhere('id', $selectedProjectId) : null;
    @endphp

    @if(!$selectedProject)
        <div class="mb-6">
            <x-filament::section>
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Select Project
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Choose a project to view its epics
                    </p>
                </div>

                {{-- Search Bar --}}
                <div class="mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="searchProject"
                            placeholder="Search projects by name or prefix..."
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        />
                        @if($searchProject)
                            <button
                                wire:click="$set('searchProject', '')"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                @if($availableProjects->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-gray-500 dark:text-gray-400">
                        <h3 class="text-base font-medium text-gray-900 dark:text-white mb-1">No Projects Available</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">You don't have access to any projects yet.</p>
                    </div>
                @elseif($this->filteredProjects->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mb-3 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="text-base font-medium text-gray-900 dark:text-white mb-1">No Projects Found</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Try adjusting your search terms</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                        @foreach($this->filteredProjects as $project)
                            <button
                                wire:click="$set('selectedProjectId', {{ $project->id }})"
                                class="relative p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:shadow-md transition-all text-left overflow-hidden"
                                style="border-left: 4px solid {{ $project->color ?? '#6B7280' }};"
                            >
                                {{-- Pin Icon Badge --}}
                                @if($project->is_pinned)
                                    <div class="absolute top-2 right-2">
                                        <div class="flex items-center justify-center w-6 h-6 rounded-full shadow-sm"
                                             style="background-color: {{ $project->color ?? '#6B7280' }};"
                                             title="Pinned Project">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M16 9V4h1c.55 0 1-.45 1-1s-.45-1-1-1H7c-.55 0-1 .45-1 1s.45 1 1 1h1v5c0 1.66-1.34 3-3 3v2h5.97v7l1 1 1-1v-7H19v-2c-1.66 0-3-1.34-3-3z"/>
                                            </svg>
                                        </div>
                                    </div>
                                @endif

                                {{-- Project Prefix Badge --}}
                                @if($project->ticket_prefix)
                                    @php
                                        $color = $project->color ?? '#6B7280';
                                        $hex = ltrim($color, '#');
                                        $r = hexdec(substr($hex, 0, 2));
                                        $g = hexdec(substr($hex, 2, 2));
                                        $b = hexdec(substr($hex, 4, 2));
                                        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
                                        $textColor = $brightness > 155 ? '#1F2937' : '#FFFFFF';
                                    @endphp
                                    <div class="inline-flex px-2.5 py-1 rounded text-xs font-semibold mb-3"
                                         style="background-color: {{ $color }}; color: {{ $textColor }};">
                                        {{ $project->ticket_prefix }}
                                    </div>
                                @endif

                                {{-- Project Name --}}
                                <h3 class="font-semibold text-base text-gray-900 dark:text-white line-clamp-2">
                                    {{ $project->name }}
                                </h3>
                            </button>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        </div>
    @else
        {{-- Project Switcher --}}
        <div class="mb-4" x-data="{ open: false }">
            <div class="relative">
                <button
                    @click="open = !open"
                    @click.away="open = false"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-900 dark:text-white bg-white dark:bg-gray-800 border-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    style="border-color: {{ $selectedProject->color ?? '#D1D5DB' }};"
                >
                    @if($selectedProject->ticket_prefix)
                        @php
                            $color = $selectedProject->color ?? '#6B7280';
                            $hex = ltrim($color, '#');
                            $r = hexdec(substr($hex, 0, 2));
                            $g = hexdec(substr($hex, 2, 2));
                            $b = hexdec(substr($hex, 4, 2));
                            $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
                            $textColor = $brightness > 155 ? '#1F2937' : '#FFFFFF';
                        @endphp
                        <span class="px-2 py-0.5 rounded text-xs font-semibold"
                              style="background-color: {{ $color }}; color: {{ $textColor }};">
                            {{ $selectedProject->ticket_prefix }}
                        </span>
                    @endif
                    <span>{{ $selectedProject->name }}</span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                {{-- Dropdown Menu --}}
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute top-full left-0 mt-2 w-80 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto"
                    style="display: none;"
                >
                    <div class="p-2">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Switch Project
                        </div>
                        @foreach($this->filteredProjects as $project)
                            <button
                                wire:click="$set('selectedProjectId', {{ $project->id }})"
                                @click="open = false"
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left {{ $project->id === $selectedProjectId ? 'bg-gray-50 dark:bg-gray-700' : '' }}"
                            >
                                @if($project->is_pinned)
                                    <div class="flex items-center justify-center w-5 h-5 rounded-full shrink-0"
                                         style="background-color: {{ $project->color ?? '#6B7280' }};"
                                         title="Pinned">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M16 9V4h1c.55 0 1-.45 1-1s-.45-1-1-1H7c-.55 0-1 .45-1 1s.45 1 1 1h1v5c0 1.66-1.34 3-3 3v2h5.97v7l1 1 1-1v-7H19v-2c-1.66 0-3-1.34-3-3z"/>
                                        </svg>
                                    </div>
                                @endif
                                @if($project->ticket_prefix)
                                    @php
                                        $color = $project->color ?? '#6B7280';
                                        $hex = ltrim($color, '#');
                                        $r = hexdec(substr($hex, 0, 2));
                                        $g = hexdec(substr($hex, 2, 2));
                                        $b = hexdec(substr($hex, 4, 2));
                                        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
                                        $textColor = $brightness > 155 ? '#1F2937' : '#FFFFFF';
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold"
                                          style="background-color: {{ $color }}; color: {{ $textColor }};">
                                        {{ $project->ticket_prefix }}
                                    </span>
                                @endif
                                <div class="flex-1 min-w-0 text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $project->name }}
                                </div>
                                @if($project->id === $selectedProjectId)
                                    <svg class="w-4 h-4 flex-shrink-0" style="color: {{ $project->color ?? '#3B82F6' }};" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($selectedProjectId && $epics->isNotEmpty())
        <x-filament::section>
            <x-slot name="heading">
                Epics Overview
            </x-slot>

            <div class="w-full space-y-3">
                @foreach($epics as $epic)
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div
                            class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 px-4 py-3 flex justify-between items-center cursor-pointer"
                            wire:click="toggleEpic({{ $epic->id }})"
                        >
                            <div class="flex items-center space-x-4">
                                <div>
                                    <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $epic->name }}</h3>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 hidden md:block">
                                        {{ $epic->start_date ? $epic->start_date->format('M d, Y') : '-' }} -
                                        {{ $epic->end_date ? $epic->end_date->format('M d, Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-gray-300 text-sm rounded-full px-3 py-1">
                                    {{ $epic->tickets->count() }} tickets
                                </div>
                                <button class="text-gray-400 hover:text-primary-500 focus:outline-none">
                                    @if($this->isExpanded($epic->id))
                                        <x-heroicon-s-chevron-down class="h-5 w-5 text-primary-500 dark:text-primary-400" />
                                    @else
                                        <x-heroicon-s-chevron-right class="h-5 w-5 dark:text-gray-400" />
                                    @endif
                                </button>
                            </div>
                        </div>

                        <!-- Epic Content - Accordion Content -->
                        @if($this->isExpanded($epic->id))
                            <div class="p-4">
                                <!-- Epic Description -->
                                @if($epic->description)
                                    <div class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-300 mb-2">Description</h4>
                                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md text-sm text-gray-900 dark:text-gray-300">
                                            {!! $epic->description !!}
                                        </div>
                                    </div>
                                @endif

                                <!-- Tickets -->
                                <div class="w-full">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-300">Tickets</h4>
                                        <a href="{{ route('filament.admin.resources.tickets.create', ['epic_id' => $epic->id]) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300">
                                            <x-heroicon-s-plus class="w-4 h-4 inline-block mr-1" />
                                            Add Ticket
                                        </a>
                                    </div>

                                    @if($epic->tickets->isEmpty())
                                        <div class="text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-4 rounded-md text-center border border-dashed border-gray-300 dark:border-gray-600 w-full">
                                            No tickets found for this epic.
                                        </div>
                                    @else
                                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-md w-full">
                                            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                <thead class="bg-gray-50 dark:bg-gray-700">
                                                    <tr>
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ticket</th>
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Assign To</th>
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Due Date</th>
                                                        <th scope="col" class="relative px-3 py-2">
                                                            <span class="sr-only">Actions</span>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                    @foreach($epic->tickets as $ticket)
                                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                            <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-900 dark:text-gray-100">
                                                                {{ $ticket->uuid }}
                                                            </td>
                                                            <td class="px-3 py-2 text-xs text-gray-900 dark:text-gray-100">
                                                                {{ $ticket->name }}
                                                            </td>
                                                            <td class="px-3 py-2 whitespace-nowrap text-xs">
                                                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                                                    {{ match($ticket->status->name ?? '') {
                                                                        'To Do' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
                                                                        'In Progress' => 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
                                                                        'Review' => 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200',
                                                                        'Done' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
                                                                        default => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
                                                                    } }}">
                                                                    {{ $ticket->status->name ?? 'No Status' }}
                                                                </span>
                                                            </td>
                                                            <td class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                                                                @if($ticket->assignees->isEmpty())
                                                                    <x-filament::badge color="gray" icon="heroicon-m-user-minus">
                                                                        Unassigned
                                                                    </x-filament::badge>
                                                                @else
                                                                    <div class="flex flex-wrap gap-1">
                                                                        @foreach($ticket->assignees->take(2) as $assignee)
                                                                            <x-filament::badge
                                                                                color="primary"
                                                                                icon="heroicon-m-user"
                                                                                size="sm"
                                                                            >
                                                                                {{ $assignee->name }}
                                                                            </x-filament::badge>
                                                                        @endforeach

                                                                        @if($ticket->assignees->count() > 2)
                                                                            <x-filament::badge
                                                                                color="gray"
                                                                                size="sm"
                                                                                :tooltip="$ticket->assignees->skip(2)->pluck('name')->implode(', ')"
                                                                            >
                                                                                +{{ $ticket->assignees->count() - 2 }}
                                                                            </x-filament::badge>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                                                {{ $ticket->due_date ? $ticket->due_date->format('M d, Y') : '-' }}
                                                            </td>
                                                            <td class="px-3 py-2 whitespace-nowrap text-right text-xs font-medium">

                                                                <a href="{{ route('filament.admin.resources.tickets.view', ['record' => $ticket->id]) }}" target="_blank" class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300">
                                                                    View
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @elseif($selectedProjectId && $epics->isEmpty())
        {{-- No Epics Found State --}}
        <div class="flex flex-col items-center justify-center h-64 text-gray-500 dark:text-gray-400 gap-4">
            <div class="flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 p-6">
                <x-heroicon-o-flag class="w-16 h-16 text-gray-400 dark:text-gray-500" />
            </div>
            <h2 class="text-xl font-medium text-gray-600 dark:text-gray-300">No epics found in this project</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                This project doesn't have any epics yet. Create an epic to organize your tickets.
            </p>
        </div>
    @endif
</x-filament-panels::page>
