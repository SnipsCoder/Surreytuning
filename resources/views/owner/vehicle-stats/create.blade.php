<x-layouts.owner>
    <x-page-header title="Add Vehicle Stat" subtitle="Create a new performance stat entry" />

    <form method="POST" action="{{ route('vehicle-stats.store') }}" class="space-y-4 max-w-3xl">
        @csrf
        @include('owner.vehicle-stats._form', ['vehicleStat' => null])
    </form>
</x-layouts.owner>
