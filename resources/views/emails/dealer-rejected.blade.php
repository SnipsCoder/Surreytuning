@php($brandName = \App\Models\Setting::brandName())
<x-emails.layout :subject="$brandName . ' — Application Update'">
    <h2>Application Update</h2>
    <p>Hi {{ $application->contact_name }},</p>
    <p>Thank you for your interest in joining {{ $brandName }}. After reviewing your application for <strong>{{ $application->company_name }}</strong>, we are unfortunately unable to approve your account at this time.</p>
    @if ($application->rejection_reason)
        <p><strong>Reason:</strong> {{ $application->rejection_reason }}</p>
    @endif
    <p>If you have any questions, please don't hesitate to contact us.</p>
</x-emails.layout>
