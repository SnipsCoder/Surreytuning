<x-layouts.owner>
    <x-page-header title="Edit Vehicle Stat" subtitle="{{ $vehicleStat->make }} {{ $vehicleStat->model }}" />

    <form method="POST" action="{{ route('vehicle-stats.update', $vehicleStat) }}" class="space-y-4 max-w-3xl">
        @csrf
        @method('PUT')
        @include('owner.vehicle-stats._form', ['vehicleStat' => $vehicleStat])
    </form>
</x-layouts.owner>
