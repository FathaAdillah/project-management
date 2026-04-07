<div>
    <form wire:submit="addComment">
        {{ $this->form }}

        <div class="flex justify-end mt-3" style="margin-top: 20px">
            <x-filament::button type="submit" size="sm">
                Post Comment
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</div>