<x-emails.layout :subject="'File Request Received — ' . $ref">
    <h2>Your File Request Has Been Received</h2>
    <p>Thank you — we have received your file request and our team will review it shortly.</p>
    <p>
        <strong>Reference:</strong> {{ $ref }}<br>
        <strong>Vehicle:</strong> {{ $fileRequest->vehicle_make }} {{ $fileRequest->vehicle_model }} ({{ $fileRequest->vehicle_year }})<br>
        <strong>Stage:</strong> {{ $fileRequest->fileStage?->name ?? '—' }}<br>
        <strong>Submitted:</strong> {{ $fileRequest->created_at->format('d/m/Y H:i') }}
    </p>
    <a href="{{ $url }}" class="btn">View Request</a>
</x-emails.layout>
