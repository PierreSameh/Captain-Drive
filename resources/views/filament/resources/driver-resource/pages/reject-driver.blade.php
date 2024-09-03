<x-filament-panels::page>
    <form wire:submit.prevent="reject">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-4">
            Reject Driver
        </x-filament::button>
    </form>
</x-filament-panels::page>