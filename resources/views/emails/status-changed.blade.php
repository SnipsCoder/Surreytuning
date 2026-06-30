<x-emails.layout :subject="'File Request Status Update — ' . $ref">
    <h2>File Request Status Updated</h2>
    <p>The status of your file request <strong>{{ $ref }}</strong> has been updated.</p>
    <p>
        <strong>Previous status:</strong> {{ $oldStatus->label() }}<br>
        <strong>New status:</strong> {{ $newStatus->label() }}
    </p>
    <a href="{{ $url }}" class="btn">View Request</a>
</x-emails.layout>
