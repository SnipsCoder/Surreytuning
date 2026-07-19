@php($brandName = \App\Models\Setting::brandName())
<x-emails.layout :subject="'Your tuning file is ready — ' . $ref">
    <h2>Your Tuning File Is Ready</h2>
    <p>Hi {{ $contactName }},</p>
    <p>Good news — the tuning file for your request <strong>{{ $ref }}</strong> is ready to download.</p>
    <p>
        <strong>Vehicle:</strong> {{ $vehicle }}<br>
        @if ($stage)<strong>Service:</strong> {{ $stage }}<br>@endif
        <strong>Reference:</strong> {{ $ref }}
    </p>
    <a href="{{ $url }}" class="btn">Download Your File</a>
    <p style="margin-top:16px;">Log in to your {{ $brandName }} portal to download the file and review any notes from our technicians.</p>
</x-emails.layout>
