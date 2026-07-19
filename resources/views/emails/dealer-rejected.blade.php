@php($brandName = \App\Models\Setting::brandName())
@php($supportEmail = \App\Models\Setting::supportEmail())
<x-emails.layout :subject="$brandName . ' — Application Update'">
    <h2>Application Update</h2>
    <p>Hi {{ $application->contact_name }},</p>
    <p>Thank you for your interest in joining {{ $brandName }}. After reviewing your application for <strong>{{ $application->company_name }}</strong>, we are unfortunately unable to approve your account at this time.</p>
    @if ($application->rejection_reason)
        <p><strong>Reason:</strong> {{ $application->rejection_reason }}</p>
    @endif
    <p>If you believe this was a mistake, or you'd like to provide more details to support your application, we'd be glad to hear from you.</p>
    @if ($supportEmail)
        <a href="mailto:{{ $supportEmail }}?subject={{ rawurlencode($brandName . ' — Application enquiry: ' . $application->company_name) }}" class="btn">Contact {{ $brandName }}</a>
        <p style="margin-top:16px;">Or email us directly at <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.</p>
    @else
        <p>If you have any questions, please don't hesitate to contact us.</p>
    @endif
</x-emails.layout>
