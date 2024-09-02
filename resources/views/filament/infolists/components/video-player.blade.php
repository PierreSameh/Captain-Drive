@php
    $state = $getState();
@endphp

<x-filament-infolists::entry-wrapper :entry="$entry">
    @if ($state)
        <video width="100%" controls>
            <source src="{{ Storage::url($state) }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    @else
        <div class="text-gray-500">No video available</div>
    @endif
</x-filament-infolists::entry-wrapper>