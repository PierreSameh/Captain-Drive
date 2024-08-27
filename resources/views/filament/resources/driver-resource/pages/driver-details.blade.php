<x-filament::card>
    <h2 class="text-2xl font-bold mb-4">Driver Details</h2>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p><strong>Name:</strong> {{ $driver->name }}</p>
            <p><strong>Email:</strong> {{ $driver->email }}</p>
            <p><strong>Phone:</strong> {{ $driver->phone }}</p>
            <p><strong>Additional Phone:</strong> {{ $driver->add_phone }}</p>
            <p><strong>National ID:</strong> {{ $driver->national_id }}</p>
            <p><strong>Social Status:</strong> {{ $driver->social_status }}</p>
            <p><strong>Gender:</strong> {{ $driver->gender }}</p>
            <p><strong>Status:</strong> {{ $driver->status }}</p>
        </div>
        <div>
            <p><strong>Latitude:</strong> {{ $driver->lat }}</p>
            <p><strong>Longitude:</strong> {{ $driver->lng }}</p>
            <p><strong>ID:</strong> {{$driver->super_key . $driver->unique_id }}</p>
        </div>
    </div>

    @if($driver->picture)
        <div class="mt-4">
            <h3 class="text-xl font-bold mb-2">Driver Picture</h3>
            <img src="{{ asset('storage/', $driver->picture) }}" alt="Driver Picture" class="max-w-full h-auto">
        </div>
    @endif

    @if($driverDocs->isNotEmpty())
        <div class="mt-4">
            <h3 class="text-xl font-bold mb-2">Driver Documents</h3>
            <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p><strong>National Id Front</strong></p>
                        <img src="{{ asset('storage/', $driverDocs->national_front) }}" alt="{{ $driverDocs->document_type }}" class="max-w-full h-auto">
                    </div>
            </div>
        </div>
    @endif

    @if($vehicle)
        <div class="mt-4">
            <h3 class="text-xl font-bold mb-2">Vehicle Information</h3>
            <p><strong>Model:</strong> {{ $vehicle->model }}</p>
            <p><strong>Plate Number:</strong> {{ $vehicle->plate_number }}</p>
            @if($vehicle->vehicle_image)
                <img src="{{ asset('storage/', $vehicle->image) }}" alt="Vehicle Image" class="max-w-full h-auto mt-2">
            @endif
        </div>
    @endif
</x-filament::card>