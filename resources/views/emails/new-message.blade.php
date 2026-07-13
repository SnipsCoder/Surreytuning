@php($brandColour = \App\Models\Setting::brandColour())
<x-emails.layout :subject="'New Message on File Request ' . $ref">
    <h2>New Message Received</h2>
    <p>A new message has been posted on file request <strong>{{ $ref }}</strong>.</p>
    <p>
        <strong>From:</strong> {{ $message->sender?->full_name ?? 'System' }}<br>
        <strong>Sent:</strong> {{ $message->created_at->format('d/m/Y H:i') }}
    </p>
    <blockquote style="border-left: 3px solid {{ $brandColour }}; margin: 16px 0; padding: 8px 16px; color: #4b5563; background: #f9fafb;">
        {{ \Illuminate\Support\Str::limit($message->body, 300) }}
    </blockquote>
    <a href="{{ $url }}" class="btn">View Full Thread</a>
</x-emails.layout>
