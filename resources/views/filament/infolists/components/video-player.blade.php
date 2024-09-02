@php
    $state = $getState();
@endphp

<x-filament-infolists::entry-wrapper :entry="$entry">
    @if ($state['status'] === 'available')
        <video width="100%" controls>
            {{-- <source src="{{ Storage::disk('public')->url($state['path']) }}" type="video/mp4"> --}}
            <source src="{{ asset("storage/app/public/" . url($state['path']) }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    @else
        <div class="text-gray-500">{{ $state['message'] }}</div>
    @endif

    @if(app()->environment('local'))
        {{-- <div class="mt-4 p-4 bg-gray-100 rounded">
            <h3 class="text-lg font-semibold">Debug Information:</h3>
            <pre>{{ json_encode($state['debug'], JSON_PRETTY_PRINT) }}</pre>
        </div> --}}
    @endif
</x-filament-infolists::entry-wrapper>