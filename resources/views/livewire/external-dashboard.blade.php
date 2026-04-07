<div class="min-h-screen bg-gray-50">
    <!-- Flash Messages -->
    @if (session('message'))
        <div class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transition-all duration-300"
            id="success-message">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg transition-all duration-300"
            id="error-message">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-4 sm:py-6 gap-4">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 truncate">{{ $project->name }}</h1>
                <p class="text-gray-600 mt-1 text-sm sm:text-base">{{ config('app.name') }} - External Dashboard</p>
            </div>
            <div class="flex items-center space-x-3 sm:space-x-4 flex-shrink-0">
                <!-- Refresh Button -->
                <button wire:click="refreshData" wire:loading.attr="disabled"
                    class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-3 sm:px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center space-x-1 sm:space-x-2">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    <span wire:loading.remove class="hidden sm:inline">Refresh</span>
                    <span wire:loading class="hidden sm:inline">Refreshing...</span>
                </button>

                <!-- Logout Button -->
                <button wire:click="logout"
                    class="bg-red-600 hover:bg-red-700 text-white px-3 sm:px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center space-x-1 sm:space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                    <span class="hidden sm:inline">Logout</span>
                </button>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Project Stats Overview (Always visible) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Team</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $projectStats['total_team'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-green-50 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Progress</p>
                        <div class="flex items-center space-x-2">
                            <p
                                class="text-2xl font-bold {{ $projectStats['progress_percentage'] >= 100 ? 'text-green-600' : ($projectStats['progress_percentage'] >= 75 ? 'text-blue-600' : ($projectStats['progress_percentage'] >= 50 ? 'text-yellow-600' : 'text-gray-900')) }}">
                                {{ $projectStats['progress_percentage'] ?? 0 }}%
                            </p>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-gradient-to-r {{ $projectStats['progress_percentage'] >= 100 ? 'from-green-400 to-green-600' : ($projectStats['progress_percentage'] >= 75 ? 'from-blue-400 to-blue-600' : ($projectStats['progress_percentage'] >= 50 ? 'from-yellow-400 to-yellow-600' : 'from-gray-400 to-gray-600')) }} h-2 rounded-full transition-all duration-300"
                                style="width: {{ min($projectStats['progress_percentage'] ?? 0, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-50 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Remaining Days</p>
                        <p
                            class="text-2xl font-bold {{ $projectStats['remaining_days'] !== null && $projectStats['remaining_days'] < 0 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $projectStats['remaining_days'] ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-50 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Tasks</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $projectStats['total_tickets'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Layout Container -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm" id="dashboard-tabs">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
                <!-- Desktop/Tablet Tab Navigation -->
                <nav class="hidden sm:flex space-x-8 px-4 lg:px-6" aria-label="Tabs">
                    <button
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors duration-200"
                        data-tab="tasks" onclick="switchTab('tasks')">
                        <span class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                            <span class="hidden md:inline">Project Tasks</span>
                            <span class="md:hidden">Tasks</span>
                        </span>
                    </button>
                    <button
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors duration-200"
                        data-tab="activity" onclick="switchTab('activity')">
                        <span class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span class="hidden md:inline">Recent Activity</span>
                            <span class="md:hidden">Activity</span>
                        </span>
                    </button>
                </nav>

                <div class="sm:hidden px-4 py-3">
                    <div class="relative">
                        <button type="button" onclick="toggleMobileTabDropdown()"
                            class="w-full bg-white border border-gray-300 rounded-md px-4 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            id="mobile-tab-button">
                            <span class="flex items-center justify-between">
                                <span class="flex items-center space-x-2" id="mobile-tab-selected">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                        </path>
                                    </svg>
                                    <span>Project Tasks</span>
                                </span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </span>
                        </button>

                        <div id="mobile-tab-dropdown"
                            class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-lg z-10">
                            <div class="py-1">
                                <button onclick="switchMobileTab('tasks')"
                                    class="mobile-tab-option w-full px-4 py-2 text-left hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                                    data-tab="tasks">
                                    <span class="flex items-center space-x-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                            </path>
                                        </svg>
                                        <span>Project Tasks</span>
                                    </span>
                                </button>
                                
                                <button onclick="switchMobileTab('activity')"
                                    class="mobile-tab-option w-full px-4 py-2 text-left hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                                    data-tab="activity">
                                    <span class="flex items-center space-x-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        <span>Recent Activity</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="tab-content-container">
                <!-- Tasks Tab -->
                <div id="tasks-tab" class="tab-content">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <div class="flex flex-col gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Project Tasks</h3>
                                <p class="text-sm text-gray-600">All tasks in this project (ordered by creation date)
                                </p>
                            </div>

                            <!-- Filters -->
                            <div class="flex flex-col gap-3">
                                <!-- Search -->
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="searchTerm"
                                        placeholder="Search tasks..."
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Filters Row -->
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <!-- Status Filter -->
                                    <select wire:model.live="selectedStatus"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                        <option value="">All Status</option>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                                        @endforeach
                                    </select>

                                    <!-- Clear Filters -->
                                    @if ($selectedStatus || $searchTerm)
                                        <button wire:click="clearFilters"
                                            onclick="setTimeout(() => switchTab(getCurrentTab()), 150)"
                                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200 whitespace-nowrap">
                                            Clear Filters
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="overflow-x-auto">
                        <!-- Desktop Table -->
                        <table class="hidden sm:table min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Code</th>
                                    <th
                                        class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Task Name</th>
                                    <th
                                        class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Due Date</th>
                                    <th
                                        class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($this->tickets as $ticket)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td
                                            class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $ticket->uuid }}
                                        </td>
                                        <td class="px-4 lg:px-6 py-4 text-sm text-gray-900">
                                            <div class="font-medium">{{ $ticket->name }}</div>
                                            @if ($ticket->description)
                                                <div class="text-gray-500 text-xs mt-1 truncate max-w-xs">
                                                    {{ Str::limit($ticket->description, 100) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if ($ticket->due_date)
                                                <span
                                                    class="{{ \Carbon\Carbon::parse($ticket->due_date)->isPast() ? 'text-red-600 font-medium' : '' }}">
                                                    {{ \Carbon\Carbon::parse($ticket->due_date)->format('M d, Y') }}
                                                </span>
                                                @if (\Carbon\Carbon::parse($ticket->due_date)->isPast())
                                                    <div class="text-red-500 text-xs">Overdue</div>
                                                @endif
                                            @else
                                                <span class="text-gray-400">No due date</span>
                                            @endif
                                        </td>
                                        <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white"
                                                style="background-color: {{ $ticket->status->color ?? '#6B7280' }}">
                                                {{ $ticket->status->name ?? 'Unknown' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 lg:px-6 py-12 text-center">
                                            <div class="text-gray-500">
                                                <svg class="w-12 h-12 mx-auto mb-4" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                                    </path>
                                                </svg>
                                                <p class="text-lg font-medium">No tasks found</p>
                                                <p class="text-sm">Try adjusting your search or filter criteria.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Mobile Card View -->
                        <div class="sm:hidden">
                            @forelse($this->tickets as $ticket)
                                <div
                                    class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors duration-150">
                                    <div class="space-y-3">
                                        <!-- Header Row -->
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-medium text-gray-900 truncate">{{ $ticket->name }}
                                                </h4>
                                                <p class="text-sm text-gray-600 font-mono">{{ $ticket->uuid }}</p>
                                            </div>
                                            <span
                                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white flex-shrink-0"
                                                style="background-color: {{ $ticket->status->color ?? '#6B7280' }}">
                                                {{ $ticket->status->name ?? 'Unknown' }}
                                            </span>
                                        </div>

                                        <!-- Description -->
                                        @if ($ticket->description)
                                            <p class="text-sm text-gray-600 line-clamp-2">
                                                {{ Str::limit($ticket->description, 120) }}
                                            </p>
                                        @endif

                                        <!-- Due Date -->
                                        <div class="flex items-center text-sm">
                                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            @if ($ticket->due_date)
                                                <span
                                                    class="{{ \Carbon\Carbon::parse($ticket->due_date)->isPast() ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                                    Due:
                                                    {{ \Carbon\Carbon::parse($ticket->due_date)->format('M d, Y') }}
                                                    @if (\Carbon\Carbon::parse($ticket->due_date)->isPast())
                                                        <span class="text-red-500 text-xs ml-1">(Overdue)</span>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-gray-400">No due date</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                        </path>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-900">No tasks found</p>
                                    <p class="text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if ($this->tickets->hasPages())
                        <div class="px-4 lg:px-6 py-4 border-t border-gray-200" id="tasks-pagination-section">
                            {{ $this->tickets->links() }}
                        </div>
                    @endif
                </div>

                <!-- Activity Tab -->
                <div id="activity-tab" class="tab-content hidden">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                            <p class="text-sm text-gray-600">Latest updates and changes in the project</p>
                        </div>
                    </div>
                    <div class="p-4 lg:p-6">
                        @if ($this->recentActivities->count() > 0)
                            <!-- Desktop Activity List -->
                            <div class="hidden sm:block space-y-4">
                                @foreach ($this->recentActivities as $activity)
                                    <div
                                        class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg transition-colors duration-150">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-gray-600" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-900">
                                                <span
                                                    class="font-medium">{{ $activity->ticket->name ?? 'Unknown Task' }}</span>
                                                @if ($activity->status)
                                                    moved to <span class="font-medium"
                                                        style="color: {{ $activity->status->color ?? '#6B7280' }}">{{ $activity->status->name }}</span>
                                                @else
                                                    was updated
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $activity->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Mobile Activity List -->
                            <div class="sm:hidden space-y-3">
                                @foreach ($this->recentActivities as $activity)
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-3 h-3 text-gray-600" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-gray-900 font-medium leading-tight">
                                                    {{ $activity->ticket->name ?? 'Unknown Task' }}
                                                </p>
                                                @if ($activity->status)
                                                    <p class="text-sm text-gray-600 mt-1">
                                                        Moved to <span
                                                            class="font-medium px-2 py-0.5 rounded text-xs text-white"
                                                            style="background-color: {{ $activity->status->color ?? '#6B7280' }}">{{ $activity->status->name }}</span>
                                                    </p>
                                                @else
                                                    <p class="text-sm text-gray-600 mt-1">Task was updated</p>
                                                @endif
                                                <p class="text-xs text-gray-500 mt-2">
                                                    {{ $activity->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Recent Activities Pagination -->
                            @if ($this->recentActivities->hasPages())
                                <div class="mt-6 pt-4 border-t border-gray-200">
                                    {{ $this->recentActivities->links() }}
                                </div>
                            @endif
                        @else
                            <div class="text-center py-12">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <div>
                                    <p class="text-lg font-medium text-gray-900">No recent activity</p>
                                    <p class="text-sm text-gray-500">Activities will appear here when tasks are updated
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.getElementById('success-message');
            const errorMessage = document.getElementById('error-message');

            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.opacity = '0';
                    setTimeout(() => successMessage.remove(), 300);
                }, 3000);
            }

            if (errorMessage) {
                setTimeout(() => {
                    errorMessage.style.opacity = '0';
                    setTimeout(() => errorMessage.remove(), 300);
                }, 5000);
            }
        });

        // Tab Management with persistence across Livewire updates
        let currentActiveTab = sessionStorage.getItem('currentActiveTab') || 'tasks';
        let tabScrollPositions = {};

        // Maintain tab state across page interactions
        function saveCurrentTab(tabName) {
            currentActiveTab = tabName;
            sessionStorage.setItem('currentActiveTab', tabName);
        }

        function getCurrentTab() {
            return sessionStorage.getItem('currentActiveTab') || 'tasks';
        }

        // Mobile tab dropdown functions
        function toggleMobileTabDropdown() {
            const dropdown = document.getElementById('mobile-tab-dropdown');
            dropdown.classList.toggle('hidden');

            // Close dropdown when clicking outside
            if (!dropdown.classList.contains('hidden')) {
                setTimeout(() => {
                    document.addEventListener('click', closeMobileDropdownOnClickOutside);
                }, 10);
            }
        }

        function closeMobileDropdownOnClickOutside(event) {
            const dropdown = document.getElementById('mobile-tab-dropdown');
            const button = document.getElementById('mobile-tab-button');

            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add('hidden');
                document.removeEventListener('click', closeMobileDropdownOnClickOutside);
            }
        }

        function switchMobileTab(tabName) {
            // Update mobile dropdown selection
            updateMobileTabSelection(tabName);

            // Close dropdown
            document.getElementById('mobile-tab-dropdown').classList.add('hidden');
            document.removeEventListener('click', closeMobileDropdownOnClickOutside);

            // Switch to the tab
            switchTab(tabName);
        }

        function updateMobileTabSelection(tabName) {
            const selectedContainer = document.getElementById('mobile-tab-selected');
            const tabData = {
                'tasks': {
                    icon: 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    label: 'Project Tasks'
                },
                'activity': {
                    icon: 'M13 10V3L4 14h7v7l9-11h-7z',
                    label: 'Recent Activity'
                }
            };

            const tab = tabData[tabName];
            if (tab) {
                selectedContainer.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${tab.icon}"></path>
                    </svg>
                    <span>${tab.label}</span>
                `;
            }

            // Update active state in dropdown options
            document.querySelectorAll('.mobile-tab-option').forEach(option => {
                option.classList.remove('active');
                if (option.dataset.tab === tabName) {
                    option.classList.add('active');
                }
            });
        }

        function switchTab(tabName) {
            if (currentActiveTab) {
                tabScrollPositions[currentActiveTab] = window.pageYOffset;
            }

            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
                tab.classList.add('hidden');
            });

            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });

            const selectedTab = document.getElementById(tabName + '-tab');
            const selectedButton = document.querySelector(`[data-tab="${tabName}"]`);

            if (selectedTab && selectedButton) {
                selectedTab.classList.add('active');
                selectedTab.classList.remove('hidden');
                selectedButton.classList.add('active');

                saveCurrentTab(tabName);

                if (window.innerWidth < 640) {
                    updateMobileTabSelection(tabName);
                }

                if (tabScrollPositions[tabName] !== undefined) {
                    setTimeout(() => {
                        window.scrollTo({
                            top: tabScrollPositions[tabName],
                            behavior: 'smooth'
                        });
                    }, 50);
                }
            }
        }

        // Initialize tabs on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTab = getCurrentTab();
            switchTab(savedTab);

            if (typeof Livewire !== 'undefined') {
                setupLivewireListeners();
            } else {
                document.addEventListener('livewire:init', setupLivewireListeners);
            }
        });

        // Handle Livewire component updates (filtering, pagination, etc.)
        document.addEventListener('livewire:morph', function() {
            const savedTab = getCurrentTab();

            setTimeout(() => {
                switchTab(savedTab);
            }, 50);
        });

        // Handle Livewire navigation after component updates (fallback)
        document.addEventListener('livewire:navigated', function() {
            setTimeout(() => {
                const activeTab = getCurrentTab();
                switchTab(activeTab);
            }, 100);
        });

        // Prevent scroll jump on pagination and maintain tab state
        function handlePaginationClick(event) {
            const currentScrollPos = window.pageYOffset;

            const currentTab = getCurrentTab();

            setTimeout(() => {
                switchTab(currentTab);

                setTimeout(() => {
                    window.scrollTo({
                        top: currentScrollPos,
                        behavior: 'instant'
                    });
                }, 50);
            }, 100);
        }

        // Attach pagination handlers
        document.addEventListener('click', function(e) {
            if (e.target.closest('nav[role="navigation"]') || e.target.closest('.pagination')) {
                handlePaginationClick(e);
            }
        });

        // Handle filter changes to maintain tab state
        document.addEventListener('change', function(e) {
            if (e.target.matches('select[wire\\:model\\.live]') || e.target.matches('input[wire\\:model\\.live]')) {
                const currentTab = getCurrentTab();

                setTimeout(() => {
                    switchTab(currentTab);
                }, 150);
            }
        });

        // Handle search input changes
        document.addEventListener('input', function(e) {
            if (e.target.matches('input[wire\\:model\\.live\\.debounce]')) {
                const currentTab = getCurrentTab();

                setTimeout(() => {
                    switchTab(currentTab);
                }, 500);
            }
        });

        function setupLivewireListeners() {
            Livewire.on('data-refreshed', () => {
                const currentTab = getCurrentTab();

                setTimeout(() => {
                    switchTab(currentTab);
                }, 150);
            });

            Livewire.on('pagination-updated', () => {
                const currentTab = getCurrentTab();
                setTimeout(() => {
                    switchTab(currentTab);
                }, 50);
            });

            Livewire.hook('morph.updated', ({ el, component }) => {
                const currentTab = getCurrentTab();
                setTimeout(() => {
                    switchTab(currentTab);
                }, 25);
            });
        }
    </script>
@endpush
