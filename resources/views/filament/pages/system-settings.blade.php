<x-filament::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit">
                Save
            </x-filament::button>
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('settings-saved', () => {
                setTimeout(() => window.location.reload(), 200);
            });
        });
    </script>
</x-filament::page>