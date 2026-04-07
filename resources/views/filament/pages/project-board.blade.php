<x-filament-panels::page>

    {{-- Project Selector --}}
    @if(!$selectedProject)
        <div class="mb-6">
            <x-filament::section>
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Select Project
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Choose a project to view its board
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

                @if($projects->isEmpty())
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
                                wire:click="selectProject({{ $project->id }})"
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
                                        // Convert hex to RGB
                                        $hex = ltrim($color, '#');
                                        $r = hexdec(substr($hex, 0, 2));
                                        $g = hexdec(substr($hex, 2, 2));
                                        $b = hexdec(substr($hex, 4, 2));
                                        // Calculate brightness
                                        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
                                        // Use dark text for light colors, light text for dark colors
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
                                wire:click="selectProject({{ $project->id }})"
                                @click="open = false"
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left {{ $project->id === $selectedProject->id ? 'bg-gray-50 dark:bg-gray-700' : '' }}"
                            >
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
                                @if($project->id === $selectedProject->id)
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

    @if($selectedProject)
        <div
            x-data="{
                draggingTicket: null,
                isTouchDevice: false,
                touchStartX: 0,
                touchStartY: 0,
                scrollStartX: 0,
                columnScrollPositions: {},

                moveTicketToStatus(ticketId, statusId) {
                    $wire.call('moveTicket', parseInt(ticketId), parseInt(statusId));
                },

                saveScrollPositions() {
                    const columns = document.querySelectorAll('.status-column .overflow-y-auto');
                    columns.forEach((column, index) => {
                        this.columnScrollPositions[index] = column.scrollTop;
                    });
                },

                restoreScrollPositions() {
                    const columns = document.querySelectorAll('.status-column .overflow-y-auto');
                    columns.forEach((column, index) => {
                        if (this.columnScrollPositions[index] !== undefined) {
                            column.scrollTop = this.columnScrollPositions[index];
                        }
                    });
                },

                init() {
                    this.$nextTick(() => {
                        this.removeAllEventListeners();
                        this.attachAllEventListeners();
                        this.setupTouchScrolling();
                        this.isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
                        this.setupPageVisibilityListener();
                    });
                },

                setupPageVisibilityListener() {
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden) {
                            this.saveScrollPositions();
                            setTimeout(() => {
                                this.removeAllEventListeners();
                                this.attachAllEventListeners();
                                this.restoreScrollPositions();
                            }, 100);
                        }
                    });

                    window.addEventListener('focus', () => {
                        this.saveScrollPositions();
                        setTimeout(() => {
                            this.removeAllEventListeners();
                            this.attachAllEventListeners();
                            this.restoreScrollPositions();
                        }, 100);
                    });

                    window.addEventListener('popstate', () => {
                        this.saveScrollPositions();
                        setTimeout(() => {
                            this.removeAllEventListeners();
                            this.attachAllEventListeners();
                            this.restoreScrollPositions();
                        }, 200);
                    });

                    document.addEventListener('livewire:navigated', () => {
                        this.saveScrollPositions();
                        setTimeout(() => {
                            this.removeAllEventListeners();
                            this.attachAllEventListeners();
                            this.restoreScrollPositions();
                        }, 300);
                    });

                    document.addEventListener('livewire:load', () => {
                        this.saveScrollPositions();
                        setTimeout(() => {
                            this.removeAllEventListeners();
                            this.attachAllEventListeners();
                            this.restoreScrollPositions();
                        }, 100);
                    });

                    document.addEventListener('livewire:updated', () => {
                        this.saveScrollPositions();
                        setTimeout(() => {
                            this.removeAllEventListeners();
                            this.attachAllEventListeners();
                            this.restoreScrollPositions();
                        }, 100);
                    });

                    window.addEventListener('ticket-updated', () => {
                        this.saveScrollPositions();
                        setTimeout(() => {
                            this.removeAllEventListeners();
                            this.attachAllEventListeners();
                            this.restoreScrollPositions();
                        }, 150);
                    });

                    setInterval(() => {
                        if (document.visibilityState === 'visible') {
                            this.saveScrollPositions();
                            this.ensureDragDropInitialized();
                            this.restoreScrollPositions();
                        }
                    }, 2000);
                },

                ensureDragDropInitialized() {
                    const tickets = document.querySelectorAll('.ticket-card');
                    let needsReinitialization = false;

                    tickets.forEach(ticket => {
                        if (!ticket.getAttribute('draggable') || ticket.getAttribute('draggable') !== 'true') {
                            needsReinitialization = true;
                        }
                    });

                    if (needsReinitialization && tickets.length > 0) {
                        this.removeAllEventListeners();
                        this.attachAllEventListeners();
                    }
                },

                setupTouchScrolling() {
                    const container = document.getElementById('board-container');

                    container.addEventListener('touchstart', (e) => {
                        this.touchStartX = e.touches[0].clientX;
                        this.touchStartY = e.touches[0].clientY;
                        this.scrollStartX = container.scrollLeft;
                    }, { passive: true });

                    container.addEventListener('touchmove', (e) => {
                        if (e.touches.length !== 1) return;

                        const touchX = e.touches[0].clientX;
                        const touchY = e.touches[0].clientY;
                        const moveX = this.touchStartX - touchX;
                        const moveY = this.touchStartY - touchY;

                        if (Math.abs(moveX) > Math.abs(moveY)) {
                            e.preventDefault();
                            container.scrollLeft = this.scrollStartX + moveX;
                        }
                    }, { passive: false });
                },

                removeAllEventListeners() {
                    const tickets = document.querySelectorAll('.ticket-card');
                    tickets.forEach(ticket => {
                        ticket.removeAttribute('draggable');
                        const newTicket = ticket.cloneNode(true);
                        ticket.parentNode.replaceChild(newTicket, ticket);
                    });

                    const columns = document.querySelectorAll('.status-column');
                    columns.forEach(column => {
                        const newColumn = column.cloneNode(false);
                        while (column.firstChild) {
                            newColumn.appendChild(column.firstChild);
                        }
                        if (column.parentNode) {
                            column.parentNode.replaceChild(newColumn, column);
                        }
                    });
                },

                attachAllEventListeners() {
                    @if(!$this->canMoveTickets())
                        return;
                    @endif

                    const tickets = document.querySelectorAll('.ticket-card');
                    tickets.forEach(ticket => {
                        ticket.setAttribute('draggable', true);

                        ticket.addEventListener('dragstart', (e) => {
                            this.draggingTicket = ticket.getAttribute('data-ticket-id');
                            ticket.classList.add('opacity-50');
                            e.dataTransfer.effectAllowed = 'move';
                        });

                        ticket.addEventListener('dragend', () => {
                            ticket.classList.remove('opacity-50');
                            this.draggingTicket = null;
                        });

                        let longPressTimer;
                        let isDragging = false;
                        let originalColumn;

                        ticket.addEventListener('touchstart', (e) => {
                            if (isDragging) return;

                            longPressTimer = setTimeout(() => {
                                originalColumn = ticket.closest('.status-column');
                                this.draggingTicket = ticket.getAttribute('data-ticket-id');
                                ticket.classList.add('opacity-50', 'relative', 'z-30');
                                isDragging = true;
                                ticket.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)';
                            }, 500);
                        }, { passive: true });

                        ticket.addEventListener('touchmove', (e) => {
                            if (!isDragging) {
                                clearTimeout(longPressTimer);
                                return;
                            }

                            const touch = e.touches[0];
                            const columns = document.querySelectorAll('.status-column');

                            columns.forEach(column => {
                                const rect = column.getBoundingClientRect();
                                if (touch.clientX >= rect.left &&
                                    touch.clientX <= rect.right &&
                                    touch.clientY >= rect.top &&
                                    touch.clientY <= rect.bottom) {
                                    column.classList.add('bg-primary-50', 'dark:bg-primary-950');
                                } else {
                                    column.classList.remove('bg-primary-50', 'dark:bg-primary-950');
                                }
                            });
                        });

                        ticket.addEventListener('touchend', (e) => {
                            clearTimeout(longPressTimer);

                            if (!isDragging) return;

                            isDragging = false;
                            ticket.classList.remove('opacity-50', 'relative', 'z-30');
                            ticket.style.boxShadow = '';

                            const touch = e.changedTouches[0];
                            const columns = document.querySelectorAll('.status-column');

                            let targetColumn = null;
                            columns.forEach(column => {
                                const rect = column.getBoundingClientRect();
                                if (touch.clientX >= rect.left &&
                                    touch.clientX <= rect.right &&
                                    touch.clientY >= rect.top &&
                                    touch.clientY <= rect.bottom) {
                                    targetColumn = column;
                                }
                                column.classList.remove('bg-primary-50', 'dark:bg-primary-950');
                            });

                            if (targetColumn && targetColumn !== originalColumn) {
                                const statusId = targetColumn.getAttribute('data-status-id');
                                const ticketId = this.draggingTicket;

                                this.moveTicketToStatus(ticketId, statusId);
                            }

                            this.draggingTicket = null;
                        });

                        ticket.addEventListener('touchcancel', () => {
                            clearTimeout(longPressTimer);
                            if (!isDragging) return;

                            isDragging = false;
                            ticket.classList.remove('opacity-50', 'relative', 'z-30');
                            ticket.style.boxShadow = '';
                            this.draggingTicket = null;

                            document.querySelectorAll('.status-column').forEach(column => {
                                column.classList.remove('bg-primary-50', 'dark:bg-primary-950');
                            });
                        });
                    });

                    const columns = document.querySelectorAll('.status-column');
                    columns.forEach(column => {
                        column.addEventListener('dragover', (e) => {
                            e.preventDefault();
                            e.dataTransfer.dropEffect = 'move';
                            column.classList.add('bg-primary-50', 'dark:bg-primary-950');
                        });

                        column.addEventListener('dragleave', () => {
                            column.classList.remove('bg-primary-50', 'dark:bg-primary-950');
                        });

                        column.addEventListener('drop', (e) => {
                            e.preventDefault();
                            column.classList.remove('bg-primary-50', 'dark:bg-primary-950');

                            if (this.draggingTicket) {
                                const statusId = column.getAttribute('data-status-id');
                                const ticketId = this.draggingTicket;
                                this.draggingTicket = null;
                                this.moveTicketToStatus(ticketId, statusId);
                            }
                        });
                    });
                }
            }"
            x-init="init()"
            @ticket-moved.window="init()"
            @ticket-updated.window="init()"
            @refresh-board.window="init()"
            wire:key="board-container-{{ $selectedProject->id }}"
            class="relative overflow-x-auto pb-6 {{ !$this->canMoveTickets() ? 'view-only-mode' : '' }}"
            id="board-container"
        >
            {{-- Mobile swipe hint --}}
            <div class="md:hidden flex justify-center mb-2 text-xs text-gray-500 dark:text-gray-400 items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Swipe horizontally to view all columns</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </div>

            {{-- View Only Mode Indicator --}}
            @if(!$this->canMoveTickets())
                <div class="flex justify-center mb-4">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg text-amber-800 dark:text-amber-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span class="text-sm font-medium">View Only Mode</span>
                        <span class="text-xs opacity-75">You can view tickets but cannot move them</span>
                    </div>
                </div>
            @endif

            <div class="inline-flex gap-4 pb-2 min-w-full">
                @foreach ($this->ticketStatuses as $status)
                    <div
                        wire:key="status-column-{{ $status->id }}"
                        class="status-column rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col bg-gray-50 dark:bg-gray-900 w-[calc(85vw-2rem)] min-w-[280px] max-w-[350px] h-[700px] sm:w-[calc((100vw-6rem)/2)] sm:h-[750px] lg:w-[calc((100vw-8rem)/3)] lg:h-[800px] xl:w-[calc((100vw-10rem)/4)] xl:h-[850px]"
                        data-status-id="{{ $status->id }}"
                    >
                        <div
                            class="px-4 py-3 rounded-t-xl border-b border-gray-200 dark:border-gray-700 flex-shrink-0"
                            style="background-color: {{ $status->color ?? '#f3f4f6' }};"
                        >
                            <div class="flex items-center justify-between">
                                <h3 class="font-medium flex items-center gap-2" style="color: white; text-shadow: 0px 0px 1px rgba(0,0,0,0.5);">
                                    <span>{{ $status->name }}</span>
                                    <span class="text-sm opacity-80">{{ $status->tickets->count() }}</span>
                                    @if($status->is_completed)
                                        <div class="flex items-center justify-center w-6 h-6 bg-green-500 rounded-full border-2 border-white shadow-lg" title="Completed Status">
                                            <svg class="w-3 h-3 text-white font-bold" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </h3>

                                <!-- Sort Menu Dropdown -->
                                <div class="relative" x-data="{ open: false }">
                                    <button
                                        @click="open = !open"
                                        @click.away="open = false"
                                        class="p-1 rounded hover:bg-black hover:bg-opacity-20 transition-colors"
                                        style="color: white;"
                                    >
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                        </svg>
                                    </button>

                                    <div
                                        x-show="open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute top-8 left-0 w-52 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
                                        style="display: none; transform: translateX(-100%);"
                                    >
                                        <div class="p-2">
                                            <div class="flex items-center justify-between px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">Sort list</span>
                                                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="py-1">
                                                <button
                                                    wire:click="setSortOrder({{ $status->id }}, 'date_created_newest')"
                                                    @click="open = false"
                                                    class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-white rounded"
                                                >
                                                    Date created (newest first)
                                                </button>
                                                <button
                                                    wire:click="setSortOrder({{ $status->id }}, 'date_created_oldest')"
                                                    @click="open = false"
                                                    class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-white rounded"
                                                >
                                                    Date created (oldest first)
                                                </button>
                                                <button
                                                    wire:click="setSortOrder({{ $status->id }}, 'card_name_alphabetical')"
                                                    @click="open = false"
                                                    class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-white rounded"
                                                >
                                                    Card name (alphabetically)
                                                </button>
                                                <button
                                                    wire:click="setSortOrder({{ $status->id }}, 'due_date')"
                                                    @click="open = false"
                                                    class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-white rounded"
                                                >
                                                    Due date
                                                </button>
                                                <button
                                                    wire:click="setSortOrder({{ $status->id }}, 'priority')"
                                                    @click="open = false"
                                                    class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-white rounded"
                                                >
                                                    Priority
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 flex flex-col gap-3 flex-1 overflow-y-auto" style="max-height: calc(100% - 60px);" x-data="{ visibleTickets: 10, totalTickets: {{ $status->tickets->count() }}, scrollPos: 0 }" x-init="$nextTick(() => { $el.addEventListener('scroll', () => { scrollPos = $el.scrollTop; if ($el.scrollTop + $el.clientHeight >= $el.scrollHeight - 100 && visibleTickets < totalTickets) { visibleTickets = Math.min(visibleTickets + 10, totalTickets); } }); })" x-ref="ticketContainer{{ $status->id }}">
                            @foreach ($status->tickets as $index => $ticket)
                                <div
                                    wire:key="ticket-{{ $status->id }}-{{ $ticket->id }}"
                                    class="ticket-card bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 cursor-move"
                                    data-ticket-id="{{ $ticket->id }}"
                                    x-show="{{ $index }} < visibleTickets"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                >
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-mono text-gray-500 dark:text-gray-400 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded truncate max-w-[120px] sm:max-w-none">
                                            {{ $ticket->uuid }}
                                        </span>
                                        <div class="flex items-center gap-1">
                                            @if ($ticket->priority)
                                                <span class="text-xs px-1.5 py-0.5 rounded whitespace-nowrap text-white font-medium" style="background-color: {{ $ticket->priority->color }};">
                                                    {{ $ticket->priority->name }}
                                                </span>
                                            @endif
                                            @if ($ticket->due_date)
                                                <span class="text-xs px-1.5 py-0.5 rounded whitespace-nowrap {{ $ticket->due_date->isPast() ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' }}">
                                                    {{ $ticket->due_date->format('M d') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">{{ $ticket->name }}</h4>

                                    @if ($ticket->description)
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 line-clamp-2">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($ticket->description), 100) }}
                                        </p>
                                    @endif

                                    <div class="flex justify-between items-center mt-2">
                                       @if ($ticket->assignees->isNotEmpty())
                                            <div class="flex flex-wrap gap-1 max-w-[180px]">
                                                @foreach($ticket->assignees->take(2) as $assignee)
                                                    <div class="inline-flex items-center px-2 py-1 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 gap-1">
                                                        <span class="w-4 h-4 rounded-full bg-primary-500 flex items-center justify-center text-xs text-white flex-shrink-0">
                                                            {{ substr($assignee->name, 0, 1) }}
                                                        </span>
                                                        <span class="text-xs font-medium truncate">{{ \Illuminate\Support\Str::limit($assignee->name, 8) }}</span>
                                                    </div>
                                                @endforeach
                                                @if($ticket->assignees->count() > 2)
                                                    <div class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400">
                                                        <span class="text-xs font-medium">+{{ $ticket->assignees->count() - 2 }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 dark:text-gray-500 mr-1 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="text-xs font-medium">Unassigned</span>
                                            </div>
                                        @endif

                                        <a
                                            href="{{ \App\Filament\Resources\Tickets\TicketResource::getUrl('view', ['record' => $ticket->id]) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            onclick="
                                                event.preventDefault();
                                                const container = this.closest('.overflow-y-auto');
                                                const scrollPos = container.scrollTop;
                                                window.open(this.href, '_blank');
                                                setTimeout(() => {
                                                    container.scrollTop = scrollPos;
                                                }, 0);
                                                return false;
                                            "
                                            class="inline-flex items-center justify-center w-8 h-8 text-sm font-medium rounded-lg border border-gray-200 dark:border-gray-700 text-primary-600 hover:text-primary-500 dark:text-primary-500 dark:hover:text-primary-400 flex-shrink-0"
                                        >
                                            <x-heroicon-m-eye class="w-4 h-4" />
                                        </a>
                                    </div>
                                </div>
                            @endforeach

                            @if ($status->tickets->isEmpty())
                                <div class="flex items-center justify-center h-24 text-gray-500 dark:text-gray-400 text-sm italic border border-dashed border-gray-300 dark:border-gray-700 rounded-lg">
                                    No tickets
                                </div>
                            @else
                                <!-- Loading indicator for more tickets -->
                                <div x-show="visibleTickets < totalTickets" class="flex items-center justify-center py-4 text-gray-500 dark:text-gray-400 text-sm">
                                    <div class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Loading more tickets...</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if ($this->ticketStatuses->isEmpty())
                    <div class="w-full flex items-center justify-center h-40 text-gray-500 dark:text-gray-400">
                        No status columns found for this project
                    </div>
                @endif
            </div>
        </div>
    @endif
</x-filament-panels::page>
