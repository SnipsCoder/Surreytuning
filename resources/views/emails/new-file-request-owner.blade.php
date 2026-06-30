<x-emails.layout :subject="'New File Request — ' . $ref">
    <h2>New File Request Received</h2>
    <p>A new file request has been submitted and requires your attention.</p>
    <p>
        <strong>Reference:</strong> {{ $ref }}<br>
        <strong>Vehicle:</strong> {{ $fileRequest->vehicle_make }} {{ $fileRequest->vehicle_model }} ({{ $fileRequest->vehicle_year }})<br>
        <strong>Stage:</strong> {{ $fileRequest->fileStage?->name ?? '—' }}<br>
        <strong>Submitted:</strong> {{ $fileRequest->created_at->format('d/m/Y H:i') }}
    </p>
    <a href="{{ $url }}" class="btn">View File Request</a>
</x-emails.layout>
